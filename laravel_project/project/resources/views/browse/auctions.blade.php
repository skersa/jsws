@extends('layouts.app')
@section('title', 'Müzayedeler')
@section('content')
<div class="container py-4">
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
            @if($categories->count())
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
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="ended"  {{ request('status') == 'ended'  ? 'selected' : '' }}>Bitti</option>
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
            <span id="result-count">{{ $auctions->total() }}</span> sonuç
        </div>
    </div>

    <div class="idx-section-head">
        <div class="idx-section-title">
            <i class="bi bi-grid"></i> Müzayedeler
        </div>
        <div class="idx-section-date">
            {{ now()->format('d.m.Y H:i') }} itibarıyla
        </div>
    </div>

    <div class="row g-3" id="auction-grid">
        @forelse($auctions as $auction)
        <div class="col-xl-3 col-lg-4 col-md-6 auction-item"
             data-title="{{ strtolower($auction->title) }}"
             data-status="{{ $auction->status }}">
            @include('browse.card', ['auction' => $auction])
        </div>
        @empty
        <div class="idx-empty">
            <i class="bi bi-inbox"></i>
            <p>Aramanızla eşleşen artırma bulunamadı.</p>
        </div>
        @endforelse
    </div>

    @if($auctions->hasPages())
    <div class="mt-4 d-flex justify-content-center">
        {{ $auctions->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<div id="idxSortRoot" data-sort="{{ request('sort', 'bids') }}"></div>
<script src="{{ asset('assets/js/custom/index-sort.js') }}"></script>
<script src="{{ asset('assets/js/index.js') }}"></script>
@endpush
