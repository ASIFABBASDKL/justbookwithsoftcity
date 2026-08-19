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
            'service_user_id'     => 'nullable|exists:service_users,id',
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'payment_method'      => 'required|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_name'        => 'nullable|string|max:255',
            'account_title'       => 'nullable|string|max:255',
            'account_number'      => 'nullable|string|max:255',
            'sort_no'             => 'nullable|string|max:255',
        ]);

        $payment = Payment::create($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'Payment method stored successfully!',
            'data'    => $payment,
        ], 200);
    }
    
    public function getPaymentsByProvider($providerId)
    {
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
        'service_user_id'     => 'nullable|exists:service_users,id',
        'service_provider_id' => 'nullable|exists:service_providers,id',
        'payment_method'      => 'required|string|max:255',
        'account_holder_name' => 'nullable|string|max:255',
        'account_name'        => 'nullable|string|max:255',
        'account_title'       => 'nullable|string|max:255',
        'account_number'      => 'nullable|string|max:255',
        'sort_no'             => 'nullable|string|max:255',
    ]);

    // 🔹 Fetch payment by ID
    $payment = Payment::findOrFail($request->payment_id);

    // 🔹 Check if the payment belongs to the given service_user_id or service_provider_id
    if ($request->service_user_id && $payment->service_user_id != $request->service_user_id) {
        return response()->json([
            'status'  => false,
            'message' => 'This payment does not belong to the given service user.',
        ], 403);
    }

    if ($request->service_provider_id && $payment->service_provider_id != $request->service_provider_id) {
        return response()->json([
            'status'  => false,
            'message' => 'This payment does not belong to the given service provider.',
        ], 403);
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
