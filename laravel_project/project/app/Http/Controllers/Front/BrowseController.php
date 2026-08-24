<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrowseController extends Controller
{
    public function auctions(Request $request)
    {
        $categories = Category::active()->ordered()->withCount('auctions')->get();

        $query = Auction::query()->with(['category', 'cover']);

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $request->category));
        }

        if ($request->status === 'active') {
            $query->where('status', 'active')->where('ends_at', '>', now());
        } elseif ($request->status === 'ended') {
            $query->where(function ($q) {
                $q->where('status', '!=', 'active')->orWhere('ends_at', '<=', now());
            });
        }

        match ($request->sort) {
            'ending' => $query->where('ends_at', '>', now())->orderBy('ends_at'),
            'new'    => $query->latest(),
            'price'  => $query->orderByDesc('current_price'),
            default  => $query->withCount('bids')->orderByDesc('bids_count'),
        };

        $auctions = $query->paginate(12)->withQueryString();

        return Inertia::render('Browse/Auctions', [
            'auctions'   => [
                'data'  => collect($auctions->items())->map->toCard()->values(),
                'links' => $auctions->linkCollection()->toArray(),
                'total' => $auctions->total(),
                'has_pages' => $auctions->hasPages(),
            ],
            'categories' => $categories->map(fn ($c) => [
                'slug' => $c->slug, 'name' => $c->name, 'auctions_count' => $c->auctions_count,
            ])->values(),
            'filters' => [
                'q' => $request->q ?? '', 'category' => $request->category ?? '',
                'status' => $request->status ?? '', 'sort' => $request->sort ?? 'bids',
            ],
            'now' => now()->format('d.m.Y H:i'),
        ]);
    }

    public function live()
    {
        $liveAuctions = Auction::query()
            ->with(['category', 'cover'])
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->orderBy('ends_at')
            ->get();

        return Inertia::render('Browse/Live', [
            'liveAuctions' => $liveAuctions->map->toCard()->values(),
            'now' => now()->format('d.m.Y H:i'),
        ]);
    }

    public function explore()
    {
        $categories = Category::active()->whereNull('parent_id')->ordered()->withCount('auctions')->take(6)->get();

        $featuredAuctions = Auction::query()
            ->with(['category', 'cover'])
            ->where('is_featured', true)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest()->take(8)->get();

        $newAuctions = Auction::query()
            ->with(['category', 'cover'])
            ->latest()->take(8)->get();

        return Inertia::render('Browse/Explore', [
            'categories' => $categories->map(fn ($c) => [
                'slug' => $c->slug, 'name' => $c->name,
                'auctions_count' => $c->auctions_count, 'image_url' => $c->image_url,
                'browse_url' => route('browse.auctions', ['category' => $c->slug]),
            ])->values(),
            'featuredAuctions' => $featuredAuctions->map->toCard()->values(),
            'newAuctions'      => $newAuctions->map->toCard()->values(),
            'now' => now()->format('d.m.Y H:i'),
        ]);
    }
}
