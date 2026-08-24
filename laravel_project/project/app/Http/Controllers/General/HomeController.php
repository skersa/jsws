<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Category;
use App\Models\User;
use App\Models\Bid;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Auction::with(['cover', 'category'])
            ->withCount('bids');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->status === 'ended') {
            $query->where('status', 'ended');
        } elseif ($request->status === 'active') {
            $query->where('status', 'active')->where('ends_at', '>', now());
        } else {
            $query->whereIn('status', ['active', 'ended']);
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        match ($request->sort) {
            'ending' => $query->where('status', 'active')->orderBy('ends_at'),
            'new'    => $query->orderByDesc('created_at'),
            'price'  => $query->orderByDesc('current_price'),
            default  => $query->orderByDesc('bids_count'),
        };

        $activeAuctions = $query->take(24)->get();

        $recentAuctions = collect();
        if (!$request->hasAny(['q', 'category', 'status', 'sort'])) {
            $recentAuctions = Auction::with(['cover', 'category'])
                ->withCount('bids')
                ->whereIn('status', ['active', 'ended'])
                ->orderByDesc('created_at')
                ->take(8)
                ->get();
        }

        $categories = Category::withCount([
            'auctions' => fn ($q) => $q->whereIn('status', ['active', 'ended'])
        ])->having('auctions_count', '>', 0)->orderByDesc('auctions_count')->get();

        return Inertia::render('Index', [
            'activeAuctions' => $activeAuctions->map->toCard()->values(),
            'recentAuctions' => $recentAuctions->map->toCard()->values(),
            'categories'     => $categories->map(fn ($c) => [
                'slug'           => $c->slug,
                'name'           => $c->name,
                'auctions_count' => $c->auctions_count,
            ])->values(),
            'stories'        => story_bar_data(),
            'canUploadStory' => auth()->check() && auth()->user()->isSeller(),
            'currentUserId'  => auth()->id(),
            'filters'        => [
                'q'        => $request->q ?? '',
                'category' => $request->category ?? '',
                'status'   => $request->status ?? '',
                'sort'     => $request->sort ?? 'bids',
            ],
            'now'            => now()->format('d.m.Y H:i'),
        ]);
    }
}
