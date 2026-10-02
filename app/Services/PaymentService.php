<?php

namespace App\Services;

use App\Models\Escrow;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Payout;
use App\Models\PlatformSetting;
use App\Models\Wallet;
use Illuminate\Support\Str;

class PaymentService
{
    public function holdEscrow(Order $order, ?int $milestoneId = null): Escrow
    {
        $amount = $milestoneId
            ? (float) $order->milestones()->where('id', $milestoneId)->value('amount')
            : (float) $order->price + (float) $order->extras_total;

        $escrow = Escrow::create([
            'order_id' => $order->id,
            'milestone_id' => $milestoneId,
            'amount' => $amount,
            'status' => 'held',
            'gateway_ref' => 'sim_'.Str::uuid(),
            'held_at' => now(),
        ]);

        $this->ledger(null, $order->id, 'escrow_hold', $amount, 0, 'escrow:'.$escrow->id);

        return $escrow;
    }

    public function releaseEscrow(Order $order): void
    {
        $escrows = $order->escrows()->where('status', 'held')->get();
        $wallet = Wallet::firstOrCreate(
            ['service_provider_id' => $order->seller->sellerProfile->id],
            [
                'total_amount' => 0,
                'total_available_amount' => 0,
                'total_withdrawal_amount' => 0,
                'pending_clearance' => 0,
            ]
        );

        foreach ($escrows as $escrow) {
            $escrow->update(['status' => 'released', 'released_at' => now()]);
            $wallet->increment('total_amount', (float) $order->seller_earning);
            $wallet->increment('pending_clearance', (float) $order->seller_earning);
            $this->ledger($wallet->id, $order->id, 'escrow_release', 0, (float) $order->seller_earning, 'escrow:'.$escrow->id, (float) $wallet->fresh()->pending_clearance);
            $this->ledger($wallet->id, $order->id, 'commission', (float) $order->platform_fee, 0, 'order:'.$order->id, (float) $wallet->fresh()->pending_clearance);
        }

        $order->seller->sellerProfile?->increment('total_earnings', (float) $order->seller_earning);
    }

    public function refundEscrow(Order $order, ?float $amount = null): void
    {
        $escrows = $order->escrows()->where('status', 'held')->get();
        foreach ($escrows as $escrow) {
            $escrow->update(['status' => 'refunded', 'released_at' => now()]);
            $this->ledger(null, $order->id, 'refund', 0, (float) ($amount ?? $escrow->amount), 'escrow:'.$escrow->id);
        }
    }

    public function requestPayout(Wallet $wallet, float $amount, string $method = 'stripe'): Payout
    {
        $min = PlatformSetting::number('min_withdrawal', 20);
        if ($amount < $min) {
            abort(response()->json(['status' => false, 'message' => "Minimum withdrawal is {$min}"], 422));
        }
        if ((float) $wallet->total_available_amount < $amount) {
            abort(response()->json(['status' => false, 'message' => 'Insufficient available balance'], 400));
        }

        $wallet->decrement('total_available_amount', $amount);
        $wallet->increment('total_withdrawal_amount', $amount);

        $payout = Payout::create([
            'seller_id' => $wallet->service_provider_id,
            'amount' => $amount,
            'method' => $method,
            'status' => 'processing',
            'requested_at' => now(),
            'gateway_ref' => 'sim_payout_'.Str::uuid(),
        ]);

        $this->ledger($wallet->id, null, 'withdrawal', $amount, 0, 'payout:'.$payout->id, (float) $wallet->fresh()->total_available_amount);

        $payout->update(['status' => 'paid', 'paid_at' => now()]);

        return $payout->fresh();
    }

    public function createInvoice(Order $order): Invoice
    {
        return Invoice::firstOrCreate(
            ['order_id' => $order->id],
            [
                'invoice_number' => 'INV-'.$order->order_number,
                'buyer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id,
                'amount' => (float) $order->price + (float) $order->extras_total,
                'tax' => 0,
            ]
        );
    }

    public function moveClearedFunds(): int
    {
        $days = (int) PlatformSetting::number('pending_clearance_days', 7);
        $count = 0;
        $wallets = Wallet::where('pending_clearance', '>', 0)->get();
        foreach ($wallets as $wallet) {
            $amount = (float) $wallet->pending_clearance;
            $wallet->decrement('pending_clearance', $amount);
            $wallet->increment('total_available_amount', $amount);
            $this->ledger($wallet->id, null, 'clearance', 0, $amount, 'days:'.$days, (float) $wallet->fresh()->total_available_amount);
            $count++;
        }

        return $count;
    }

    private function ledger(?int $walletId, ?int $orderId, string $type, float $debit, float $credit, ?string $ref, ?float $balance = null): void
    {
        LedgerEntry::create([
            'wallet_id' => $walletId,
            'order_id' => $orderId,
            'type' => $type,
            'debit' => $debit,
            'credit' => $credit,
            'balance_after' => $balance,
            'reference' => $ref,
        ]);
    }
}
