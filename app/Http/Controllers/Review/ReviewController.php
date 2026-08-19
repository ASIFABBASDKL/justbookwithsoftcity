<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Booking;

class ReviewController extends Controller
{
    /**
     * Submit a review for a booking
     */
    public function submitReview(Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'service_user_id' => 'required|exists:service_users,id',
            'service_provider_id' => 'required|exists:service_providers,id',
            'rating' => 'required|integer|min:1|max:5',
            'satisfaction' => 'nullable|integer|min:1|max:5',
            'response_rate' => 'nullable|integer|min:1|max:5',
            'job_success' => 'nullable|integer|min:1|max:5',
            'reliability' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // ✅ Prevent duplicate review for same booking by same user
        $existingReview = Review::where('booking_id', $data['booking_id'])
            ->where('service_user_id', $data['service_user_id'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'status' => false,
                'message' => 'You have already submitted a review for this booking.',
            ], 400);
        }

        // ✅ Create new review
        $review = Review::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Review submitted successfully!',
            'data' => $review,
        ], 200);
    }
    public function getProviderReviews($provider_id)
    {
        $reviews = Review::with(['serviceUser.user', 'booking'])
            ->where('service_provider_id', $provider_id)
            ->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No reviews found for this provider.',
            ], 404);
        }

        // 🔹 Reviews detail
        $data = $reviews->map(function ($review) {
            return [
                'review_id' => $review->id,
                'booking_id' => $review->booking_id,
                // 'service_user_id' => $review->service_user_id,
                'service_user_name' => $review->serviceUser->user->fullname ?? 'N/A',
                'rating' => $review->rating,
                'comment' => $review->comment,
                // 'satisfaction' => $review->satisfaction,
                // 'response_rate' => $review->response_rate,
                // 'job_success' => $review->job_success,
                // 'reliability' => $review->reliability,
                'created_at' => $review->created_at->toDateTimeString(),
            ];
        });

        // 🔹 Calculate averages
        $totalReviews = $reviews->count();

        $avgSatisfaction = round(($reviews->sum('satisfaction') / ($totalReviews * 5)) * 100, 2);
        $avgResponseRate = round(($reviews->sum('response_rate') / ($totalReviews * 5)) * 100, 2);
        $avgJobSuccess = round(($reviews->sum('job_success') / ($totalReviews * 5)) * 100, 2);
        $avgReliability = round(($reviews->sum('reliability') / ($totalReviews * 5)) * 100, 2);

        return response()->json([
            'status' => true,
            'provider_id' => $provider_id,
            'total_reviews' => $totalReviews,
            'average_rating' => round($reviews->avg('rating'), 2),
            'performance' => [
                'satisfaction' => $avgSatisfaction . '%',
                'response_rate' => $avgResponseRate . '%',
                'job_success' => $avgJobSuccess . '%',
                'reliability' => $avgReliability . '%',
            ],
            'reviews' => $data,
        ]);
    }


}
