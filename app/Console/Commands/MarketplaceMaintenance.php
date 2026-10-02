<?php

namespace App\Console\Commands;

use App\Services\ConnectService;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class MarketplaceMaintenance extends Command
{
    protected $signature = 'marketplace:maintenance';

    protected $description = 'Clear pending wallet funds and refill monthly connects';

    public function handle(PaymentService $payments, ConnectService $connects): int
    {
        $cleared = $payments->moveClearedFunds();
        $refilled = $connects->monthlyRefill();
        $this->info("Wallets cleared: {$cleared}. Connect refills: {$refilled}.");

        return self::SUCCESS;
    }
}
