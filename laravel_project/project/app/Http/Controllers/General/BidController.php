<?php

namespace App\Http\Controllers\General;

use App\Events\BidPlaced;
use App\Http\Controllers\Controller;
use App\Console\Commands\CloseAuctions;
use App\Models\Auction;
use App\Models\Bid;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class BidController extends Controller
{
    public function store(Request $request, Auction $auction)
    {
        if (! $auction->isActive()) {
            return response()->json(['message' => 'Bu müzayede aktif değil.'], 422);
        }

        if ($auction->user_id === auth()->id()) {
            return response()->json(['message' => 'Kendi ilanınıza teklif veremezsiniz.'], 422);
        }

        $minAmount = (float) $auction->current_price + (float) $auction->min_bid_increment;

        try {
            $request->validate([
                'amount' => "required|numeric|min:{$minAmount}",
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors()['amount'][0] ?? 'Geçersiz teklif.',
            ], 422);
        }

        $bid = Bid::create([
            'auction_id' => $auction->id,
            'user_id'    => auth()->id(),
            'amount'     => $request->amount,
            'ip_address' => $request->ip(),
        ]);

        $auction->update(['current_price' => $request->amount]);

        broadcast(new BidPlaced($bid))->toOthers();

        return response()->json([
            'bid_id'        => $bid->id,
            'bidder_id'     => (int) auth()->id(),
            'bidder_name'   => auth()->user()->name,
            'amount'        => (float) $bid->amount,
            'display_price' => number_format($bid->amount, 0, ',', '.') . ' ₺',
            'total_bids'    => $auction->fresh()->bids()->count(),
        ]);
    }

    public function show(Auction $auction)
    {
        $auction->increment('view_count');
        $auction->load(['images', 'cover', 'bids.user', 'category', 'user']);

        $seller = $auction->user;
        $me = auth()->id();
        $inc = (float) $auction->min_bid_increment;
        $minBid = (float) $auction->current_price + $inc;
        $fmt = fn ($n) => number_format((float) $n, 0, ',', '.') . ' ₺';

        $statusMap = [
            'draft' => ['Bekliyor', 'warning'], 'active' => ['Aktif', 'success'],
            'rejected' => ['Reddedildi', 'danger'], 'ended' => ['Bitti', 'danger'],
            'sold' => ['Satıldı', 'seller'], 'cancelled' => ['İptal', 'warning'],
        ];
        [$statusLabel, $statusType] = $statusMap[$auction->status] ?? ['—', 'info'];

        $ratingVal = $seller->sellerRating();
        $r = round($ratingVal * 2) / 2;
        $stars = [];
        for ($i = 1; $i <= 5; $i++) {
            $stars[] = $r >= $i ? 'full' : ($r >= $i - 0.5 ? 'half' : 'empty');
        }

        $data = [
            'id' => $auction->id,
            'slug' => $auction->slug,
            'title' => $auction->title,
            'title_70' => \Illuminate\Support\Str::limit($auction->title, 70),
            'title_30' => \Illuminate\Support\Str::limit($auction->title, 30),
            'status' => $auction->status,
            'status_label' => $statusLabel,
            'status_type' => $statusType,
            'is_active' => $auction->isActive(),
            'has_finished' => $auction->hasFinished(),
            'is_live' => (bool) $auction->is_live,
            'location' => $auction->location,
            'category_name' => $auction->category?->name,
            'cover_url' => $auction->cover?->url() ?? asset('assets/media/placeholder.svg'),
            'images' => $auction->images->map(fn ($img) => ['url' => $img->url(), 'is_cover' => (bool) $img->is_cover])->values(),
            'description' => $auction->description,
            'condition_label' => match ($auction->condition) { 'new' => 'Sıfır', 'used' => 'İkinci El', 'refurbished' => 'Yenilenmiş', default => '—' },
            'min_increment_fmt' => $fmt($inc),
            'starting_price_fmt' => $fmt($auction->starting_price),
            'starts_at_fmt' => $auction->starts_at->format('d.m.Y H:i'),
            'ends_at_fmt' => $auction->ends_at->format('d.m.Y H:i'),
            'view_count_fmt' => number_format($auction->view_count) . ' kez',
            'display_price' => $auction->displayPrice(),
            'bid_count' => $auction->bidCount(),
            'buy_now_price_fmt' => $auction->buy_now_price ? $fmt($auction->buy_now_price) : null,
            'uses_promo_video' => $auction->usesPromoVideo(),
            'is_direct_video' => $auction->isDirectVideoFile(),
            'promo_video_url' => $auction->promo_video_url,
            'embed_video_url' => $auction->embedVideoUrl(),
            'min_bid' => $minBid,
            'min_bid_fmt' => $fmt($minBid),
            'quick' => [
                ['inc_fmt' => $fmt($inc), 'val' => $minBid, 'val_fmt' => $fmt($minBid)],
                ['inc_fmt' => $fmt($inc * 5), 'val' => $minBid + $inc * 4, 'val_fmt' => $fmt($minBid + $inc * 4)],
                ['inc_fmt' => $fmt($inc * 10), 'val' => $minBid + $inc * 9, 'val_fmt' => $fmt($minBid + $inc * 9)],
            ],
            'seller' => [
                'id' => $seller->id,
                'name' => $seller->name,
                'username' => $seller->username,
                'profile_img' => $seller->profile_img,
                'rating_fmt' => number_format($ratingVal, 1),
                'review_count' => $seller->sellerReviewCount(),
                'stars' => $stars,
                'profile_url' => route('profile.public', $seller->username),
            ],
            'bids' => $auction->bids->take(15)->values()->map(fn ($bid, $index) => [
                'name' => $bid->user->name,
                'amount_fmt' => number_format($bid->amount, 0, ',', '.') . ' ₺',
                'time' => $bid->created_at->diffForHumans(),
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($bid->user->name) . '&size=32&background=155eef&color=fff',
                'is_top' => $index === 0,
                'rank' => $index + 1,
                'rank_class' => $index === 0 ? 'r1' : ($index === 1 ? 'r2' : ($index === 2 ? 'r3' : 'rn')),
            ]),
            'is_owner' => $me === $auction->user_id,
        ];

        $config = [
            'auction_id' => (int) $auction->id,
            'min_increment' => (int) $auction->min_bid_increment,
            'bid_url' => route('bids.store', $auction),
            'csrf' => csrf_token(),
            'seller_id' => (int) $auction->user_id,
            'remaining_secs' => (int) max(0, $auction->ends_at->diffInSeconds(now(), false) * -1),
            'live_state_url' => route('auctions.live-state', $auction),
            'chat_poll_url' => route('auctions.chat.poll', $auction),
            'chat_store_url' => route('auctions.chat.store', $auction),
            'is_finished' => $auction->hasFinished() ? '1' : '0',
            'uses_video' => $auction->usesPromoVideo() ? '1' : '0',
            'last_bid_id' => (int) ($auction->bids->max('id') ?? 0),
            'sold_handled' => in_array($auction->status, ['sold', 'ended']) ? '1' : '0',
            'is_auth' => auth()->check() ? '1' : '0',
            'current_user_id' => auth()->check() ? (int) auth()->id() : '',
            'current_min' => (int) ($auction->current_price + $auction->min_bid_increment),
            'messages_start_url' => route('messages.start'),
            'login_url' => route('login'),
        ];

        return \Inertia\Inertia::render('Auctions/Show', ['a' => $data, 'config' => $config]);
    }

    /**
     * Canlı durum (polling). WebSocket olmadan gerçek-zamanlı akış sağlar:
     * yeni teklifler, güncel fiyat, izleyici sayısı ve satış durumu.
     */
    public function liveState(Request $request, Auction $auction)
    {
        // Cron olmayan ortamda süresi dolan açık artırmaları fırsatçı kapat
        CloseAuctions::runThrottled(app(OrderService::class));

        $viewerCount = $this->trackViewer($request, $auction);

        $auction->refresh();

        $after = (int) $request->query('after', 0);

        $newBids = $auction->bids()
            ->reorder()
            ->where('bids.id', '>', $after)
            ->with('user:id,name')
            ->orderBy('bids.id')
            ->take(25)
            ->get()
            ->map(fn (Bid $b) => [
                'bid_id'        => $b->id,
                'bidder_id'     => (int) $b->user_id,
                'bidder_name'   => $b->user?->name ?? 'Kullanıcı',
                'amount'        => (float) $b->amount,
                'display_price' => number_format($b->amount, 0, ',', '.') . ' ₺',
            ]);

        $sold = null;
        if (in_array($auction->status, ['sold', 'ended'], true)) {
            $win = $auction->winning_bid_id
                ? Bid::with('user:id,name')->find($auction->winning_bid_id)
                : null;
            $sold = [
                'status'      => $auction->status,
                'buyer_name'  => $win?->user?->name,
                'display_price' => $win ? number_format($win->amount, 0, ',', '.') . ' ₺' : null,
            ];
        }

        return response()->json([
            'status'        => $auction->status,
            'is_live'       => (bool) $auction->is_live,
            'stream_mode'   => $auction->stream_mode,
            'current_price' => (float) $auction->current_price,
            'display_price' => number_format($auction->current_price, 0, ',', '.') . ' ₺',
            'total_bids'    => $auction->bids()->count(),
            'viewer_count'  => $viewerCount,
            'new_bids'      => $newBids,
            'sold'          => $sold,
            'server_time'   => now()->toIso8601String(),
            'ends_at'       => optional($auction->ends_at)->toIso8601String(),
        ]);
    }

    /** İzleyici presence'ini cache üzerinden takip eder (satıcı hariç). */
    private function trackViewer(Request $request, Auction $auction): int
    {
        $key = "auction:{$auction->id}:viewers";
        $viewers = Cache::get($key, []);

        $now = now()->timestamp;
        $me  = auth()->check() ? (string) auth()->id() : 'g:' . $request->session()->getId();

        $viewers[$me] = $now;

        // 15 sn'den eski izleyicileri düş
        $viewers = array_filter($viewers, fn ($ts) => ($now - $ts) <= 15);

        Cache::put($key, $viewers, now()->addMinute());

        $sellerKey = (string) $auction->user_id;
        $count = count(array_filter(array_keys($viewers), fn ($id) => $id !== $sellerKey));

        return $count;
    }
}
