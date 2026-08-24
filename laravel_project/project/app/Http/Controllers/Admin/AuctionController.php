<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Category;
use App\Notifications\AuctionStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $auctions = Auction::with(['user', 'category'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all'      => Auction::count(),
            'draft'    => Auction::where('status', 'draft')->count(),
            'active'   => Auction::where('status', 'active')->count(),
            'rejected' => Auction::where('status', 'rejected')->count(),
            'ended'    => Auction::where('status', 'ended')->count(),
        ];

        $statusMap = [
            'draft'     => ['Bekliyor', 'pf-badge-warning'],
            'active'    => ['Aktif', 'pf-badge-success'],
            'rejected'  => ['Reddedildi', 'pf-badge-danger'],
            'ended'     => ['Bitti', 'pf-badge-info'],
            'sold'      => ['Satıldı', 'pf-badge-cyan'],
            'cancelled' => ['İptal', 'pf-badge-warning'],
        ];

        return Inertia::render('Admin/Auctions/Index', [
            'counts'  => $counts,
            'filters' => [
                'search' => (string) $request->input('search', ''),
                'status' => (string) $request->input('status', ''),
            ],
            'auctions' => [
                'data' => collect($auctions->items())->map(fn ($a) => [
                    'id'             => $a->id,
                    'title'          => Str::limit($a->title, 45),
                    'cover'          => $a->coverUrl(),
                    'category'       => $a->category?->name ?? '—',
                    'location'       => $a->location,
                    'seller_name'    => $a->user->name,
                    'seller_email'   => $a->user->email,
                    'starting_price' => (float) $a->starting_price,
                    'status'         => $a->status,
                    'status_label'   => ($statusMap[$a->status] ?? ['—', 'pf-badge-muted'])[0],
                    'status_class'   => ($statusMap[$a->status] ?? ['—', 'pf-badge-muted'])[1],
                    'created'        => $a->created_at->format('d.m.Y'),
                    'raw_title'      => $a->title,
                    'show_url'       => route('admin.auctions.show', $a),
                    'edit_url'       => route('admin.auctions.edit', $a),
                    'approve_url'    => route('admin.auctions.approve', $a),
                    'reject_url'     => route('admin.auctions.reject', $a),
                    'destroy_url'    => route('admin.auctions.destroy', $a),
                ])->values(),
                'links'     => $auctions->linkCollection()->toArray(),
                'has_pages' => $auctions->hasPages(),
                'total'     => $auctions->total(),
                'from'      => $auctions->firstItem(),
                'to'        => $auctions->lastItem(),
            ],
        ]);
    }

    public function show(Auction $auction)
    {
        $auction->load(['user', 'category', 'images', 'bids.user']);

        $statusMap = [
            'draft'     => ['Bekliyor', 'warning'],
            'active'    => ['Aktif', 'success'],
            'rejected'  => ['Reddedildi', 'danger'],
            'ended'     => ['Bitti', 'danger'],
            'sold'      => ['Satıldı', 'seller'],
            'cancelled' => ['İptal', 'warning'],
        ];
        $cond = fn ($c) => match ($c ?? '') { 'new' => 'Sıfır', 'used' => 'İkinci El', 'refurbished' => 'Yenilenmiş', default => '—' };
        $tl = fn ($n) => $n ? number_format($n, 0, ',', '.') . ' ₺' : '—';

        return Inertia::render('Admin/Auctions/Show', [
            'auction' => [
                'id'            => $auction->id,
                'title'         => $auction->title,
                'title_short'   => Str::limit($auction->title, 50),
                'description'   => $auction->description,
                'status'        => $auction->status,
                'status_label'  => ($statusMap[$auction->status] ?? ['—', 'info'])[0],
                'status_type'   => ($statusMap[$auction->status] ?? ['—', 'info'])[1],
                'cover'         => $auction->cover?->url() ?? asset('assets/media/placeholder.svg'),
                'images'        => $auction->images->map(fn ($i) => ['url' => $i->url(), 'is_cover' => (bool) $i->is_cover])->values(),
                'display_price' => $auction->displayPrice(),
                'bid_count'     => $auction->bidCount(),
                'view_count'    => number_format($auction->view_count),
                'time_left'     => $auction->timeLeft(),
                'details'       => [
                    ['bi-tag', 'Kategori', $auction->category?->name ?? '—'],
                    ['bi-currency-lira', 'Başlangıç', $tl($auction->starting_price)],
                    ['bi-arrow-up', 'Min. artış', $tl($auction->min_bid_increment)],
                    ['bi-shield-lock', 'Taban fiyat', $tl($auction->reserve_price)],
                    ['bi-lightning', 'Hemen al', $tl($auction->buy_now_price)],
                    ['bi-star', 'Ürün durumu', $cond($auction->condition)],
                    ['bi-geo-alt', 'Konum', $auction->location ?? '—'],
                    ['bi-calendar', 'Başlangıç', $auction->starts_at->format('d.m.Y H:i')],
                    ['bi-calendar-x', 'Bitiş', $auction->ends_at->format('d.m.Y H:i')],
                ],
                'seller' => [
                    'name'    => $auction->user->name,
                    'email'   => $auction->user->email,
                    'avatar'  => $auction->user->avatar ? asset('storage/' . $auction->user->avatar) : null,
                    'initial' => mb_strtoupper(mb_substr($auction->user->name, 0, 1)),
                    'url'     => route('admin.users.show', $auction->user),
                ],
                'bids' => $auction->bids->take(8)->map(fn ($b) => [
                    'name'       => $b->user->name,
                    'avatar'     => $b->user->avatar ? asset('storage/' . $b->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($b->user->name) . '&size=32&background=155eef&color=fff',
                    'is_auto'    => (bool) ($b->is_auto ?? false),
                    'amount'     => number_format($b->amount, 0, ',', '.') . ' ₺',
                    'time_human' => $b->created_at->diffForHumans(),
                ])->values(),
                'index_url'   => route('admin.auctions.index'),
                'edit_url'    => route('admin.auctions.edit', $auction),
                'approve_url' => route('admin.auctions.approve', $auction),
                'reject_url'  => route('admin.auctions.reject', $auction),
                'destroy_url' => route('admin.auctions.destroy', $auction),
            ],
        ]);
    }

    public function edit(Auction $auction)
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Auctions/Edit', [
            'categories' => $categories,
            'auction' => [
                'id'                => $auction->id,
                'title'             => $auction->title,
                'description'       => $auction->description,
                'category_id'       => $auction->category_id,
                'condition'         => $auction->condition ?? 'new',
                'location'          => $auction->location,
                'status'            => $auction->status,
                'starting_price'    => $auction->starting_price,
                'min_bid_increment' => $auction->min_bid_increment,
                'reserve_price'     => $auction->reserve_price,
                'buy_now_price'     => $auction->buy_now_price,
                'starts_at'         => $auction->starts_at->format('Y-m-d\TH:i'),
                'ends_at'           => $auction->ends_at->format('Y-m-d\TH:i'),
                'update_url'        => route('admin.auctions.update', $auction),
                'show_url'          => route('admin.auctions.show', $auction),
            ],
        ]);
    }

    public function update(Request $request, Auction $auction)
    {
        $data = $request->validate([
            'title'             => 'required|string|max:120',
            'description'       => 'required|string|max:5000',
            'category_id'       => 'nullable|exists:categories,id',
            'condition'         => 'required|in:new,used,refurbished',
            'location'          => 'nullable|string|max:100',
            'starting_price'    => 'required|numeric|min:1',
            'min_bid_increment' => 'required|numeric|min:1',
            'reserve_price'     => 'nullable|numeric|min:0',
            'buy_now_price'     => 'nullable|numeric|min:0',
            'starts_at'         => 'required|date',
            'ends_at'           => 'required|date|after:starts_at',
            'status'            => 'required|in:draft,active,rejected,ended,cancelled,sold',
        ]);

        $auction->update($data);

        return redirect()
            ->route('admin.auctions.show', $auction)
            ->with('success', 'İlan güncellendi.');
    }

    public function approve(Auction $auction)
    {
        $auction->update(['status' => 'active']);

        $auction->user->notify(
            new AuctionStatusNotification($auction, 'approved')
        );

        return back()->with('success', "\"{$auction->title}\" onaylandı.");
    }

    public function reject(Request $request, Auction $auction)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $auction->update(['status' => 'rejected']);

        $auction->user->notify(
            new AuctionStatusNotification($auction, 'rejected', $request->reason)
        );

        return back()->with('success', "\"{$auction->title}\" reddedildi.");
    }

    public function destroy(Auction $auction)
    {
        $title = $auction->title;
        $auction->delete();

        $msg = "\"{$title}\" silindi.";

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['message' => $msg]);
        }

        return redirect()
            ->route('admin.auctions.index')
            ->with('success', $msg);
    }
}
