<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $auctionIds = $user->auctions()->pluck('id');

        $bidsReceived = Bid::whereIn('auction_id', $auctionIds);

        $soldCount     = $user->auctions()->where('status', 'sold')->count();
        $endedCount    = $user->auctions()->whereIn('status', ['sold', 'ended'])->count();
        $sellerOrders  = Order::where('seller_id', $user->id);

        $stats = [
            'auctions'            => $user->auctions()->count(),
            'active'              => $user->auctions()->where('status', 'active')->count(),
            'bids'                => (clone $bidsReceived)->count(),
            'sales'               => $soldCount,

            'auctions_this_month' => $user->auctions()->where('created_at', '>=', now()->startOfMonth())->count(),
            'active_this_week'    => $user->auctions()->where('status', 'active')->where('created_at', '>=', now()->startOfWeek())->count(),
            'bids_today'          => (clone $bidsReceived)->where('created_at', '>=', now()->startOfDay())->count(),
            'sales_this_month'    => $user->auctions()->where('status', 'sold')->where('updated_at', '>=', now()->startOfMonth())->count(),

            'completion_rate'     => $endedCount > 0 ? round(($soldCount / $endedCount) * 100) : 0,
            'seller_rating'       => number_format($user->sellerRating(), 1),
            'review_count'        => $user->sellerReviewCount(),
            'avg_price'           => (float) $user->auctions()->avg('current_price'),

            'earned_this_month'   => (float) (clone $sellerOrders)->whereIn('status', ['paid', 'completed', 'shipped'])
                                        ->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'pending_balance'     => (float) (clone $sellerOrders)->where('status', 'paid')->sum('amount'),
        ];

        // En çok teklif alan ilanlar
        $topBidAuctions = $user->auctions()
            ->withCount('bids')
            ->orderByDesc('bids_count')
            ->take(5)
            ->get()
            ->filter(fn($a) => $a->bids_count > 0)
            ->values();

        // Son aktivite: alınan son teklifler
        $recentActivities = (clone $bidsReceived)
            ->with(['user', 'auction'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($bid) => (object) [
                'color'      => '#10b981',
                'text'       => 'Yeni teklif: <strong>' . e(Str::limit($bid->auction?->title ?? 'İlan', 26)) . '</strong> — '
                                . number_format((float) $bid->amount, 0, ',', '.') . ' ₺',
                'created_at' => $bid->created_at,
            ]);

        // Son 30 gün: günlük alınan teklif tutarı (satış performansı grafiği)
        $start = now()->subDays(29)->startOfDay();
        $daily = (clone $bidsReceived)
            ->where('created_at', '>=', $start)
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(amount) as total'))
            ->groupBy('d')
            ->pluck('total', 'd');

        $chartData = [];
        $chartLabels = [];
        for ($i = 0; $i < 30; $i++) {
            $day = (clone $start)->addDays($i);
            $key = $day->format('Y-m-d');
            $chartData[]   = (float) ($daily[$key] ?? 0);
            $chartLabels[] = $day->format('d.m');
        }

        $latestAuctions = $user->auctions()->latest()->take(5)->get();

        $notifications = $user->notifications()->latest()->take(5)->get();

        $walletBalance = (float) ($user->balance ?? 0);

        // Canlı yayına başlanabilecek aktif ilanlar (henüz canlı olmayanlar)
        $broadcastableAuctions = $user->auctions()
            ->where('status', 'active')
            ->where('is_live', false)
            ->where('stream_mode', 'live')
            ->orderBy('ends_at')
            ->take(6)
            ->get();

        // Halihazırda canlı olan ilanlar
        $liveAuctions = $user->auctions()
            ->where('is_live', true)
            ->where('status', 'active')
            ->get();

        $sMap = [
            'active' => 'pf-badge-success', 'draft' => 'pf-badge-warning', 'ended' => 'pf-badge-dark',
            'sold' => 'pf-badge-cyan', 'cancelled' => 'pf-badge-danger', 'rejected' => 'pf-badge-danger',
        ];
        $sLbl = [
            'active' => 'Aktif', 'draft' => 'Taslak', 'ended' => 'Bitti',
            'sold' => 'Satıldı', 'cancelled' => 'İptal', 'rejected' => 'Reddedildi',
        ];

        return \Inertia\Inertia::render('Seller/Dashboard', [
            'sellerName'   => $user->name,
            'stats'        => $stats,
            'walletBalance'=> number_format($walletBalance, 2, ',', '.') . ' ₺',
            'walletPct'    => $walletBalance > 0 ? min(100, round(($walletBalance / 10000) * 100)) : 3,
            'chartData'    => $chartData,
            'chartLabels'  => $chartLabels,
            'liveAuctions' => $liveAuctions->map(fn ($la) => [
                'title'         => Str::limit($la->title, 20),
                'broadcast_url' => route('seller.auctions.broadcast', $la),
            ])->values(),
            'broadcastableAuctions' => $broadcastableAuctions->map(fn ($ba) => [
                'title'         => Str::limit($ba->title, 24),
                'title_short'   => Str::limit($ba->title, 18),
                'broadcast_url' => route('seller.auctions.broadcast', $ba),
            ])->values(),
            'broadcastableCount' => $broadcastableAuctions->count(),
            'topBidAuctions' => $topBidAuctions->map(fn ($a) => [
                'title' => Str::limit($a->title, 32),
                'bids'  => $a->bids_count,
            ])->values(),
            'recentActivities' => collect($recentActivities)->map(fn ($act) => [
                'color' => $act->color,
                'text'  => $act->text,
                'time'  => $act->created_at->diffForHumans(),
            ])->values(),
            'latestAuctions' => $latestAuctions->map(fn ($a) => [
                'title'        => Str::limit($a->title, 38),
                'title_short'  => Str::limit($a->title, 26),
                'cover_url'    => $a->coverUrl(),
                'created_ago'  => $a->created_at->diffForHumans(),
                'display_price'=> $a->displayPrice(),
                'status_class' => $sMap[$a->status] ?? 'pf-badge-dark',
                'status_label' => $sLbl[$a->status] ?? ucfirst($a->status),
                'bid_count'    => $a->bidCount(),
                'ends_ago'     => ($a->status === 'active' && $a->ends_at) ? $a->ends_at->diffForHumans() : '—',
            ])->values(),
            'links' => [
                'auctions_index' => route('seller.auctions.index'),
                'auctions_create'=> route('seller.auctions.create'),
                'profile_edit'   => route('seller.profile.edit'),
                'balance_index'  => route('general.balance.index'),
                'withdraw'       => route('general.balance.withdraw.create'),
            ],
        ]);
    }
}
