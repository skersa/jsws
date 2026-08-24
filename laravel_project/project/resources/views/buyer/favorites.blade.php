@extends('layouts.app')
@section('title', 'Favorilerim')

@section('content')
<div class="dash-wrap py-4">

    <div class="admin-toolbar dash-hero">
        <div>
            <div class="toolbar-title">Favorilerim</div>
            <div class="dash-hero-sub">Beğenip takibe aldığın ilanlar burada listelenir.</div>
        </div>
    </div>

    <div class="admin-card" data-testid="favorites-card">
        @if($items->isEmpty())
            <div class="pf-empty">
                <div class="pf-empty-icon"><i class="bi bi-heart"></i></div>
                <div class="pf-empty-title">Favori listen boş</div>
                <div class="pf-empty-sub">Beğendiğin ilanları favorilere ekle.</div>
                <a href="{{ route('browse.auctions') }}" class="btn-admin-pri dash-mt">Müzayedeleri Keşfet</a>
            </div>
        @else
            <div class="dash-fav-grid">
                @foreach($items as $w)
                <a class="dash-fav-card" href="{{ route('auctions.show', $w) }}">
                    <img src="{{ $w->cover?->url() ?? asset('assets/media/placeholder.svg') }}" alt="{{ $w->title }}">
                    <div class="dash-fav-body">
                        <div class="dash-fav-title">{{ \Illuminate\Support\Str::limit($w->title, 32) }}</div>
                        <div class="dash-fav-price">{{ $w->displayPrice() }}</div>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $items->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
