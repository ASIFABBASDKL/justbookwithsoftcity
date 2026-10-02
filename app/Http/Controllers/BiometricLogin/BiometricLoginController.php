<?php

namespace App\Http\Controllers\BiometricLogin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BiometricLogin;
use App\Models\User;
use App\Http\Controllers\Auth\AuthController;

class BiometricLoginController extends Controller
{
    /**
     * Enable / Store biometric login
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|max:255',
            'device_name' => 'required|string|max:255',
            'biometric_token' => 'required|string|max:255',
        ]);

        $record = BiometricLogin::updateOrCreate(
            [
                'user_id' => $this->authUser()->id,
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

    $payload = app(AuthController::class)->issueTokenPayload($user);

    return response()->json([
        'status' => true,
        'message' => 'Biometric login successful',
        'data' => $payload,
    ], 200);
}


    public function deactivate(Request $request)
    {
        $record = BiometricLogin::where('user_id', $this->authUser()->id)->first();

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
