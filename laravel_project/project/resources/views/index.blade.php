@extends('layouts.app')
@section('title', 'Ana Sayfa')
@section('content')
<div class="container py-4">

    @include('partials.story-bar')
    <div class="idx-filterbar">
        <div class="idx-search-wrap">
            <i class="bi bi-search"></i>
            <input type="text"
                   id="search-input"
                   placeholder="Artırma ara..."
                   value="{{ request('q') }}"
                   autocomplete="off">
        </div>

        <div class="idx-selects-row">
            @if(isset($categories) && $categories->count())
            <select class="idx-select" id="cat-select" onchange="applyFilters()">
                <option value="">Tüm Kategoriler</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->slug }}"
                    {{ request('category') == $cat->slug ? 'selected' : '' }}>
                    {{ $cat->name }} ({{ $cat->auctions_count }})
                </option>
                @endforeach
            </select>
            @endif

            <select class="idx-select" id="status-select" onchange="applyFilters()">
                <option value="">Tüm Durumlar</option>
                <option value="active"  {{ request('status') == 'active'  ? 'selected' : '' }}>Aktif</option>
                <option value="ended"   {{ request('status') == 'ended'   ? 'selected' : '' }}>Bitti</option>
            </select>
        </div>

        <div class="idx-filter-divider"></div>

        <div class="idx-sort-btns">
            <button class="idx-sort-btn {{ !request('sort') || request('sort') == 'bids' ? 'active' : '' }}"
                    onclick="setSort('bids')" id="sort-bids">
                <i class="bi bi-fire"></i> Popüler
            </button>
            <button class="idx-sort-btn {{ request('sort') == 'ending' ? 'active' : '' }}"
                    onclick="setSort('ending')" id="sort-ending">
                <i class="bi bi-clock"></i> Bitmek Üzere
            </button>
            <button class="idx-sort-btn {{ request('sort') == 'new' ? 'active' : '' }}"
                    onclick="setSort('new')" id="sort-new">
                <i class="bi bi-stars"></i> Yeni
            </button>
            <button class="idx-sort-btn {{ request('sort') == 'price' ? 'active' : '' }}"
                    onclick="setSort('price')" id="sort-price">
                <i class="bi bi-sort-down"></i> Fiyat
            </button>
        </div>

        <div class="idx-filter-count">
            <span id="result-count">{{ ($activeAuctions ?? collect())->count() }}</span> sonuç
        </div>
    </div>

    <div class="idx-section-head">
        <div class="idx-section-title">
            <i class="bi bi-activity"></i>
            @if(request('status') == 'ended') Biten Artırmalar
            @elseif(request('sort') == 'ending') Bitmek Üzere
            @elseif(request('sort') == 'new') Yeni Eklenenler
            @else Aktif Artırmalar
            @endif
        </div>
        <div class="idx-section-date">
            {{ now()->format('d.m.Y H:i') }} itibarıyla
        </div>
    </div>

    <div class="row g-3" id="auction-grid">
        @forelse($activeAuctions ?? [] as $auction)
        <div class="col-xl-3 col-lg-4 col-md-6 auction-item"
             data-title="{{ strtolower($auction->title) }}"
             data-status="{{ $auction->status }}">
            <a href="{{ route('auctions.show', $auction) }}" class="idx-card">
                <div class="idx-card-img">
                    <img src="{{ $auction->cover?->url() ?? asset('assets/media/placeholder.svg') }}"
                         alt="{{ $auction->title }}"
                         loading="eager">

                    @if($auction->isActive())
                        <div class="idx-live-badge"><span class="dot"></span> CANLI</div>
                    @else
                        <div class="idx-ended-badge">BİTTİ</div>
                    @endif

                    <div class="idx-price-overlay">{{ $auction->displayPrice() }}</div>
                </div>

                <div class="idx-card-body">
                    <div class="idx-card-title">{{ $auction->title }}</div>

                    <div class="idx-card-meta">
                        @if($auction->category)
                            <span><i class="bi bi-tag"></i>{{ $auction->category->name }}</span>
                        @endif
                        <span><i class="bi bi-chat-square"></i>{{ $auction->bidCount() }} teklif</span>
                        @if($auction->location)
                            <span><i class="bi bi-geo-alt"></i>{{ Str::limit($auction->location, 18) }}</span>
                        @endif
                    </div>

                    <div class="idx-card-bottom">
                        <div>
                            <div class="idx-bid-lbl">Güncel Teklif</div>
                            <div class="idx-bid-val">{{ $auction->displayPrice() }}</div>
                        </div>
                        <div>
                            <div class="idx-timer-lbl">Kalan</div>
                            <div class="idx-timer-val" data-ends="{{ $auction->ends_at->timestamp }}">
                                {{ $auction->timeLeft() }}
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="idx-empty">
            <i class="bi bi-inbox"></i>
            <p>Şu an gösterilecek artırma yok.</p>
        </div>
        @endforelse

        <div id="no-results">
            <i class="bi bi-search"></i>
            <p>Aramanızla eşleşen artırma bulunamadı.</p>
        </div>
    </div>

    @if(!request()->hasAny(['q', 'category', 'status', 'sort']) && isset($recentAuctions) && $recentAuctions->count())
    <div class="idx-section-head mt-5">
        <div class="idx-section-title">
            <i class="bi bi-clock-history"></i> Son Eklenenler
        </div>
        <a href="{{ route('index', ['sort' => 'new']) }}" class="idx-see-all">
            Tümünü Gör <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-3 mb-4">
        @foreach($recentAuctions as $auction)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('auctions.show', $auction) }}" class="idx-card">
                <div class="idx-card-img">
                    <img src="{{ $auction->cover?->url() ?? asset('assets/media/placeholder.svg') }}"
                         alt="{{ $auction->title }}"
                         loading="eager">

                    @if($auction->isActive())
                        <div class="idx-live-badge"><span class="dot"></span> CANLI</div>
                    @else
                        <div class="idx-ended-badge">BİTTİ</div>
                    @endif

                    <div class="idx-price-overlay">{{ $auction->displayPrice() }}</div>
                </div>

                <div class="idx-card-body">
                    <div class="idx-card-title">{{ $auction->title }}</div>
                    <div class="idx-card-meta">
                        @if($auction->category)
                            <span><i class="bi bi-tag"></i>{{ $auction->category->name }}</span>
                        @endif
                        <span><i class="bi bi-chat-square"></i>{{ $auction->bidCount() }} teklif</span>
                    </div>
                    <div class="idx-card-bottom">
                        <div>
                            <div class="idx-bid-lbl">Güncel Teklif</div>
                            <div class="idx-bid-val">{{ $auction->displayPrice() }}</div>
                        </div>
                        <div>
                            <div class="idx-timer-lbl">Kalan</div>
                            <div class="idx-timer-val" data-ends="{{ $auction->ends_at->timestamp }}">
                                {{ $auction->timeLeft() }}
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection

@push('scripts')
<div id="idxSortRoot" data-sort="{{ request('sort', 'bids') }}"></div>
<script src="{{ asset('assets/js/custom/index-sort.js') }}"></script>
<script src="{{ asset('assets/js/index.js') }}"></script>
@endpush
