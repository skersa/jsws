<?php

namespace App\Http\Controllers\Seller;

use App\Events\AuctionSold;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use App\Services\OrderService;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function show(Auction $auction)
    {
        abort_unless($auction->user_id === auth()->id(), 403);

        // Yayın moduna geç (canlı). is_live YALNIZCA kamera başlatılınca true olur
        // (liveStatus endpoint'i ile) — böylece izleyici "Canlı İzle" sekmesini
        // yalnızca gerçekten yayın varken görür.
        if (! $auction->hasFinished() && $auction->stream_mode !== 'live') {
            $auction->update(['stream_mode' => 'live']);
        }

        $bids = $auction->bids()->with('user:id,name')->orderByDesc('amount')->take(50)->get();
        $topBid = $bids->first();

        return \Inertia\Inertia::render('Seller/Broadcast', [
            'auction' => [
                'id'          => $auction->id,
                'slug'        => $auction->slug,
                'title'       => $auction->title,
                'is_live'     => (bool) $auction->is_live,
                'has_finished' => $auction->hasFinished(),
                'top_amount'  => $topBid?->amount,
                'bid_count'   => $bids->count(),
            ],
            'bids' => $bids->map(fn (Bid $b) => [
                'id'     => $b->id,
                'amount' => (float) $b->amount,
                'name'   => $b->user?->name ?? 'Kullanıcı',
                'time'   => optional($b->created_at)->format('H:i'),
            ])->values(),
            'routes' => [
                'sell'        => route('seller.auctions.sell', $auction),
                'end'         => route('seller.auctions.end-broadcast', $auction),
                'live_status' => route('seller.auctions.live-status', $auction),
                'chat_poll'   => route('auctions.chat.poll', $auction),
                'chat_store'  => route('auctions.chat.store', $auction),
                'view_public' => route('auctions.show', $auction),
            ],
        ]);
    }


    /**
     * Satıcı kamerayı başlatınca/durdurunca canlı durumu günceller.
     * Bu sayede izleyici tarafındaki "Canlı İzle" sekmesi yalnızca yayın açıkken görünür.
     */
    public function liveStatus(Request $request, Auction $auction)
    {
        abort_unless($auction->user_id === auth()->id(), 403);

        $live = $request->boolean('live');

        if ($auction->hasFinished()) {
            $live = false;
        }

        $auction->update([
            'is_live'         => $live,
            'live_started_at' => $live ? ($auction->live_started_at ?? now()) : $auction->live_started_at,
            'live_ended_at'   => $live ? null : now(),
        ]);

        return response()->json(['success' => true, 'is_live' => $live]);
    }

    /**
     * Yayın modunu ve tanıtım videosunu günceller.
     * Satıcı canlı yayın yerine ürün tanıtım videosu ekleyebilir.
     */
    public function streamSettings(Request $request, Auction $auction)
    {
        abort_unless($auction->user_id === auth()->id(), 403);

        $data = $request->validate([
            'stream_mode'     => ['required', 'in:live,video'],
            'promo_video_url' => ['nullable', 'url', 'max:2048', 'required_if:stream_mode,video'],
        ], [
            'promo_video_url.required_if' => 'Tanıtım videosu modu için bir video linki gir.',
            'promo_video_url.url'         => 'Geçerli bir video linki gir (YouTube, Vimeo veya .mp4).',
        ]);

        $update = ['stream_mode' => $data['stream_mode']];

        if ($data['stream_mode'] === 'video') {
            $update['promo_video_url'] = $data['promo_video_url'];
            // Video moduna geçince canlı yayını kapat
            $update['is_live']       = false;
            $update['live_ended_at'] = $auction->is_live ? now() : $auction->live_ended_at;
        } else {
            $update['promo_video_url'] = $request->input('promo_video_url') ?: null;
        }

        $auction->update($update);

        return back()->with('profile_success', 'Yayın ayarların güncellendi.');
    }

    public function sell(Request $request, Auction $auction)
    {
        abort_unless($auction->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'bid_id' => ['required', 'integer', 'exists:bids,id'],
        ]);

        $bid = Bid::where('id', $validated['bid_id'])
            ->where('auction_id', $auction->id)
            ->firstOrFail();

        // Emanet tabanlı sipariş oluştur (kazananı belirle, açık artırmayı kapat)
        $order = $this->orders->createFromWinningBid($auction, $bid);

        // Satış sonrası canlı yayını da kapat
        $auction->update(['is_live' => false, 'live_ended_at' => now()]);

        broadcast(new AuctionSold(
            auction      : $auction,
            buyerName    : $bid->user->name,
            amount       : $bid->amount,
            displayPrice : number_format($bid->amount, 0, ',', '.').' ₺',
        ));

        return response()->json([
            'success'      => true,
            'winner_name'  => $bid->user->name,
            'amount'       => $bid->amount,
            'order_number' => $order->order_number,
        ]);
    }

    // web.php'de 'end-broadcast' route'u bu metoda bağlı
    public function endBroadcast(Auction $auction)
    {
        abort_unless($auction->user_id === auth()->id(), 403);

        // Yayını her durumda "canlı değil" yap ki izleyici tarafında socket/polling dursun
        $auction->update([
            'is_live'       => false,
            'live_ended_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
