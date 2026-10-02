<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailOtpMail;
use App\Mail\PasswordResetOtpMail;
use App\Mail\PasswordResetSuccessMail;
use App\Models\BuyerProfile;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(protected SmsService $sms)
    {
    }

    public function registerApi(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:user,provider',
            'as_seller' => 'sometimes|boolean',
            'device_token' => 'nullable|string|max:500',
        ]);

        $asSeller = $request->boolean('as_seller') || $request->input('role') === 'provider';

        $user = User::create([
            'fullname' => $request->fullname,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => $request->password,
            'is_buyer' => true,
            'is_seller' => $asSeller,
            'device_token' => $request->device_token,
            'timezone' => 'UTC',
        ]);

        $user->username = $this->uniqueUsername($user);
        $user->save();

        $buyer = $user->ensureBuyerProfile();
        $responseData = [
            'user' => $user->fresh(),
            'buyer_profile' => $buyer,
            'service_user' => $buyer,
        ];

        if ($asSeller) {
            $seller = $user->becomeSeller();
            $responseData['seller_profile'] = $seller;
            $responseData['service_provider'] = $seller;
            $responseData['wallet'] = $seller->wallet;
        }

        $phoneOtp = (string) random_int(100000, 999999);
        cache()->put("phone_otp_{$user->phone_number}", $phoneOtp, now()->addMinutes(10));
        $this->sms->sendOtp($user->phone_number, $phoneOtp);

        $emailOtp = (string) random_int(100000, 999999);
        cache()->put("email_otp_{$user->email}", $emailOtp, now()->addMinutes(10));
        Mail::to($user->email)->send(new EmailOtpMail($emailOtp));

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully. Verify your phone & email.',
            'data' => $responseData,
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'otp' => 'required|numeric',
        ]);

        $cachedOtp = cache()->get("email_otp_{$request->email}");

        if ($cachedOtp && (string) $cachedOtp === (string) $request->otp) {
            $user = User::where('email', $request->email)->first();
            $user->email_verified_at = now();
            $user->save();

            cache()->forget("email_otp_{$request->email}");

            return response()->json([
                'status' => true,
                'message' => 'Email verified successfully',
                'data' => $user,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid or expired OTP',
        ], 422);
    }

    public function verifyPhone(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|exists:users,phone_number',
            'otp' => 'required|numeric',
        ]);

        $cachedOtp = cache()->get("phone_otp_{$request->phone_number}");

        if ($cachedOtp && (string) $cachedOtp === (string) $request->otp) {
            $user = User::where('phone_number', $request->phone_number)->first();
            $user->markPhoneAsVerified();

            cache()->forget("phone_otp_{$request->phone_number}");

            return response()->json([
                'status' => true,
                'message' => 'Phone verified successfully',
                'data' => $user,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid or expired OTP',
        ], 422);
    }

    public function loginApi(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_token' => 'nullable|string|max:500',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (! $user->hasVerifiedPhone()) {
            return response()->json([
                'status' => false,
                'message' => 'Please verify your phone number before login.',
            ], 403);
        }

        if ($request->filled('device_token') && $user->device_token !== $request->device_token) {
            $user->update(['device_token' => $request->device_token]);
        }

        $user->forceFill(['last_seen_at' => now()])->save();

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'data' => $this->issueTokenPayload($user),
        ], 200);
    }

    public function logoutApi(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['serviceProvider.wallet', 'serviceUser']);

        return response()->json([
            'status' => true,
            'data' => $this->profilePayload($user),
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = (string) random_int(1000, 9999);
        cache()->put("password_reset_{$request->email}", $otp, now()->addMinutes(10));
        Mail::to($request->email)->send(new PasswordResetOtpMail($otp));

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your email address',
        ], 200);
    }

    public function verifyPasswordOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
        ]);

        $cachedOtp = cache()->get("password_reset_{$request->email}");

        if ($cachedOtp && (string) $cachedOtp === (string) $request->otp) {
            return response()->json([
                'status' => true,
                'message' => 'OTP verified successfully. You can now reset your password.',
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid or expired OTP',
        ], 422);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $cachedOtp = cache()->get("password_reset_{$request->email}");

        if ($cachedOtp && (string) $cachedOtp === (string) $request->otp) {
            $user = User::where('email', $request->email)->first();
            $user->password = $request->password;
            $user->save();

            cache()->forget("password_reset_{$request->email}");
            $user->tokens()->delete();

            Mail::to($user->email)->send(new PasswordResetSuccessMail($user));

            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully',
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid or expired OTP',
        ], 422);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Old password does not match',
            ], 422);
        }

        $user->password = $request->new_password;
        $user->save();

        $current = $user->currentAccessToken();
        if ($current instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $user->tokens()->where('id', '!=', $current->id)->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',
        ], 200);
    }

    public function getProviderDeviceToken($id)
    {
        $this->requireOwnProvider((int) $id);

        $provider = SellerProfile::with('user')->find($id);
        $token = $provider?->user?->device_token;

        return response()->json([
            'status' => (bool) $token,
            'device_token' => $token,
        ], 200);
    }

    public function getServiceUserDeviceToken($id)
    {
        $this->requireOwnServiceUser((int) $id);

        $serviceUser = BuyerProfile::with('user')->find($id);
        $token = $serviceUser?->user?->device_token;

        return response()->json([
            'status' => (bool) $token,
            'device_token' => $token,
        ], 200);
    }

    public function getAllServiceUsers()
    {
        $users = BuyerProfile::with('user')->get();

        return response()->json([
            'status' => true,
            'message' => 'All service users fetched successfully.',
            'data' => $users,
        ], 200);
    }

    public function getAllServiceProviders()
    {
        $providers = SellerProfile::with(['user', 'wallet'])->get();

        return response()->json([
            'status' => true,
            'message' => 'All service providers fetched successfully.',
            'data' => $providers,
        ], 200);
    }

    /**
     * @return array<string, mixed>
     */
    public function issueTokenPayload(User $user, string $tokenName = 'api'): array
    {
        $user->tokens()->where('name', $tokenName)->delete();
        $plain = $user->createToken($tokenName)->plainTextToken;
        $user->load(['sellerProfile.wallet', 'buyerProfile']);

        return array_merge($this->profilePayload($user), [
            'token' => $plain,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function profilePayload(User $user): array
    {
        $user->loadMissing(['sellerProfile.wallet', 'buyerProfile']);

        $payload = [
            'user' => $user,
            'buyer_profile' => $user->buyerProfile,
            'service_user' => $user->buyerProfile,
            'is_buyer' => (bool) $user->is_buyer,
            'is_seller' => (bool) $user->is_seller,
        ];

        if ($user->is_seller && $user->sellerProfile) {
            $payload['seller_profile'] = $user->sellerProfile;
            $payload['service_provider'] = $user->sellerProfile;
            $payload['wallet'] = $user->sellerProfile->wallet ?? null;
        }

        return $payload;
    }

    protected function uniqueUsername(User $user): string
    {
        $base = Str::slug($user->fullname) ?: 'user';
        $candidate = $base.$user->id;

        $i = 1;
        while (User::where('username', $candidate)->where('id', '!=', $user->id)->exists()) {
            $candidate = $base.$user->id.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
