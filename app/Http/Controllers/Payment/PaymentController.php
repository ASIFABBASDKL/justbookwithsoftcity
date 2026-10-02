<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentController extends Controller
{
    //
    public function storePaymentMethodApi(Request $request)
    {
        $request->validate([
            'payment_method'      => 'required|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_name'        => 'nullable|string|max:255',
            'account_title'       => 'nullable|string|max:255',
            'account_number'      => 'nullable|string|max:255',
            'sort_no'             => 'nullable|string|max:255',
        ]);

        $payment = Payment::create(array_merge($request->only([
            'payment_method',
            'account_holder_name',
            'account_name',
            'account_title',
            'account_number',
            'sort_no',
        ]), [
            'service_user_id' => $this->ownServiceUserId(),
            'service_provider_id' => $this->ownServiceProviderId(),
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Payment method stored successfully!',
            'data'    => $payment,
        ], 200);
    }
    
    public function getPaymentsByProvider($providerId)
    {
        $this->requireOwnProvider((int) $providerId);

        $payments = Payment::where('service_provider_id', $providerId)->get();

        return response()->json([
            'status'  => true,
            'message' => $payments->isNotEmpty()
                ? "Payment methods for provider ID {$providerId}"
                : "No payment methods found for provider ID {$providerId}",
            'data'    => $payments,
        ], 200);
    }
    public function getPaymentsByUser($userId)
    {
        $this->requireOwnServiceUser((int) $userId);

        $payments = Payment::where('service_user_id', $userId)->get();

        return response()->json([
            'status'  => true,
            'message' => $payments->isNotEmpty()
                ? "Payment methods for user ID {$userId}"
                : "No payment methods found for user ID {$userId}",
            'data'    => $payments,
        ], 200);
    }
    public function updatePaymentMethodApi(Request $request)
    {
    $request->validate([
        'payment_id'          => 'required|exists:payments,id',
        'payment_method'      => 'required|string|max:255',
        'account_holder_name' => 'nullable|string|max:255',
        'account_name'        => 'nullable|string|max:255',
        'account_title'       => 'nullable|string|max:255',
        'account_number'      => 'nullable|string|max:255',
        'sort_no'             => 'nullable|string|max:255',
    ]);

    // 🔹 Fetch payment by ID
    $payment = Payment::findOrFail($request->payment_id);

    $ownsAsUser = $this->ownServiceUserId() && (int) $payment->service_user_id === (int) $this->ownServiceUserId();
    $ownsAsProvider = $this->ownServiceProviderId() && (int) $payment->service_provider_id === (int) $this->ownServiceProviderId();

    if (! $ownsAsUser && ! $ownsAsProvider) {
        $this->deny();
    }

    // 🔹 Update payment
    $payment->update($request->only([
        'payment_method',
        'account_holder_name',
        'account_name',
        'account_title',
        'account_number',
        'sort_no',
    ]));

    return response()->json([
        'status'  => true,
        'message' => 'Payment method updated successfully!',
        'data'    => $payment,
    ], 200);
}

}
