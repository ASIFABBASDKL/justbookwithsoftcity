<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Booking;
use App\Models\ServiceAndPricing;


class ServiceProviderController extends Controller
{

    /**
     * Update Service Provider (API).
     */
    public function updateServiceProviderApi(Request $request, $id)
    {
        $this->requireOwnProvider((int) $id);

        $serviceProvider = ServiceProvider::findOrFail($id);

        $data = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:255',
            'id_verification' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'driving_license' => 'nullable|string|max:255',
            'passport' => 'nullable|string|max:255',
            'gmc_dbs_number' => 'nullable|string|max:255',
            'portfolio' => 'nullable|string|max:255',
            'experience_years' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $serviceProvider->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Service Provider updated successfully!',
            'data' => $serviceProvider,
        ], 200);
    }


    public function updateProfileApi(Request $request, $providerId)
    {
        $this->requireOwnProvider((int) $providerId);

        $serviceProvider = ServiceProvider::with('user')->findOrFail($providerId);
        $user = $serviceProvider->user;

        // ✅ Validation
        $data = $request->validate([
            'fullname' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'country' => 'sometimes|nullable|string|max:255',
        ]);

        // ✅ Duplicate email check
        if (!empty($data['email'])) {
            $exists = \App\Models\User::where('email', $data['email'])
                ->where('id', '!=', $user->id)
                ->exists();
            if ($exists) {
                return response()->json(['status' => false, 'message' => 'This email is already taken by another user'], 409);
            }
        }

        // ✅ Duplicate phone check
        if (!empty($data['phone_number'])) {
            $exists = \App\Models\User::where('phone_number', $data['phone_number'])
                ->where('id', '!=', $user->id)
                ->exists();
            if ($exists) {
                return response()->json(['status' => false, 'message' => 'This phone number is already taken by another user'], 409);
            }
        }

        // ✅ Old phone save for comparison
        $oldPhone = $user->phone_number;
        $newPhone = $data['phone_number'] ?? $oldPhone;

        // ✅ Update user
        $user->update([
            'fullname' => $data['fullname'] ?? $user->fullname,
            'email' => $data['email'] ?? $user->email,
            'phone_number' => $newPhone,
        ]);

        // ✅ Update provider
        $serviceProvider->update([
            'country' => $data['country'] ?? $serviceProvider->country,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully!',
            'data' => [
                'provider_id' => $serviceProvider->id,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'country' => $serviceProvider->country,
            ]
        ], 200);
    }
    public function todayEarningsAndReviews($providerId)
    {
        $this->requireOwnProvider((int) $providerId);

        $provider = ServiceProvider::findOrFail($providerId);

        // -----------------------------
        // ✅ Today
        // -----------------------------
        $todayBookings = Booking::where('service_provider_id', $providerId)
            ->whereDate('booking_date', now()->toDateString())
            ->where('status', 'completed')
            ->get();

        $totalEarningToday = $todayBookings->sum('total_amount');

        // -----------------------------
        // ✅ Yesterday
        // -----------------------------
        $yesterdayBookings = Booking::where('service_provider_id', $providerId)
            ->whereDate('booking_date', now()->subDay()->toDateString())
            ->where('status', 'completed')
            ->get();

        $totalEarningYesterday = $yesterdayBookings->sum('total_amount');

        // ✅ Compare Today vs Yesterday (percentage)
        if ($totalEarningYesterday > 0) {
            $earningComparison = (($totalEarningToday - $totalEarningYesterday) / $totalEarningYesterday) * 100;
        } else {
            $earningComparison = $totalEarningToday > 0 ? 100 : 0;
        }

        // ✅ Condition for + or -
        $earningComparison = round($earningComparison, 2);
        $earningComparison = $earningComparison > 0 ? '+' . $earningComparison : $earningComparison;

        // -----------------------------
        // ✅ This Week
        // -----------------------------
        $weekBookings = Booking::where('service_provider_id', $providerId)
            ->whereBetween('booking_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'completed')
            ->get();

        $totalEarningWeek = $weekBookings->sum('total_amount');

        // -----------------------------
        // ✅ This Month
        // -----------------------------
        $monthBookings = Booking::where('service_provider_id', $providerId)
            ->whereBetween('booking_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('status', 'completed')
            ->get();

        $totalEarningMonth = $monthBookings->sum('total_amount');

        // ✅ Provider ki total completed bookings (all-time)
        $totalBookings = Booking::where('service_provider_id', $providerId)
            ->where('status', 'completed')
            ->count();

        // ✅ Provider ki average rating
        $averageRating = Review::where('service_provider_id', $providerId)->avg('rating');

        return response()->json([
            'status' => true,
            'provider_id' => $providerId,

            // Today
            'total_earning_today' => $totalEarningToday,

            // Compare Today vs Yesterday
            'earning_comparison_percent' => $earningComparison, // 👈 e.g. +25.5 ya -10.2

            // This Week
            'total_earning_week' => $totalEarningWeek,

            // This Month
            'total_earning_month' => $totalEarningMonth,

            // All time
            'total_bookings' => $totalBookings,
            'average_rating' => round($averageRating, 2),
        ]);
    }
    public function providerSummary($providerId)
    {
        // ✅ Provider exist check
        $provider = ServiceProvider::with('user')->findOrFail($providerId);

        // ✅ Provider ka name (user table se)
        $providerName = $provider->user->fullname ?? null;

        // ✅ Provider ka image (service_providers table se)
        $providerImage = $provider->image ?? null;

        // ✅ Provider ki services
        $services = ServiceAndPricing::where('service_provider_id', $providerId)
            ->pluck('service_name');

        // ✅ Completed bookings count
        $completedBookings = Booking::where('service_provider_id', $providerId)
            ->where('status', 'completed')
            ->count();

        // ✅ Average rating
        $averageRating = Review::where('service_provider_id', $providerId)->avg('rating');

        return response()->json([
            'status' => true,
            'provider_id' => $providerId,
            'provider_name' => $providerName,
            'provider_image' => $providerImage, // 👈 image add
            'services' => $services,
            'completed_bookings' => $completedBookings,
            'average_rating' => round($averageRating, 2),
        ]);
    }

}