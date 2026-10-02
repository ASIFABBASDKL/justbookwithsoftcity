<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Dispute;
use App\Models\Gig;
use App\Models\JobPosted;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Payout;
use App\Models\PlatformSetting;
use App\Models\Report;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserBan;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return ApiResponse::success([
            'users' => User::count(),
            'orders' => Order::count(),
            'revenue' => Order::where('status', 'completed')->sum('platform_fee'),
            'open_disputes' => Dispute::where('status', 'open')->count(),
            'pending_gigs' => Gig::where('status', 'pending')->count(),
        ]);
    }

    public function users(Request $request)
    {
        $q = User::query()->with(['roles', 'buyerProfile', 'sellerProfile']);
        if ($request->filled('q')) {
            $q->where('email', 'like', '%'.$request->q.'%');
        }

        return ApiResponse::success($q->latest()->paginate(30));
    }

    public function banUser(Request $request, User $user)
    {
        $request->validate(['reason' => 'required|string', 'expires_at' => 'nullable|date']);
        $ban = UserBan::create([
            'user_id' => $user->id,
            'reason' => $request->reason,
            'banned_by' => $request->user()->id,
            'expires_at' => $request->expires_at,
        ]);

        return ApiResponse::success($ban, 'User banned');
    }

    public function pendingGigs()
    {
        return ApiResponse::success(Gig::where('status', 'pending')->with('seller.user')->latest()->paginate(20));
    }

    public function approveGig(Request $request, Gig $gig)
    {
        if ($request->boolean('reject')) {
            $gig->update(['status' => 'rejected', 'rejection_reason' => $request->reason]);

            return ApiResponse::success($gig, 'Gig rejected');
        }
        $gig->update(['status' => 'active', 'rejection_reason' => null]);

        return ApiResponse::success($gig, 'Gig approved');
    }

    public function moderateJob(Request $request, JobPosted $job)
    {
        $job->update(['status' => $request->get('status', 'closed')]);

        return ApiResponse::success($job, 'Job updated');
    }

    public function forceOrder(Request $request, Order $order, OrderService $orders)
    {
        $action = $request->get('action');
        if ($action === 'complete') {
            $orders->complete($order, $request->user());
        } elseif ($action === 'cancel') {
            $orders->forceCancel($order, $request->user(), (float) $order->price + (float) $order->extras_total, 'admin');
        }

        return ApiResponse::success($order->fresh(), 'Order updated');
    }

    public function ledger()
    {
        return ApiResponse::success(LedgerEntry::latest()->paginate(50));
    }

    public function payouts()
    {
        return ApiResponse::success(Payout::latest()->paginate(30));
    }

    public function approvePayout(Payout $payout)
    {
        $payout->update(['status' => 'paid', 'paid_at' => now()]);

        return ApiResponse::success($payout, 'Payout approved');
    }

    public function disputes()
    {
        return ApiResponse::success(Dispute::with('order')->latest()->paginate(20));
    }

    public function resolveDispute(Request $request, Dispute $dispute, OrderService $orders)
    {
        $request->validate(['resolution' => 'required|in:full_refund,partial,seller_wins', 'refund_amount' => 'nullable|numeric']);
        $dispute->update([
            'status' => 'resolved',
            'resolved_by' => $request->user()->id,
            'resolution' => $request->resolution,
            'refund_amount' => $request->refund_amount,
        ]);
        $order = $dispute->order;
        if ($request->resolution === 'full_refund') {
            $orders->forceCancel($order, $request->user(), (float) $order->price + (float) $order->extras_total, 'dispute');
        } elseif ($request->resolution === 'partial') {
            $orders->forceCancel($order, $request->user(), (float) $request->refund_amount, 'dispute_partial');
        } else {
            $orders->complete($order, $request->user());
        }

        return ApiResponse::success($dispute->fresh(), 'Dispute resolved');
    }

    public function reports()
    {
        return ApiResponse::success(Report::latest()->paginate(20));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        $cat = Category::create($data);

        return ApiResponse::success($cat, 'Category created', 201);
    }

    public function storeSkill(Request $request)
    {
        $data = $request->validate(['name' => 'required|string', 'category_id' => 'nullable|exists:categories,id']);
        $skill = Skill::create($data + ['slug' => \Illuminate\Support\Str::slug($data['name'])]);

        return ApiResponse::success($skill, 'Skill created', 201);
    }

    public function settings(Request $request)
    {
        if ($request->isMethod('post')) {
            foreach ($request->except('_token') as $key => $value) {
                PlatformSetting::setValue($key, $value);
            }

            return ApiResponse::success(PlatformSetting::all(), 'Settings saved');
        }

        return ApiResponse::success(PlatformSetting::all());
    }

    public function announcements(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate(['title' => 'required', 'body' => 'required', 'is_active' => 'boolean']);

            return ApiResponse::success(Announcement::create($data), 'Announcement created', 201);
        }

        return ApiResponse::success(Announcement::latest()->get());
    }
}
