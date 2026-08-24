@extends('layouts.app')
@section('title', 'Keşfet')
@section('content')

<div class="dsc-hero">
    <div class="dsc-hero-inner">
        <div class="dsc-hero-icon"><i class="bi bi-compass"></i></div>
        <h1 class="dsc-hero-title">Keşfet</h1>
        <p>Binlerce müzayede ilanı arasından ilginizi çekeni bulun</p>
        <span class="dsc-hero-chip"><i class="bi bi-clock"></i> {{ now()->format('d.m.Y H:i') }} itibarıyla</span>
    </div>
</div>

<div class="container-xxl py-4 px-3 px-md-4">

    <div class="idx-section-head mb-3">
        <div class="idx-section-title">
            <i class="bi bi-tags-fill"></i> Kategoriler
        </div>
    </div>

    <div class="row g-3 mb-5">
        @forelse($categories as $cat)
        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
            <a href="{{ route('browse.auctions', ['category' => $cat->slug]) }}" class="idx-card dsc-cat-card">
                <div class="idx-card-img">
                    <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" loading="eager">
                    <div class="dsc-cat-overlay"></div>
                </div>
                <div class="dsc-cat-foot">
                    <div class="dsc-cat-name">{{ $cat->name }}</div>
                    <div class="dsc-cat-count"><i class="bi bi-collection"></i> {{ $cat->auctions_count }} ilan</div>
                </div>
            </a>
        </div>
        @empty
        <div class="idx-empty">
            <i class="bi bi-tags"></i>
            <p>Henüz kategori eklenmemiş.</p>
        </div>
        @endforelse
    </div>

    @if($featuredAuctions->count())
    <div class="idx-section-head mb-3">
        <div class="idx-section-title">
            <i class="bi bi-star-fill"></i> Öne Çıkanlar
        </div>
    </div>
    <div class="row g-3 mb-5">
        @foreach($featuredAuctions as $auction)
        <div class="col-xl-3 col-lg-4 col-md-6">
            @include('browse.card', ['auction' => $auction])
        </div>
        @endforeach
    </div>
    @endif

    <div class="idx-section-head mb-3">
        <div class="idx-section-title">
            <i class="bi bi-clock-history"></i> Yeni Eklenenler
        </div>
        <a href="{{ route('browse.auctions', ['sort' => 'new']) }}" class="idx-see-all">
            Tümünü Gör <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-3">
        @forelse($newAuctions as $auction)
        <div class="col-xl-3 col-lg-4 col-md-6">
            @include('browse.card', ['auction' => $auction])
        </div>
        @empty
        <div class="idx-empty">
            <i class="bi bi-inbox"></i>
            <p>Henüz ilan eklenmemiş.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
