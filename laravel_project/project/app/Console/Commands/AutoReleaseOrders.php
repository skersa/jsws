<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class AutoReleaseOrders extends Command
{
    protected $signature = 'orders:auto-release';

    protected $description = 'Teslim onayı verilmemiş kargolu siparişleri süre dolunca otomatik tamamlar';

    public function handle(OrderService $orders): int
    {
        $count = $orders->autoReleaseExpired();
        $this->info("{$count} sipariş otomatik tamamlandı.");
        return self::SUCCESS;
    }
}
