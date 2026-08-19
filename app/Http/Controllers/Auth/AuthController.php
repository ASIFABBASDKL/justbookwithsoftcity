<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailOtpMail;
use App\Mail\PasswordResetOtpMail;
use App\Mail\PasswordResetSuccessMail;
use App\Models\ServiceProvider;
use App\Models\Wallet;
use App\Models\ServiceUser;
class AuthController extends Controller
{
    /**
     * Register API - create a new user (API).
     */
    public function registerApi(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'in:user,provider',
            'device_token' => 'nullable|string|max:500',
        ]);
    
        $user = User::create([
            'fullname' => $request->fullname,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
            'device_token' => $request->device_token,
        ]);
    
        // ✅ Prepare response data
        $responseData = [
            'user' => $user,
        ];
    
        if ($user->role === 'provider') {
            $serviceProvider = ServiceProvider::create([
                'user_id' => $user->id,
            ]);
    
            $wallet = Wallet::create([
                'service_provider_id' => $serviceProvider->id,
                'total_amount' => 0.00,
                'total_available_amount' => 0.00,
                'total_withdrawal_amount' => 0.00,
            ]);
    
            $responseData['service_provider'] = $serviceProvider;
            $responseData['wallet'] = $wallet;
        } else {
            $serviceUser = ServiceUser::create([
                'user_id' => $user->id,
            ]);
    
            $responseData['service_user'] = $serviceUser;
        }
    
        // ✅ Phone OTP
        $phoneOtp = rand(100000, 999999);
        cache()->put("phone_otp_{$user->phone_number}", $phoneOtp, now()->addMinutes(10));
    
        // ✅ Email OTP
        $emailOtp = rand(100000, 999999);
        cache()->put("email_otp_{$user->email}", $emailOtp, now()->addMinutes(10));
        Mail::to($user->email)->send(new EmailOtpMail($emailOtp));
    
        return response()->json([
            'status' => true,
            'message' => 'User registered successfully. Verify your phone & email.',
            'phone_otp' => $phoneOtp,   // sirf testing ke liye
            'email_otp' => $emailOtp,   // sirf testing ke liye
            'data' => $responseData,
        ], 200);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'otp' => 'required|numeric',
        ]);

        $cachedOtp = cache()->get("email_otp_{$request->email}");

        if ($cachedOtp && $cachedOtp == $request->otp) {
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
        ], 404);
    }
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|exists:users,phone_number',
            'otp' => 'required|numeric',
        ]);

        $cachedOtp = cache()->get("phone_otp_{$request->phone_number}");

        if ($cachedOtp && $cachedOtp == $request->otp) {
            $user = User::where('phone_number', $request->phone_number)->first();
            $user->markPhoneAsVerified(); // ✅ Model method

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
        ], 404);
    }
    /**
     * Login API - authenticate a user (API).
     **/
     
    public function loginApi(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_token' => 'nullable|string|max:500',
        ]);
    
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
    
            // ✅ Prevent login if phone is not verified
            if (!$user->hasVerifiedPhone()) {
                Auth::logout();
                return response()->json([
                    'status' => false,
                    'message' => 'Please verify your phone number before login.',
                ], 403);
            }
    
            // ✅ Update device token
            if ($request->filled('device_token') && $user->device_token !== $request->device_token) {
                $user->update(['device_token' => $request->device_token]);
            }
    
            // ✅ Eager load relations
            $user->load([
                'serviceProvider.wallet', // provider + wallet
                'serviceUser',            // user
            ]);
    
            // ✅ Prepare response
            $responseData = [
                'user' => $user,
            ];
    
            if ($user->role === 'provider' && $user->serviceProvider) {
                $responseData['service_provider'] = $user->serviceProvider;
                $responseData['wallet'] = $user->serviceProvider->wallet ?? null;
            } elseif ($user->role === 'user' && $user->serviceUser) {
                $responseData['service_user'] = $user->serviceUser;
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => $responseData,
            ], 200);
        }
    
        return response()->json([
            'status' => false,
            'message' => 'Invalid credentials',
        ], 404);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Generate 4 digit OTP
        $otp = rand(1000, 9999);

        // Cache me store karo (10 min expiry)
        cache()->put("password_reset_{$request->email}", $otp, now()->addMinutes(10));

        // Mail bhejna
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

        if ($cachedOtp && $cachedOtp == $request->otp) {
            // OTP valid
            return response()->json([
                'status' => true,
                'message' => 'OTP verified successfully. You can now reset your password.',
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid or expired OTP',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $cachedOtp = cache()->get("password_reset_{$request->email}");

        if ($cachedOtp && $cachedOtp == $request->otp) {
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // OTP hatado
            cache()->forget("password_reset_{$request->email}");

            // ✅ Confirmation email bhejo
            Mail::to($user->email)->send(new PasswordResetSuccessMail($user));

            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully',
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid or expired OTP',
        ], 200);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($request->user_id);

        // Check old password
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Old password does not match',
            ], 200);
        }

        // Update new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',
        ], 200);
    }
    public function getProviderDeviceToken($id)
    {
        $provider = ServiceProvider::with('user')->find($id);
        $token = $provider?->user?->device_token;

        return response()->json([
            'status' => (bool) $token,
            'device_token' => $token,
        ], 200);
    }
    public function getServiceUserDeviceToken($id)
    {
        $serviceUser = ServiceUser::with('user')->find($id);
        $token = $serviceUser?->user?->device_token;

        return response()->json([
            'status' => (bool) $token,
            'device_token' => $token,
        ], 200);
    }


    // Fetch all Service Users
    public function getAllServiceUsers()
    {
        $users = ServiceUser::with('user')->get();
    
        return response()->json([
            'status' => true,
            'message' => 'All service users fetched successfully.',
            'data' => $users,
        ], 200);
    }
    
    // Fetch all Service Providers (with wallet + user)
    public function getAllServiceProviders()
    {
        $providers = ServiceProvider::with(['user', 'wallet'])->get();
    
        return response()->json([
            'status' => true,
            'message' => 'All service providers fetched successfully.',
            'data' => $providers,
        ], 200);
    }
    

}
