@extends('layouts.app')
@section('title', 'Canlı Açık Artırma')
@section('content')
<div class="container py-4">

    <div class="au-page-head">
        <div class="idx-section-title">
            <i class="bi bi-broadcast"></i> Canlı Açık Artırma
        </div>
        <div class="idx-section-date">
            {{ now()->format('d.m.Y H:i') }} itibarıyla
        </div>
    </div>

    <div class="row g-3">
        @forelse($liveAuctions as $auction)
        <div class="col-xl-3 col-lg-4 col-md-6">
            @include('browse.card', ['auction' => $auction])
        </div>
        @empty
        <div class="idx-empty">
            <i class="bi bi-broadcast"></i>
            <p>Şu an canlı yayında olan bir açık artırma yok.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
