<?php
namespace App\Events;

use App\Models\Auction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class AuctionSold implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Auction $auction,
        public string  $buyerName,
        public int     $amount,
        public string  $displayPrice,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('auction.' . $this->auction->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'auction.sold';
    }

    public function broadcastWith(): array
    {
        return [
            'buyer_name'    => $this->buyerName,
            'amount'        => $this->amount,
            'display_price' => $this->displayPrice,
        ];
    }
}
