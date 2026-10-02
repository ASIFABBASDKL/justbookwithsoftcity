<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str; // for random uuid
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCreatedMail;
use App\Mail\BookingStatusUpdatedMail;
use App\Models\Notification;
use App\Services\FirebaseService;
use App\Models\Wallet;
class BookingController extends Controller
{
    //
    public function getBookingsApi(Request $request)
    {
        $providerId = $this->ownServiceProviderId();
        if (! $providerId) {
            $this->deny('Only a service provider can view provider bookings.');
        }

        $bookings = Booking::with(['serviceUser', 'servicesAndPricing'])
            ->where('service_provider_id', $providerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Bookings fetched successfully',
            'data' => $bookings,
        ], 200);
    }


    public function storeBookingApi(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'service_provider_id' => 'required|exists:seller_profiles,id',
            'services_and_pricing_id' => 'required|exists:services_and_pricing,id',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'address' => 'nullable|string|max:255',
            'frequency' => 'in:one-time,weekly,monthly',
            'describe' => 'nullable|string',
            'location_img' => 'nullable|string|max:255',
            'special_request' => 'nullable|string',
            'price' => 'nullable|numeric',
            'subtotal' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'service_charges' => 'nullable|numeric',
            'emergency_booking' => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
            'payment_method' => 'nullable|string|max:100',
            'transaction_id' => 'required|string|unique:payment_transactions,transaction_id',
        ]);

        $serviceUserId = $this->ownServiceUserId();
        if (! $serviceUserId) {
            $this->deny('Only a service user can create a booking.');
        }

        $bookingData = $request->only([
            'service_provider_id',
            'services_and_pricing_id',
            'booking_date',
            'booking_time',
            'address',
            'frequency',
            'describe',
            'location_img',
            'special_request',
            'price',
            'subtotal',
            'discount',
            'tax',
            'service_charges',
            'emergency_booking',
            'total_amount',
            'payment_method',
        ]);
        $bookingData['service_user_id'] = $serviceUserId;
        $bookingData['payment_status'] = 'unpaid';
        $bookingData['status'] = 'pending';

        [$booking, $transaction] = DB::transaction(function () use ($bookingData, $request) {
            $booking = Booking::create($bookingData);
            $wallet = $booking->serviceProvider->wallet ?? null;
            if (! $wallet) {
                $wallet = Wallet::create([
                    'service_provider_id' => $booking->service_provider_id,
                    'total_amount' => 0.00,
                    'total_available_amount' => 0.00,
                    'total_withdrawal_amount' => 0.00,
                    'pending_clearance' => 0.00,
                ]);
            }
            $transaction = PaymentTransaction::create([
                'service_provider_id' => $booking->service_provider_id,
                'wallet_id' => $wallet->id,
                'booking_id' => $booking->id,
                'transaction_id' => $request->transaction_id,
                'payment_method' => $booking->payment_method ?? 'cash',
                'amount' => (float) ($booking->total_amount ?? 0),
                'status' => 'incoming',
            ]);
            $wallet->increment('total_amount', (float) $transaction->amount);

            return [$booking->fresh(), $transaction];
        });

        // 5. Service User Notification
        if ($booking->serviceUser && $booking->serviceUser->user) {
            $user = $booking->serviceUser->user;

            $notification = Notification::create([
                'user_id' => $user->id,
                'device_token' => $user->device_token ?? '',
                'title' => 'Booking Created',
                'body' => 'Your booking has been created successfully 🚀',
            ]);

            if ($user->device_token) {
                $firebase->sendNotification(
                    $user->device_token,
                    $notification->title,
                    $notification->body,
                    $user->id
                );
            }
        }

        // 6. Service Provider Notification
        if ($booking->serviceProvider && $booking->serviceProvider->user) {
            $provider = $booking->serviceProvider->user;

            Mail::to($provider->email)->send(new BookingCreatedMail($booking, 'provider'));

            $notification = Notification::create([
                'user_id' => $provider->id,
                'device_token' => $provider->device_token ?? '',
                'title' => 'New Booking Received',
                'body' => 'You have received a new booking. Please check your dashboard.',
            ]);

            if ($provider->device_token) {
                $firebase->sendNotification(
                    $provider->device_token,
                    $notification->title,
                    $notification->body,
                    $provider->id
                );
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Booking created successfully!',
            'data' => $booking,
        ], 200);
    }
    public function updateBookingStatusApi(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'service_provider_id' => 'required|exists:seller_profiles,id',
        ]);

        return DB::transaction(function () use ($request, $firebase) {
            // 🔹 booking find karo
            $booking = Booking::with(['serviceUser.user', 'serviceProvider.user', 'serviceProvider.wallet'])
                ->lockForUpdate()
                ->findOrFail($request->booking_id);

            $this->requireOwnProvider((int) $booking->service_provider_id);

            if ((int) $request->service_provider_id !== (int) $booking->service_provider_id) {
                $this->deny();
            }

            $currentStatus = $booking->status;

            // 🔹 Next status flow
            $nextStatus = match ($currentStatus) {
                'pending' => 'booked',
                'booked' => 'on-the-way',
                'on-the-way' => 'started',
                'started' => 'in-progress',
                'in-progress' => 'completed',
                default => $currentStatus, // completed/cancelled → no change
            };

            // 🔹 Update booking
            if ($nextStatus !== $currentStatus) {
                $booking->status = $nextStatus;
            }
            $booking->save();

            // ✅ Agar status completed ho → wallet update
            if ($nextStatus === 'completed') {
                $wallet = $booking->serviceProvider->wallet ?? null;

                // Agar wallet exist nahi karta → create kar do
                if (!$wallet) {
                    $wallet = Wallet::create([
                        'service_provider_id' => $booking->service_provider_id,
                        'total_amount' => 0.00,
                        'total_available_amount' => 0.00,
                        'total_withdrawal_amount' => 0.00,
                    ]);
                }

                // ✅ available amount me add karo
                $wallet->increment('total_available_amount', (float) ($booking->total_amount ?? 0));
            }

            // Notification content decide
            $userTitle = "Booking Status Updated";
            $userBody = "Your booking is now '{$nextStatus}'.";
            $providerTitle = "Booking Status Changed";
            $providerBody = "Booking #{$booking->id} status updated to '{$nextStatus}'.";

            // 🔹 Send mail to Service User (if exists)
            if ($booking->serviceUser && $booking->serviceUser->user) {
                $user = $booking->serviceUser->user;

                Mail::to($user->email)
                    ->send(new BookingStatusUpdatedMail($booking, 'user', $currentStatus, $nextStatus));

                // DB Store
                $notification = Notification::create([
                    'user_id' => $user->id,
                    'device_token' => $user->device_token ?? '',
                    'title' => $userTitle,
                    'body' => $userBody,
                ]);

                // Firebase Push
                if ($user->device_token) {
                    $firebase->sendNotification($user->device_token, $notification->title, $notification->body, $user->id);
                }
            }

            // 🔹 Send mail to Service Provider (if exists)
            if ($booking->serviceProvider && $booking->serviceProvider->user) {
                $provider = $booking->serviceProvider->user;

                Mail::to($provider->email)
                    ->send(new BookingStatusUpdatedMail($booking, 'provider', $currentStatus, $nextStatus));

                // DB Store
                $notification = Notification::create([
                    'user_id' => $provider->id,
                    'device_token' => $provider->device_token ?? '',
                    'title' => $providerTitle,
                    'body' => $providerBody,
                ]);

                // Firebase Push
                if ($provider->device_token) {
                    $firebase->sendNotification($provider->device_token, $notification->title, $notification->body, $provider->id);
                }
            }

            return response()->json([
                'status' => true,
                'message' => "Booking status updated from {$currentStatus} to {$nextStatus}",
                'data' => $booking->fresh(),
            ], 200);
        });
    }



    public function getBookingsByUserApi(Request $request)
    {
        $serviceUserId = $this->ownServiceUserId();
        if (! $serviceUserId) {
            $this->deny('Only a service user can view their bookings.');
        }

        $bookings = Booking::with(['serviceProvider', 'servicesAndPricing'])
            ->where('service_user_id', $serviceUserId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Bookings fetched successfully',
            'data' => $bookings,
        ], 200);
    }

}
