@extends('layouts.app')
@section('title', 'Canlı Yayın — ' . $auction->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/live-broadcast.css') }}">
@endpush

@section('content')
@auth
@if(auth()->id() === $auction->user_id)

@php
    $bids      = $auction->bids->sortByDesc('amount');
    $topBid    = $bids->first();
    $bidCount  = $bids->count();
    $sellRoute = route('seller.auctions.sell', $auction);
    $endRoute  = route('seller.auctions.end-broadcast', $auction);
@endphp

<div class="pf-root container-fluid px-2 px-md-4 py-4">
<div class="lb-root">

    {{-- TOP BAR --}}
    <div class="lb-topbar">
        <div class="lb-topbar-left">
            <h1 class="pf-toolbar-title">Canlı Yayın</h1>
            <div style="font-size:13px;color:var(--muted);">
                {{ $auction->title }} — #{{ $auction->id }}
            </div>
        </div>
        <div class="lb-topbar-right">
            <button class="lb-cam-btn" id="camBtn" onclick="LB.toggleCamera()">
                <span class="lb-cam-dot" id="camDot"></span>
                <span id="camBtnLabel">Kamera Başlat</span>
            </button>
            <div class="lb-live-badge" id="liveBadge" style="display:none;">
                <div class="lb-live-dot"></div> CANLI
            </div>
            <div class="lb-viewer-pill">
                <i class="bi bi-eye"></i>
                <span id="viewerCount">0</span> izleyici
            </div>
        </div>
    </div>

    {{-- ANA GRID --}}
    <div class="lb-grid">

        {{-- SOL KOLON --}}
        <div class="lb-col-left">

            {{-- Video --}}
            <div class="lb-video-wrap" id="videoWrap">
                <video class="lb-video-stream" id="videoStream" autoplay muted playsinline></video>

                <div class="lb-video-off-state" id="camOffState">
                    <div class="lb-video-off-icon"><i class="bi bi-camera-video-off"></i></div>
                    <span class="lb-video-off-text">Kamera kapalı</span>
                </div>

                <div class="lb-overlay-live" id="liveOverlay" style="display:none;">
                    <div class="lb-live-dot"></div> CANLI
                </div>

                <div class="lb-overlay-viewers">
                    <i class="bi bi-eye"></i>
                    <span id="viewerCount2">0</span>
                </div>

                <div class="lb-toast-wrap">
                    <div class="lb-toast" id="soldToast">
                        <div class="lb-toast-title" id="toastTitle">Satış Tamamlandı! 🎉</div>
                        <div class="lb-toast-sub"   id="toastSub">—</div>
                    </div>
                </div>
            </div>

            {{-- Güncel İlan --}}
            <div class="au-card">
                <div class="au-card-head">
                    <div class="au-card-title"><i class="bi bi-box-seam"></i> Güncel İlan</div>
                    <div class="lb-timer lb-timer-safe" id="auctionTimer">--:--</div>
                </div>
                <div style="padding:20px 24px;">
                    <div style="display:flex;align-items:center;gap:16px;">
                        @if($auction->cover?->url())
                            <img src="{{ $auction->cover->url() }}" class="lb-item-img" alt="{{ $auction->title }}">
                        @else
                            <div class="lb-item-img-placeholder"><i class="bi bi-image"></i></div>
                        @endif
                        <div style="flex:1;min-width:0;">
                            <div class="lb-item-title">{{ Str::limit($auction->title, 55) }}</div>
                            <div class="lb-item-meta">
                                Başlangıç: {{ number_format($auction->starting_price, 0, ',', '.') }} ₺
                                &nbsp;·&nbsp; <span id="bidCountInline">{{ $bidCount }}</span> teklif
                            </div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div class="lb-item-price" id="topBidPrice">
                                {{ $topBid ? number_format($topBid->amount,0,',','.') . ' ₺' : number_format($auction->starting_price,0,',','.') . ' ₺' }}
                            </div>
                            <div class="lb-item-price-label">En yüksek teklif</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bilgi --}}
            <div class="lb-tip">
                <i class="bi bi-info-circle"></i>
                <span>
                    Listeden bir teklif seçin, ardından <strong>Bu Teklife Sat</strong> butonuna basın.
                    <strong>3 saniyelik</strong> geri sayım sonrası satış tamamlanır — iptal etmek için süre içinde tekrar tıklayın.
                </span>
            </div>

        </div>

        {{-- SAĞ KOLON --}}
        <div class="lb-col-right">

            {{-- Teklif Listesi --}}
            <div class="au-card">
                <div class="au-card-head">
                    <div class="au-card-title"><i class="bi bi-hammer"></i> Teklifler</div>
                    <span style="font-size:12px;color:var(--muted);" id="bidCountLabel">{{ $bidCount }} teklif</span>
                </div>

                <div id="bidList" style="overflow-y:auto;max-height:360px;">
                    @forelse($bids as $i => $bid)
                    <div class="lb-bid-row {{ $i === 0 ? 'lb-bid-selected' : '' }}"
                         data-bid-id="{{ $bid->id }}"
                         data-amount="{{ $bid->amount }}"
                         data-name="{{ $bid->user->name }}"
                         onclick="LB.selectBid(this)">
                        <div class="lb-bid-radio {{ $i === 0 ? 'lb-selected' : '' }}" id="radio-{{ $bid->id }}"></div>
                        <div class="lb-bid-avatar lb-av-{{ ['purple','green','amber','pink','blue'][$i % 5] }}">
                            {{ strtoupper(mb_substr($bid->user->name, 0, 1)) }}{{ strtoupper(mb_substr(explode(' ', $bid->user->name)[1] ?? 'X', 0, 1)) }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="lb-bid-name">{{ $bid->user->name }}</div>
                            <div class="lb-bid-time">{{ $bid->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="lb-bid-amount">{{ number_format($bid->amount, 0, ',', '.') }} ₺</div>
                    </div>
                    @empty
                    <div style="padding:40px 20px;text-align:center;color:var(--muted);" id="bidListEmpty">
                        <i class="bi bi-hammer" style="font-size:28px;display:block;margin-bottom:10px;opacity:.25;"></i>
                        <p style="font-size:13px;margin:0;">Henüz teklif yok</p>
                    </div>
                    @endforelse
                </div>

                {{-- Sat bölümü --}}
                <div class="lb-sell-section">
                    <div class="lb-sell-meta">
                        <span class="lb-sell-meta-label">Seçili teklif:</span>
                        <span class="lb-sell-meta-value" id="selectedLabel">
                            @if($topBid)
                                {{ $topBid->user->name }} — {{ number_format($topBid->amount, 0, ',', '.') }} ₺
                            @else
                                Seçilmedi
                            @endif
                        </span>
                    </div>

                    <button class="lb-sell-btn" id="sellBtn"
                            onclick="LB.startSell()"
                            @if($bids->isEmpty()) disabled @endif>
                        <i class="bi bi-check-lg" id="sellBtnIcon"></i>
                        <span id="sellBtnText">Bu Teklife Sat</span>
                    </button>

                    <div class="lb-cbar-wrap">
                        <div class="lb-cbar" id="sellCbar"></div>
                    </div>
                </div>
            </div>

            {{-- Yayın Kontrolleri --}}
            <div class="au-card">
                <div class="au-card-head">
                    <div class="au-card-title"><i class="bi bi-sliders"></i> Yayın Kontrolleri</div>
                </div>
                <div style="padding:16px 20px;">
                    <div class="lb-ctrl-grid">
                        <button class="lb-ctrl-btn" id="micBtn" onclick="LB.toggleMic()">
                            <i class="bi bi-mic" id="micIcon"></i>
                            <span id="micLabel">Mikrofon</span>
                        </button>
                        <button class="lb-ctrl-btn" id="screenBtn" onclick="LB.toggleScreen()">
                            <i class="bi bi-display" id="screenIcon"></i>
                            <span id="screenLabel">Ekran Paylaş</span>
                        </button>
                        <button class="lb-ctrl-btn" id="camFlipBtn" onclick="LB.flipCamera()" style="display:none;">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Kamerayı Çevir</span>
                        </button>
                        <button class="lb-ctrl-btn lb-ctrl-danger" onclick="LB.endBroadcast()">
                            <i class="bi bi-stop-circle"></i>
                            <span>Yayını Bitir</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- İzleyici Sohbeti (soru-cevap, spam korumalı) --}}
            <div class="au-card" data-testid="seller-chat-card">
                <div class="au-card-head">
                    <div class="au-card-title"><i class="bi bi-chat-dots"></i> İzleyici Sohbeti</div>
                </div>
                <div id="lbChatMessages" data-testid="seller-chat-messages"
                     style="height:220px;overflow-y:auto;padding:10px 16px;display:flex;flex-direction:column;">
                    <div id="lbChatEmpty" style="margin:auto;text-align:center;color:var(--muted);font-size:12px;">
                        <i class="bi bi-chat" style="font-size:24px;display:block;margin-bottom:6px;opacity:.3;"></i>
                        Henüz mesaj yok
                    </div>
                </div>
                <form id="lbChatForm" style="display:flex;gap:8px;padding:12px 16px;border-top:1px solid var(--border);">
                    <input id="lbChatInput" type="text" maxlength="300" autocomplete="off"
                           data-testid="seller-chat-input"
                           placeholder="İzleyicilere yanıt yaz..."
                           style="flex:1;padding:9px 12px;border-radius:10px;border:1px solid var(--border);background:var(--bg-soft);color:var(--text);font-size:13px;">
                    <button type="submit" data-testid="seller-chat-send"
                            style="padding:9px 16px;border-radius:10px;border:none;background:var(--primary);color:#fff;font-weight:700;cursor:pointer;">
                        <i class="bi bi-send"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>

<div id="auctionConfigRoot"
     data-auction-id="{{ (int) $auction->id }}"
     data-sell-endpoint="{{ $sellRoute }}"
     data-end-endpoint="{{ $endRoute }}"
     data-csrf-token="{{ csrf_token() }}"
     data-remaining-secs="{{ (int) max(0, $auction->ends_at->diffInSeconds(now(), false) * -1) }}"
     data-top-bid-id="{{ $topBid ? (int) $topBid->id : '' }}"
     data-top-bid-name="{{ $topBid?->user->name ?? '' }}"
     data-top-bid-amount="{{ $topBid ? (int) $topBid->amount : 0 }}"
     data-user-id="{{ (int) auth()->id() }}"
     data-is-sold="{{ in_array($auction->status, ['sold','ended']) ? '1' : '0' }}"
     data-live-state-url="{{ route('auctions.live-state', $auction) }}"
     data-live-status-url="{{ route('seller.auctions.live-status', $auction) }}"
     data-chat-poll-url="{{ route('auctions.chat.poll', $auction) }}"
     data-chat-store-url="{{ route('auctions.chat.store', $auction) }}"
     data-user-name="{{ auth()->user()->name }}"
     data-last-bid-id="{{ (int) ($bids->max('id') ?? 0) }}"
     data-seller-dashboard-url="{{ route('seller.dashboard') }}"
     data-bid-count="{{ (int) $bidCount }}"></div>

</div>
</div>

@push('scripts')
@vite(['resources/js/app.js'])
<script src="{{ asset('assets/js/custom/auctions-config.js') }}"></script>
<script src="{{ asset('assets/js/custom/live-broadcast.js') }}"></script>
@endpush

@else
<div class="container py-5 text-center">
    <h2>Erişim Engellendi</h2>
    <p class="text-muted">Bu sayfayı görüntüleme yetkiniz yok.</p>
    <a href="{{ route('index') }}" class="btn btn-primary">Ana Sayfaya Dön</a>
</div>
@endif
@else
    <meta http-equiv="refresh" content="0;url={{ route('login') }}">
@endauth

@endsection