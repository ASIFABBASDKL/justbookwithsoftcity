<?php

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Show Wallet for a Service Provider (single record)
     */
    public function showWalletApi($service_provider_id)
    {
        $wallet = Wallet::with('serviceProvider')
                        ->where('service_provider_id', $service_provider_id)
                        ->first();

        if (!$wallet) {
            return response()->json([
                'status'  => false,
                'message' => 'Wallet not found for this provider.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'wallet' => $wallet, // 🔹 return full wallet model
        ], 200);
    }
}
