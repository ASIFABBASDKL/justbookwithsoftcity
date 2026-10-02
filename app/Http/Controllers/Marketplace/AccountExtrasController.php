<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Gig;
use App\Models\JobPosted;
use App\Models\Portfolio;
use App\Models\SavedItem;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ConnectService;
use App\Services\PaymentService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AccountExtrasController extends Controller
{
    public function sellerShow(User $user)
    {
        $profile = $user->sellerProfile;
        if (! $profile) {
            return ApiResponse::error('Not a seller', 404);
        }
        $profile->load(['user', 'wallet']);
        $gigs = Gig::where('seller_id', $profile->id)->active()->with('packages')->get();

        return ApiResponse::success([
            'profile' => $profile,
            'gigs' => $gigs,
            'portfolios' => Portfolio::where('seller_id', $profile->id)->get(),
        ]);
    }

    public function updateSeller(Request $request)
    {
        $profile = $request->user()->sellerProfile;
        if (! $profile) {
            $this->deny('Become a seller first.');
        }
        $profile->update($request->only([
            'headline', 'bio', 'languages', 'hourly_rate', 'country', 'description', 'business_name',
        ]));
        if ($request->filled('skill_ids')) {
            $profile->skills()->sync($request->skill_ids);
        }

        return ApiResponse::success($profile, 'Seller profile updated');
    }

    public function portfolios(Request $request)
    {
        $seller = $request->user()->sellerProfile;
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'url' => 'nullable|url',
                'category_id' => 'nullable|exists:categories,id',
                'images' => 'nullable|array',
            ]);
            $data['seller_id'] = $seller->id;

            return ApiResponse::success(Portfolio::create($data), 'Portfolio item added', 201);
        }

        return ApiResponse::success(Portfolio::where('seller_id', $seller->id)->get());
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:gig,job,seller',
            'id' => 'required|integer',
        ]);
        $map = ['gig' => Gig::class, 'job' => JobPosted::class, 'seller' => SellerProfile::class];
        $model = $map[$data['type']];
        $item = $model::findOrFail($data['id']);
        $saved = SavedItem::firstOrCreate([
            'user_id' => $request->user()->id,
            'saveable_type' => $model,
            'saveable_id' => $item->id,
        ]);

        return ApiResponse::success($saved, 'Saved');
    }

    public function unsave(Request $request)
    {
        $data = $request->validate(['type' => 'required', 'id' => 'required|integer']);
        $map = ['gig' => Gig::class, 'job' => JobPosted::class, 'seller' => SellerProfile::class];
        SavedItem::where('user_id', $request->user()->id)
            ->where('saveable_type', $map[$data['type']])
            ->where('saveable_id', $data['id'])
            ->delete();

        return ApiResponse::success(null, 'Removed');
    }

    public function saved(Request $request)
    {
        return ApiResponse::success(SavedItem::where('user_id', $request->user()->id)->with('saveable')->latest()->get());
    }

    public function payout(Request $request, PaymentService $payments)
    {
        $wallet = Wallet::where('service_provider_id', $request->user()->sellerProfile?->id)->firstOrFail();
        $request->validate(['amount' => 'required|numeric|min:1', 'method' => 'nullable|string']);
        $payout = $payments->requestPayout($wallet, (float) $request->amount, $request->get('method', 'stripe'));

        return ApiResponse::success($payout, 'Payout requested');
    }

    public function buyConnects(Request $request, ConnectService $connects)
    {
        $seller = $request->user()->sellerProfile;
        $request->validate(['amount' => 'required|integer|min:1']);
        $balance = $connects->purchase($seller, (int) $request->amount);

        return ApiResponse::success($balance, 'Connects added (simulated payment)');
    }

    public function connectBalance(Request $request, ConnectService $connects)
    {
        return ApiResponse::success($connects->ensure($request->user()->sellerProfile));
    }

    public function report(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
            'reason' => 'required|string',
            'description' => 'nullable|string',
        ]);
        $map = ['gig' => Gig::class, 'user' => User::class, 'job' => JobPosted::class];
        $report = \App\Models\Report::create([
            'reportable_type' => $map[$data['type']] ?? Gig::class,
            'reportable_id' => $data['id'],
            'reporter_id' => $request->user()->id,
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
        ]);

        return ApiResponse::success($report, 'Report filed', 201);
    }

    public function announcements()
    {
        return ApiResponse::success(\App\Models\Announcement::where('is_active', true)->latest()->get());
    }

    public function stripeWebhook(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');
        if ($secret) {
            $sig = $request->header('Stripe-Signature');
            if (! $sig) {
                return ApiResponse::error('Missing signature', 400);
            }
        }
        $event = $request->input('type');

        return ApiResponse::success(['received' => $event], 'Webhook accepted');
    }
}
