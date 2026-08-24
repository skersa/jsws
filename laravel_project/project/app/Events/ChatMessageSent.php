<?php

namespace App\Events;

use App\Models\AuctionChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Canlı yayın sohbeti — ShouldBroadcastNow ile queue worker olmadan anında iletilir.
 */
class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AuctionChatMessage $chat;

    public function __construct(AuctionChatMessage $chat)
    {
        $this->chat = $chat->loadMissing('user');
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('auction.' . $this->chat->auction_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message';
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->chat->id,
            'user_id'   => (int) $this->chat->user_id,
            'user_name' => $this->chat->user?->name ?? 'Kullanıcı',
            'message'   => $this->chat->message,
            'is_seller' => (bool) $this->chat->is_seller,
        ];
    }
}
