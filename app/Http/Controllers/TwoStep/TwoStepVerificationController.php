<?php

namespace App\Http\Controllers\TwoStep;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TwoStepVerification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\EmailOtpMail; // Make sure you have this Mailable
use App\Services\SmsService;

class TwoStepVerificationController extends Controller
{
    public function __construct(protected SmsService $sms)
    {
    }
    /**
     * 🔹 Send OTP to email and phone
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'device_id' => 'required|string',
            'device_name' => 'required|string',
        ]);

        $userId = $request->user_id;
        $email = $request->email;
        $phone = $request->phone_number;
        $deviceId = $request->device_id;
        $deviceName = $request->device_name;

        // ✅ Fetch user by id
        $user = User::find($userId);
        if (!$user || $user->email !== $email || $user->phone_number !== $phone) {
            return response()->json([
                'status' => false,
                'message' => 'User info mismatch'
            ], 404);
        }

        // 🔹 Generate OTPs
        $emailOtp = rand(100000, 999999);
        $phoneOtp = rand(100000, 999999);
        $expiry = now()->addMinutes(10);

        // 🔹 Find existing record for user
        $twoStep = TwoStepVerification::where('user_id', $userId)->latest()->first();

        if (!$twoStep) {
            // 🟢 First time call → create record
            $twoStep = TwoStepVerification::create([
                'user_id' => $userId,
                'email' => $email,
                'phone_number' => $phone,
                'device_id' => $deviceId,
                'device_name' => $deviceName,
                'old_device_id' => null,
                'old_device_name' => null,
                'email_otp' => $emailOtp,
                'phone_otp' => $phoneOtp,
                'email_expires_at' => $expiry,
                'phone_expires_at' => $expiry,
                'email_verified' => false,
                'phone_verified' => false,
                'status' => false,
            ]);
        } else {
            if ($twoStep->device_id === $deviceId && $twoStep->device_name === $deviceName) {
                // 🟡 Same device → resend OTP & set status true
                $twoStep->update([
                    'email_otp' => $emailOtp,
                    'phone_otp' => $phoneOtp,
                    'email_expires_at' => $expiry,
                    'phone_expires_at' => $expiry,
                    'status' => true, // ✅ direct status true
                ]);
            } else {
                // 🔴 Different device (but same user) → shift old device to old_device_id/name
                $twoStep->update([
                    'old_device_id' => $twoStep->device_id,
                    'old_device_name' => $twoStep->device_name,
                    'device_id' => $deviceId,
                    'device_name' => $deviceName,
                    'email_otp' => $emailOtp,
                    'phone_otp' => $phoneOtp,
                    'email_expires_at' => $expiry,
                    'phone_expires_at' => $expiry,
                    'status' => true, // ✅ set status true
                ]);
            }
        }

        // 🔹 Send email OTP
        Mail::to($email)->send(new EmailOtpMail($emailOtp));
        $this->sms->sendOtp($phone, (string) $phoneOtp);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
            'user_id' => $userId,
            'device_id' => $twoStep->device_id,
            'device_name' => $twoStep->device_name,
            'old_device_id' => $twoStep->old_device_id,
            'old_device_name' => $twoStep->old_device_name,
        ], 200);
    }
    /**
     * 🔹 Verify Email OTP
     */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'device_id' => 'required|string',
            'email_otp' => 'required|string',
        ]);

        // 🔹 Fetch 2FA record by user + device
        $twoStep = TwoStepVerification::where('user_id', $request->user_id)
            ->where('device_id', $request->device_id)
            ->first();

        if (!$twoStep) {
            return response()->json([
                'status' => false,
                'message' => 'OTP session not found'
            ], 404);
        }

        // 🔹 Check if email OTP expired
        if ($twoStep->isEmailOtpExpired()) {
            return response()->json([
                'status' => false,
                'message' => 'Email OTP expired'
            ], 400);
        }

        // 🔹 Check OTP match
        if ($twoStep->email_otp !== $request->email_otp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Email OTP'
            ], 400);
        }

        // 🔹 Mark email verified
        $twoStep->email_verified = true;

        // 🔹 Update overall status (both verified)
        $twoStep->updateStatus();

        $twoStep->save();

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully',
            'user_id' => $twoStep->user_id,
            'device_id' => $twoStep->device_id,
            'device_name' => $twoStep->device_name,
        ]);
    }
    /**
     * 🔹 Verify Phone OTP
     */
    public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'device_id' => 'required|string',
            'phone_otp' => 'required|string',
        ]);

        // 🔹 Fetch 2FA record by user + device
        $twoStep = TwoStepVerification::where('user_id', $request->user_id)
            ->where('device_id', $request->device_id)
            ->first();

        if (!$twoStep) {
            return response()->json([
                'status' => false,
                'message' => 'OTP session not found'
            ], 404);
        }

        // 🔹 Check if phone OTP expired
        if ($twoStep->isPhoneOtpExpired()) {
            return response()->json([
                'status' => false,
                'message' => 'Phone OTP expired'
            ], 400);
        }

        // 🔹 Check OTP match
        if ($twoStep->phone_otp !== $request->phone_otp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Phone OTP'
            ], 400);
        }

        // 🔹 Mark phone verified
        $twoStep->phone_verified = true;

        // 🔹 Update overall status (both verified)
        $twoStep->updateStatus();

        $twoStep->save();

        return response()->json([
            'status' => true,
            'message' => 'Phone verified successfully',
            'user_id' => $twoStep->user_id,
            'device_id' => $twoStep->device_id,
            'device_name' => $twoStep->device_name,
        ]);
    }
    public function getTwoStepStatus($user_id)
    {
        $this->requireOwnUserId((int) $user_id);
        if (!User::where('id', $user_id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // 🔹 Fetch latest 2FA record for this user
        $twoStep = TwoStepVerification::where('user_id', $user_id)
            ->latest()
            ->first();

        if (!$twoStep) {
            return response()->json([
                'status' => false,
                'message' => 'No 2FA record found for this user'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => '2FA status retrieved successfully',
            'data' => [
                'user_id' => $twoStep->user_id,
                'device_id' => $twoStep->device_id,
                'device_name' => $twoStep->device_name,
                'email_verified' => $twoStep->email_verified,
                'phone_verified' => $twoStep->phone_verified,
                'overall_status' => $twoStep->status,
                'email_expires_at' => $twoStep->email_expires_at,
                'phone_expires_at' => $twoStep->phone_expires_at,
            ]
        ], 200);
    }
    public function deactivateTwoStepStatus($user_id)
    {
        $this->requireOwnUserId((int) $user_id);

        $user = User::find($user_id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // 🔹 Fetch all 2FA records for this user
        $twoStepRecords = TwoStepVerification::where('user_id', $user_id)->get();

        if ($twoStepRecords->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No 2FA records found for this user'
            ], 404);
        }

        // 🔹 Set only status to false
        foreach ($twoStepRecords as $twoStep) {
            $twoStep->update([
                'status' => false,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Two-step verification Deactive Now Again verified',
            'user_id' => $user_id
        ], 200);
    }



}
