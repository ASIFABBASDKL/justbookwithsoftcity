<?php

namespace App\Services;

use App\Models\ConnectBalance;
use App\Models\ConnectTransaction;
use App\Models\PlatformSetting;
use App\Models\Proposal;
use App\Models\SellerProfile;

class ConnectService
{
    public function ensure(SellerProfile $seller): ConnectBalance
    {
        return ConnectBalance::firstOrCreate(
            ['seller_id' => $seller->id],
            [
                'balance' => (int) PlatformSetting::number('monthly_free_connects', 10),
                'monthly_free' => (int) PlatformSetting::number('monthly_free_connects', 10),
                'last_refill_at' => now(),
            ]
        );
    }

    public function spend(SellerProfile $seller, int $amount, string $reference): void
    {
        $balance = $this->ensure($seller);
        if ($balance->balance < $amount) {
            abort(response()->json(['status' => false, 'message' => 'Not enough connects.'], 422));
        }
        $balance->decrement('balance', $amount);
        ConnectTransaction::create([
            'seller_id' => $seller->id,
            'amount' => -$amount,
            'type' => 'spend',
            'reference' => $reference,
        ]);
    }

    public function refund(SellerProfile $seller, int $amount, string $reference): void
    {
        $balance = $this->ensure($seller);
        $balance->increment('balance', $amount);
        ConnectTransaction::create([
            'seller_id' => $seller->id,
            'amount' => $amount,
            'type' => 'refund',
            'reference' => $reference,
        ]);
    }

    public function refundRejected(Proposal $accepted): void
    {
        Proposal::where('job_id', $accepted->job_id)
            ->where('id', '!=', $accepted->id)
            ->where('connects_spent', '>', 0)
            ->get()
            ->each(function (Proposal $p) {
                $this->refund($p->seller, (int) $p->connects_spent, 'job:'.$p->job_id);
            });
    }

    public function monthlyRefill(): int
    {
        $free = (int) PlatformSetting::number('monthly_free_connects', 10);
        $count = 0;
        ConnectBalance::query()->each(function (ConnectBalance $row) use ($free, &$count) {
            if ($row->last_refill_at && $row->last_refill_at->gt(now()->subMonth())) {
                return;
            }
            $row->increment('balance', $free);
            $row->update(['last_refill_at' => now(), 'monthly_free' => $free]);
            ConnectTransaction::create([
                'seller_id' => $row->seller_id,
                'amount' => $free,
                'type' => 'free',
                'reference' => 'monthly',
            ]);
            $count++;
        });

        return $count;
    }

    public function purchase(SellerProfile $seller, int $amount): ConnectBalance
    {
        $balance = $this->ensure($seller);
        $balance->increment('balance', $amount);
        ConnectTransaction::create([
            'seller_id' => $seller->id,
            'amount' => $amount,
            'type' => 'purchase',
            'reference' => 'sim_purchase',
        ]);

        return $balance->fresh();
    }
}
