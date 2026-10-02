<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentTransaction;
use App\Models\Wallet;
class PaymentTransactionController extends Controller
{
    //
    public function transactionStore(Request $request)
    {
        $data = $request->validate([
            'service_provider_id' => 'required|exists:seller_profiles,id',
            'booking_id' => 'required|exists:bookings,id',
            'transaction_id' => 'required|string|unique:payment_transactions,transaction_id',
            'payment_method' => 'required|in:credit_card,debit_card,paypal,stripe,cash,bank_transfer',
            'amount' => 'required|numeric|min:0',
            'status' => 'in:incoming,withdraw',
            'account_number' => 'nullable|string|max:50',
        ]);

        $this->requireOwnProvider((int) $data['service_provider_id']);

        // 🔹 Ensure wallet exists
        $wallet = Wallet::firstOrCreate(
            ['service_provider_id' => $data['service_provider_id']],
            [
                'total_amount' => 0.00,
                'total_available_amount' => 0.00,
                'total_withdrawal_amount' => 0.00,
            ]
        );

        // 🔹 Default status = incoming
        $data['status'] = $data['status'] ?? 'incoming';
        $data['wallet_id'] = $wallet->id;

        // 🔹 Create Transaction
        $transaction = PaymentTransaction::create($data);

        // 🔹 Wallet Update Logic
        if ($transaction->status === 'incoming') {
            // Add income to wallet
            $wallet->increment('total_amount', (float) $transaction->amount);

        } elseif ($transaction->status === 'withdraw') {
            // Withdraw only if wallet has enough balance
            if ($wallet->total_amount >= $transaction->amount) {
                $wallet->increment('total_withdrawal_amount', (float) $transaction->amount);
                $wallet->decrement('total_amount', (float) $transaction->amount);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient wallet balance ❌',
                ], 400);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment transaction created successfully ✅',
            'data' => [
                'transaction' => $transaction,
                'wallet' => $wallet->fresh()
            ],
        ], 200);
    }
    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'service_provider_id' => 'required|exists:seller_profiles,id',
            'wallet_id' => 'required|exists:wallets,id',
            'transaction_id' => 'required|string|unique:payment_transactions,transaction_id',
            'account_number' => 'required|string|max:50',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:credit_card,debit_card,paypal,stripe,cash,bank_transfer',
        ]);

        $this->requireOwnProvider((int) $data['service_provider_id']);

        // 🔹 Find wallet
        $wallet = Wallet::with('serviceProvider')->find($data['wallet_id']);

        if (!$wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet not found ❌',
            ], 404);
        }

        // 🔹 Check that wallet belongs to given provider
        if ($wallet->service_provider_id != $data['service_provider_id']) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet does not belong to this provider ❌',
            ], 403);
        }

        // 🔹 Check available balance
        if ($wallet->total_available_amount < $data['amount']) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient available balance ❌',
            ], 400);
        }

        // 🔹 Create withdraw transaction
        $transaction = PaymentTransaction::create([
            'service_provider_id' => $wallet->service_provider_id,
            'wallet_id' => $wallet->id,
            'booking_id' => null, // withdraw not linked to booking
            'transaction_id' => $data['transaction_id'],
            'payment_method' => $data['payment_method'],
            'amount' => $data['amount'],
            'status' => 'withdraw',
            'account_number' => $data['account_number'],
        ]);

        // 🔹 Update wallet
        $wallet->decrement('total_available_amount', (float) $data['amount']);
        $wallet->increment('total_withdrawal_amount', (float) $data['amount']);

        return response()->json([
            'status' => true,
            'message' => 'Withdraw successful ✅',
            'data' => [
                'transaction' => $transaction,
                'wallet' => $wallet->fresh(),
            ],
        ], 200);
    }

    public function getAllTransactions($serviceProviderId)
    {
        $this->requireOwnProvider((int) $serviceProviderId);

        $transactions = PaymentTransaction::with(['wallet', 'booking'])
            ->where('service_provider_id', $serviceProviderId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No transactions found ❌',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Transactions fetched successfully ✅',
            'data' => $transactions,
        ], 200);
    }


}
