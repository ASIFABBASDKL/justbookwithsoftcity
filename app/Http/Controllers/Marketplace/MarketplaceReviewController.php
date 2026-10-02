<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Gig;
use App\Models\Order;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class MarketplaceReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'satisfaction' => 'nullable|integer|min:1|max:5',
            'response_rate' => 'nullable|integer|min:1|max:5',
            'job_success' => 'nullable|integer|min:1|max:5',
            'reliability' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);
        $order = Order::findOrFail($data['order_id']);
        if ($order->status !== 'completed') {
            return ApiResponse::error('Reviews are allowed only on completed orders.', 422);
        }
        $user = $request->user();
        $asBuyer = (int) $order->buyer_id === (int) $user->id;
        $asSeller = (int) $order->seller_id === (int) $user->id;
        if (! $asBuyer && ! $asSeller) {
            $this->deny();
        }
        $type = $asBuyer ? 'buyer' : 'seller';
        $reviewee = $asBuyer ? $order->seller_id : $order->buyer_id;
        if (Review::where('order_id', $order->id)->where('reviewer_id', $user->id)->exists()) {
            return ApiResponse::error('Already reviewed.', 422);
        }
        $review = Review::create($data + [
            'reviewer_id' => $user->id,
            'reviewee_id' => $reviewee,
            'reviewer_type' => $type,
            'is_public' => true,
            'service_user_id' => $order->buyer->buyerProfile?->id,
            'service_provider_id' => $order->seller->sellerProfile?->id,
        ]);
        $this->recalculate($order);

        return ApiResponse::success($review, 'Review submitted', 201);
    }

    private function recalculate(Order $order): void
    {
        $sellerReviews = Review::where('reviewee_id', $order->seller_id)->where('reviewer_type', 'buyer');
        $avg = round((float) $sellerReviews->avg('rating'), 2);
        $order->seller->sellerProfile?->update(['completion_rate' => $avg ? min(100, $avg * 20) : null]);
        if ($order->source_type === 'gig_package') {
            $package = \App\Models\GigPackage::find($order->source_id);
            $gig = $package?->gig;
            if ($gig) {
                $gig->update(['avg_rating' => $avg]);
            }
        }
    }

    public static function performancePercents($reviews): array
    {
        $total = max(1, $reviews->count());
        $metric = function (string $field) use ($reviews, $total) {
            $sum = (float) $reviews->sum($field);
            $denom = $total * 5;
            if ($denom <= 0) {
                return 0;
            }

            return round(($sum / $denom) * 100, 2);
        };

        return [
            'satisfaction' => $metric('satisfaction'),
            'response_rate' => $metric('response_rate'),
            'job_success' => $metric('job_success'),
            'reliability' => $metric('reliability'),
        ];
    }
}
