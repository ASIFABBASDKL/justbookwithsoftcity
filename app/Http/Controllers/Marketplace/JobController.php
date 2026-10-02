<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\JobPosted;
use App\Models\Proposal;
use App\Services\ConnectService;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $q = JobPosted::query()->with(['category', 'skills'])->whereIn('status', ['open', 'in_review']);
        if ($request->filled('category_id')) {
            $q->where('category_id', $request->category_id);
        }
        if ($request->filled('min_budget')) {
            $q->where('budget_max', '>=', $request->min_budget);
        }
        if ($request->filled('max_budget')) {
            $q->where('budget_min', '<=', $request->max_budget);
        }
        if ($request->filled('skill_id')) {
            $q->whereHas('skills', fn ($s) => $s->where('skills.id', $request->skill_id));
        }
        if ($request->filled('q')) {
            $q->where('title', 'like', '%'.$request->q.'%');
        }

        return ApiResponse::success($q->latest()->paginate(20), 'Jobs');
    }

    public function show(JobPosted $job)
    {
        $job->increment('views');
        $job->load(['category', 'skills', 'attachments', 'buyer.buyerProfile']);

        return ApiResponse::success($job, 'Job detail');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'budget_type' => 'in:fixed,hourly',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'experience_level' => 'nullable|string',
            'status' => 'in:draft,open',
            'skill_ids' => 'nullable|array',
        ]);
        $data['budget_type'] = $data['budget_type'] ?? 'fixed';
        if (($data['budget_type'] ?? 'fixed') === 'hourly') {
            return ApiResponse::error('Hourly jobs are disabled in MVP.', 422);
        }
        $data['buyer_id'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'open';
        $job = JobPosted::create($data);
        if ($request->skill_ids) {
            $job->skills()->sync($request->skill_ids);
        }
        $request->user()->buyerProfile?->increment('jobs_posted');

        return ApiResponse::success($job, 'Job posted', 201);
    }

    public function update(Request $request, JobPosted $job)
    {
        if ((int) $job->buyer_id !== (int) $request->user()->id) {
            $this->deny();
        }
        $job->update($request->only(['title', 'description', 'budget_min', 'budget_max', 'deadline', 'status']));

        return ApiResponse::success($job, 'Job updated');
    }

    public function close(Request $request, JobPosted $job)
    {
        if ((int) $job->buyer_id !== (int) $request->user()->id) {
            $this->deny();
        }
        $job->update(['status' => 'closed']);

        return ApiResponse::success($job, 'Job closed');
    }

    public function repost(Request $request, JobPosted $job)
    {
        if ((int) $job->buyer_id !== (int) $request->user()->id) {
            $this->deny();
        }
        $copy = $job->replicate();
        $copy->status = 'open';
        $copy->proposals_count = 0;
        $copy->views = 0;
        $copy->save();
        $copy->skills()->sync($job->skills()->pluck('skills.id'));

        return ApiResponse::success($copy, 'Job reposted', 201);
    }

    public function propose(Request $request, JobPosted $job, ConnectService $connects)
    {
        if ($job->status !== 'open') {
            return ApiResponse::error('Job is not open.', 422);
        }
        $seller = $request->user()->sellerProfile;
        if (! $seller) {
            $this->deny('Become a seller first.');
        }
        $data = $request->validate([
            'cover_letter' => 'required|string',
            'bid_amount' => 'required|numeric|min:1',
            'delivery_days' => 'required|integer|min:1',
            'milestones' => 'nullable|array',
        ]);
        if (Proposal::where('job_id', $job->id)->where('seller_id', $seller->id)->exists()) {
            return ApiResponse::error('You already bid on this job.', 422);
        }
        $limit = 10;
        if (Proposal::where('seller_id', $seller->id)->whereDate('created_at', today())->count() >= $limit) {
            return ApiResponse::error('Daily proposal limit reached.', 429);
        }
        $cost = 2;
        $connects->spend($seller, $cost, 'job:'.$job->id);
        $proposal = Proposal::create([
            'job_id' => $job->id,
            'seller_id' => $seller->id,
            'cover_letter' => $data['cover_letter'],
            'bid_amount' => $data['bid_amount'],
            'delivery_days' => $data['delivery_days'],
            'connects_spent' => $cost,
            'status' => 'pending',
        ]);
        foreach ($data['milestones'] ?? [] as $i => $m) {
            $proposal->milestones()->create([
                'title' => $m['title'],
                'amount' => $m['amount'],
                'delivery_days' => $m['delivery_days'] ?? 1,
                'sort_order' => $i,
            ]);
        }
        $job->increment('proposals_count');
        if ($job->status === 'open') {
            $job->update(['status' => 'in_review']);
        }

        return ApiResponse::success($proposal->load('milestones'), 'Proposal sent', 201);
    }

    public function proposals(Request $request, JobPosted $job)
    {
        if ((int) $job->buyer_id !== (int) $request->user()->id) {
            $this->deny();
        }
        $q = $job->proposals()->with(['seller.user', 'milestones']);
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        return ApiResponse::success($q->latest()->get(), 'Proposals');
    }

    public function shortlist(Request $request, Proposal $proposal)
    {
        $this->assertJobOwner($proposal, $request->user());
        $proposal->update(['status' => $request->boolean('reject') ? 'rejected' : 'shortlisted']);

        return ApiResponse::success($proposal, 'Proposal updated');
    }

    public function hire(Request $request, Proposal $proposal, OrderService $orders)
    {
        $this->assertJobOwner($proposal, $request->user());
        $order = $orders->createFromProposal($request->user(), $proposal);

        return ApiResponse::success($order->load('items', 'milestones'), 'Hired — order created', 201);
    }

    public function withdrawProposal(Request $request, Proposal $proposal)
    {
        $seller = $request->user()->sellerProfile;
        if ((int) $proposal->seller_id !== (int) $seller?->id) {
            $this->deny();
        }
        $proposal->update(['status' => 'withdrawn']);

        return ApiResponse::success($proposal, 'Proposal withdrawn');
    }

    private function assertJobOwner(Proposal $proposal, $user): void
    {
        if ((int) $proposal->job->buyer_id !== (int) $user->id) {
            $this->deny();
        }
    }
}
