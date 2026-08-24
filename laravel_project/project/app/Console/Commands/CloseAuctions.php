<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CloseAuctions extends Command
{
    protected $signature = 'auctions:close';

    protected $description = 'Süresi dolan açık artırmaları kapatır, kazananı belirler ve sipariş oluşturur';

    public function handle(OrderService $orders): int
    {
        $due = Auction::where('status', 'active')
            ->where('ends_at', '<=', now())
            ->get();

        $sold = 0;
        $ended = 0;

        foreach ($due as $auction) {
            $topBid = $auction->bids()->reorder()->orderByDesc('amount')->orderBy('created_at')->first();

            $reserve = (float) ($auction->reserve_price ?? 0);

            if (! $topBid || ($reserve > 0 && (float) $topBid->amount < $reserve)) {
                $auction->update(['status' => 'ended']);
                $ended++;
                continue;
            }

            $orders->createFromWinningBid($auction, $topBid);
            $sold++;
        }

        $this->info("Kapatıldı: {$sold} satış, {$ended} satışsız bitti.");

        return self::SUCCESS;
    }

    /** Cron olmayan ortamlar için fırsatçı, throttle'lı kapatma. */
    public static function runThrottled(OrderService $orders): void
    {
        if (! Cache::add('auctions_close_lock', 1, now()->addSeconds(20))) {
            return;
        }

        $due = Auction::where('status', 'active')->where('ends_at', '<=', now())->get();
        foreach ($due as $auction) {
            $topBid = $auction->bids()->reorder()->orderByDesc('amount')->orderBy('created_at')->first();
            $reserve = (float) ($auction->reserve_price ?? 0);
            if (! $topBid || ($reserve > 0 && (float) $topBid->amount < $reserve)) {
                $auction->update(['status' => 'ended']);
                continue;
            }
            $orders->createFromWinningBid($auction, $topBid);
        }
    }
}
