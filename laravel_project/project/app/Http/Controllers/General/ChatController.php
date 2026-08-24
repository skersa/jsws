<?php

namespace App\Http\Controllers\General;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    /** Sohbet mesajlarını çeker (polling). after=son görülen id */
    public function poll(Request $request, Auction $auction)
    {
        $after = (int) $request->query('after', 0);

        $messages = AuctionChatMessage::where('auction_id', $auction->id)
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->with('user:id,name')
            ->orderBy('id')
            ->take(50)
            ->get()
            ->map(fn (AuctionChatMessage $m) => $this->present($m));

        return response()->json([
            'messages' => $messages,
            'live'     => (bool) $auction->is_live,
            'status'   => $auction->status,
        ]);
    }

    /** Yeni mesaj gönderir — spam koruması ile. */
    public function store(Request $request, Auction $auction)
    {
        // Yayın bitmişse sohbet kapalı (kaynak tasarrufu)
        if (in_array($auction->status, ['ended', 'sold', 'cancelled'], true)) {
            return response()->json(['message' => 'Bu yayın sona erdi, sohbet kapalı.'], 422);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:300'],
        ]);

        $text = trim(preg_replace('/\s+/', ' ', $data['message']));
        if ($text === '') {
            return response()->json(['message' => 'Boş mesaj gönderilemez.'], 422);
        }

        $userId    = (int) auth()->id();
        $isSeller  = $userId === (int) $auction->user_id;

        // ── SPAM KORUMASI ──
        // 1) Mesajlar arası min 2 sn (satıcı hariç)
        $throttleKey = "chat:throttle:{$auction->id}:{$userId}";
        if (! $isSeller && Cache::get($throttleKey)) {
            return response()->json(['message' => 'Çok hızlı yazıyorsun, birkaç saniye bekle.'], 429);
        }

        // 2) 10 sn içinde en fazla 5 mesaj
        $burstKey   = "chat:burst:{$auction->id}:{$userId}";
        $burstCount = (int) Cache::get($burstKey, 0);
        if (! $isSeller && $burstCount >= 5) {
            return response()->json(['message' => 'Spam koruması: kısa süre bekleyip tekrar dene.'], 429);
        }

        // 3) Ard arda aynı mesajı engelle
        $last = AuctionChatMessage::where('auction_id', $auction->id)
            ->where('user_id', $userId)
            ->latest('id')
            ->first();
        if ($last && mb_strtolower($last->message) === mb_strtolower($text)) {
            return response()->json(['message' => 'Aynı mesajı tekrar gönderemezsin.'], 422);
        }

        $chat = AuctionChatMessage::create([
            'auction_id' => $auction->id,
            'user_id'    => $userId,
            'message'    => $text,
            'is_seller'  => $isSeller,
        ]);

        if (! $isSeller) {
            Cache::put($throttleKey, 1, now()->addSeconds(2));
            Cache::put($burstKey, $burstCount + 1, now()->addSeconds(10));
        }

        broadcast(new ChatMessageSent($chat))->toOthers();

        return response()->json($this->present($chat->load('user:id,name')));
    }

    private function present(AuctionChatMessage $m): array
    {
        return [
            'id'        => $m->id,
            'user_id'   => (int) $m->user_id,
            'user_name' => $m->user?->name ?? 'Kullanıcı',
            'message'   => $m->message,
            'is_seller' => (bool) $m->is_seller,
        ];
    }
}
