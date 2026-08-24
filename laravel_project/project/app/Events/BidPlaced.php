<?php

namespace App\Events;

use App\Models\Bid;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ShouldBroadcastNow kullanıyoruz → queue worker olmasa bile anında iletilir
 */
class BidPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Bid $bid;

    public function __construct(Bid $bid)
    {
        $this->bid = $bid->loadMissing(['user', 'auction']);
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('auction.' . $this->bid->auction_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'bid.placed';
    }

    public function broadcastWith(): array
    {
        $auction = $this->bid->auction;

        return [
            'bid_id'        => $this->bid->id,
            'bidder_id'     => (int) $this->bid->user_id,
            'bidder_name'   => $this->bid->user->name,
            'amount'        => (float) $this->bid->amount,
            'display_price' => number_format($this->bid->amount, 0, ',', '.') . ' ₺',
            'total_bids'    => $auction->bids()->count(),
        ];
    }
}
