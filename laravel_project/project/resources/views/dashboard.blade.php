@extends('layouts.app')
@section('title', 'Panelim')

@section('content')
@php
    $user = auth()->user();
    $myBids = $user->bids()->with(['auction.cover', 'auction.category'])->latest()->take(6)->get();
    $activeBidsCount = $user->bids()
        ->whereHas('auction', fn($q) => $q->where('status', 'active'))
        ->distinct('auction_id')->count('auction_id');
    $favCount = $user->watchlist()->count();
    $wonCount = $user->purchases()->count();
    $balance = $user->balance ?? 0;
    $watchItems = $user->watchlist()->with('cover')->latest()->take(4)->get();
@endphp

<div class="dash-wrap py-4">

    <div class="admin-toolbar dash-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="toolbar-title">Merhaba, {{ $user->name }} 👋</div>
                <div class="dash-hero-sub">Tekliflerini, favorilerini ve bakiyeni buradan takip et.</div>
            </div>
            @unless($user->isAdmin())
            <a href="{{ route('general.balance.index') }}" class="btn-admin-pri">
                <i class="bi bi-wallet2"></i> Bakiye Yükle
            </a>
            @endunless
        </div>
    </div>

    <div class="dash-stats">
        @unless($user->isAdmin())
        <div class="pf-stat-card">
            <div class="pf-stat-icon-wrapper dash-ic-blue"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="pf-stat-number">{{ number_format((float) $balance, 0, ',', '.') }} ₺</div>
                <div class="pf-stat-label">Bakiye</div>
            </div>
        </div>
        @endunless
        <div class="pf-stat-card">
            <div class="pf-stat-icon-wrapper dash-ic-green"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <div class="pf-stat-number">{{ $activeBidsCount }}</div>
                <div class="pf-stat-label">Aktif Teklif</div>
            </div>
        </div>
        <div class="pf-stat-card">
            <div class="pf-stat-icon-wrapper dash-ic-pink"><i class="bi bi-heart-fill"></i></div>
            <div>
                <div class="pf-stat-number">{{ $favCount }}</div>
                <div class="pf-stat-label">Favori</div>
            </div>
        </div>
        <div class="pf-stat-card">
            <div class="pf-stat-icon-wrapper dash-ic-gold"><i class="bi bi-trophy-fill"></i></div>
            <div>
                <div class="pf-stat-number">{{ $wonCount }}</div>
                <div class="pf-stat-label">Kazanılan</div>
            </div>
        </div>
    </div>

    <div class="dash-grid">

        <div class="admin-card">
            <div class="admin-card-head">
                <div class="admin-card-title"><i class="bi bi-clock-history"></i> Son Tekliflerim</div>
                <a href="/my-bids" class="btn-admin-sec">Tümü</a>
            </div>

            @if($myBids->isEmpty())
                <div class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-hammer"></i></div>
                    <div class="pf-empty-title">Henüz teklif vermedin</div>
                    <div class="pf-empty-sub">Müzayedelere göz at ve ilk teklifini ver.</div>
                    <a href="{{ route('browse.auctions') }}" class="btn-admin-pri dash-mt">Müzayedeleri Keşfet</a>
                </div>
            @else
                <div class="pf-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>İlan</th>
                                <th>Teklifim</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myBids as $bid)
                            <tr>
                                <td>
                                    <div class="dash-item">
                                        <img class="a-avatar" src="{{ $bid->auction?->cover?->url() ?? asset('assets/media/placeholder.svg') }}" alt="">
                                        <a class="dash-item-title" href="{{ $bid->auction ? route('auctions.show', $bid->auction) : '#' }}">
                                            {{ \Illuminate\Support\Str::limit($bid->auction?->title ?? 'İlan silinmiş', 34) }}
                                        </a>
                                    </div>
                                </td>
                                <td class="dash-amount">{{ number_format($bid->amount, 0, ',', '.') }} ₺</td>
                                <td>
                                    @if($bid->auction?->status === 'active')
                                        <span class="a-badge success">Aktif</span>
                                    @elseif($bid->auction?->status === 'sold')
                                        <span class="a-badge info">Satıldı</span>
                                    @else
                                        <span class="a-badge warning">Bitti</span>
                                    @endif
                                </td>
                                <td class="dash-muted">{{ $bid->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="admin-card">
            <div class="admin-card-head">
                <div class="admin-card-title"><i class="bi bi-heart"></i> Favorilerim</div>
                <a href="/favorites" class="btn-admin-sec">Tümü</a>
            </div>

            @if($watchItems->isEmpty())
                <div class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-heart"></i></div>
                    <div class="pf-empty-title">Favori listen boş</div>
                    <div class="pf-empty-sub">Beğendiğin ilanları favorilere ekle.</div>
                </div>
            @else
                <div class="dash-fav-grid">
                    @foreach($watchItems as $w)
                    <a class="dash-fav-card" href="{{ route('auctions.show', $w) }}">
                        <img src="{{ $w->cover?->url() ?? asset('assets/media/placeholder.svg') }}" alt="{{ $w->title }}">
                        <div class="dash-fav-body">
                            <div class="dash-fav-title">{{ \Illuminate\Support\Str::limit($w->title, 28) }}</div>
                            <div class="dash-fav-price">{{ $w->displayPrice() }}</div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
