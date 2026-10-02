<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class AutoCompleteOrders extends Command
{
    protected $signature = 'orders:auto-complete';

    protected $description = 'Auto-complete delivered orders after the grace period';

    public function handle(OrderService $orders): int
    {
        $late = $orders->markLate();
        $done = $orders->autoCompleteDue();
        $this->info("Late marked: {$late}. Auto-completed: {$done}.");

        return self::SUCCESS;
    }
}
