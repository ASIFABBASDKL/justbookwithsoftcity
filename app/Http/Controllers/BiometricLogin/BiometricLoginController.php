<?php

namespace App\Http\Controllers\BiometricLogin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BiometricLogin;
use App\Models\User;
use Illuminate\Support\Str;

class BiometricLoginController extends Controller
{
    /**
     * Enable / Store biometric login
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_id' => 'required|string|max:255',
            'device_name' => 'required|string|max:255',
            'biometric_token' => 'required|string|max:255', // ✅ ab token request se aayega
        ]);

        // ✅ Create or Update record
        $record = BiometricLogin::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'device_id' => $request->device_id,
            ],
            [
                'device_name' => $request->device_name,
                'biometric_token' => $request->biometric_token, // ✅ request ka token use hoga
                'is_active' => true,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Biometric login enabled successfully',
            'data' => [
                'user_id' => $record->user_id,
                'device_id' => $record->device_id,
                'device_name' => $record->device_name,
                'biometric_token' => $record->biometric_token,
            ]
        ], 200);
    }
   public function verify(Request $request)
{
    $request->validate([
        'biometric_token' => 'required|string|max:255',
    ]);

    // 🔹 Find biometric record by token
    $record = BiometricLogin::where('biometric_token', $request->biometric_token)
        ->where('is_active', true)
        ->first();

    if (!$record) {
        return response()->json([
            'status' => false,
            'message' => 'Biometric verification failed',
        ], 401);
    }

    // ✅ Get user with relations (same as loginApi)
    $user = User::with(['serviceProvider.wallet', 'serviceUser'])->find($record->user_id);

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
        'message' => 'Biometric login successful',
        'data' => $responseData,
    ], 200);
}


    public function deactivate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $record = BiometricLogin::where('user_id', $request->user_id)->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'No biometric record found for this user',
            ], 404);
        }

        // Nullify device info and deactivate
        $record->update([
            'device_id'       => null,
            'device_name'     => null,
            'biometric_token' => null,
            'is_active'       => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Biometric login deactivated successfully',
        ], 200);
    }


}
