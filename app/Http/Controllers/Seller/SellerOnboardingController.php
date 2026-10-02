<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SellerOnboardingController extends Controller
{
    public function becomeSeller(Request $request, AuthController $auth)
    {
        $user = $request->user();
        $already = $user->is_seller && $user->sellerProfile;

        $profile = $user->becomeSeller();
        $user->refresh()->load(['sellerProfile.wallet', 'buyerProfile']);

        return response()->json([
            'status' => true,
            'message' => $already
                ? 'You are already a seller.'
                : 'Seller profile created. You can now offer gigs.',
            'data' => array_merge($auth->profilePayload($user), [
                'seller_profile' => $profile,
            ]),
        ], $already ? 200 : 201);
    }
}
