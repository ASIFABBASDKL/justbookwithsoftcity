<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Gig;
use App\Models\JobPosted;
use App\Models\Order;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function home()
    {
        return view('web.landing');
    }

    public function appHome(Request $request)
    {
        $user = $request->user();
        if ($user->hasAnyRole(['admin', 'moderator'])) {
            return redirect()->route('web.admin');
        }
        if ($user->is_seller) {
            return redirect()->route('web.seller');
        }

        return redirect()->route('web.buyer');
    }

    public function buyer(Request $request)
    {
        $user = $request->user();
        $orders = $this->safe(fn () => Order::where('buyer_id', $user->id)->latest()->limit(8)->get(), collect());
        $jobs = $this->safe(fn () => JobPosted::where('buyer_id', $user->id)->latest()->limit(6)->get(), collect());
        $gigs = $this->safe(fn () => Gig::query()->active()->with('packages')->latest()->limit(6)->get(), collect());

        $stats = [
            'active_orders' => $orders->whereIn('status', ['active', 'delivered', 'revision_requested'])->count(),
            'completed' => $this->safe(fn () => Order::where('buyer_id', $user->id)->where('status', 'completed')->count(), 0),
            'open_jobs' => $this->safe(fn () => JobPosted::where('buyer_id', $user->id)->whereIn('status', ['open', 'in_review'])->count(), 0),
            'spent' => $this->safe(fn () => (float) Order::where('buyer_id', $user->id)->where('status', 'completed')->sum('price'), 0),
        ];

        return view('web.dashboards.buyer', compact('user', 'orders', 'jobs', 'gigs', 'stats'));
    }

    public function sellerOnboard()
    {
        if (auth()->user()->is_seller && auth()->user()->sellerProfile) {
            return redirect()->route('web.seller');
        }

        return view('web.dashboards.seller-onboard');
    }

    public function becomeSeller(Request $request)
    {
        $request->user()->becomeSeller();

        return redirect()->route('web.seller')->with('success', 'Seller workspace is ready.');
    }

    public function seller(Request $request)
    {
        $user = $request->user();
        $profile = $user->sellerProfile;
        $gigs = $this->safe(fn () => Gig::where('seller_id', $profile->id)->latest()->limit(8)->get(), collect());
        $orders = $this->safe(fn () => Order::where('seller_id', $user->id)->latest()->limit(8)->get(), collect());
        $wallet = $profile?->wallet;

        $stats = [
            'active_gigs' => $gigs->where('status', 'active')->count(),
            'orders_in' => $this->safe(fn () => Order::where('seller_id', $user->id)->whereIn('status', ['active', 'delivered', 'revision_requested'])->count(), 0),
            'available' => (float) ($wallet->total_available_amount ?? 0),
            'pending' => (float) ($wallet->pending_clearance ?? 0),
        ];

        return view('web.dashboards.seller', compact('user', 'profile', 'gigs', 'orders', 'wallet', 'stats'));
    }

    public function admin()
    {
        $stats = [
            'users' => User::count(),
            'orders' => $this->safe(fn () => Order::count(), 0),
            'revenue' => $this->safe(fn () => (float) Order::where('status', 'completed')->sum('platform_fee'), 0),
            'gigs_pending' => $this->safe(fn () => Gig::where('status', 'pending')->count(), 0),
            'disputes' => $this->safe(fn () => Dispute::where('status', 'open')->count(), 0),
            'payouts' => $this->safe(fn () => Payout::where('status', 'requested')->count(), 0),
        ];

        $recentUsers = User::latest()->limit(8)->get();
        $recentOrders = $this->safe(fn () => Order::with(['buyer', 'seller'])->latest()->limit(8)->get(), collect());
        $pendingGigs = $this->safe(fn () => Gig::with('seller.user')->where('status', 'pending')->latest()->limit(6)->get(), collect());
        $disputes = $this->safe(fn () => Dispute::with('order')->where('status', 'open')->latest()->limit(6)->get(), collect());

        return view('web.dashboards.admin', compact('stats', 'recentUsers', 'recentOrders', 'pendingGigs', 'disputes'));
    }

    private function safe(callable $fn, mixed $default): mixed
    {
        try {
            if (! Schema::hasTable('orders')) {
                return $default;
            }

            return $fn();
        } catch (\Throwable) {
            return $default;
        }
    }
}
