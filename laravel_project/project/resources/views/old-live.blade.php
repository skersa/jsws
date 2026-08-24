@extends('layouts.app')
@section('title', $auction->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/auction-show.css') }}">
@endpush

@section('content')
<div class="container-fluid py-3" style="max-width:1400px;">

{{-- ── Toolbar ── --}}
<div class="au-toolbar">
    <div>
        <div class="au-title">{{ Str::limit($auction->title, 70) }}</div>
        <div class="au-breadcrumb">
            <a href="{{ route('index') }}">Ana Sayfa</a>
            <span class="sep">/</span>
            <a href="#">Müzayedeler</a>
            <span class="sep">/</span>
            <span>{{ Str::limit($auction->title, 30) }}</span>
        </div>
    </div>
    <div class="au-status-badges">
        @php
            $statusMap = [
                'draft'     => ['Bekliyor', 'warning'],
                'active'    => ['Aktif', 'success'],
                'rejected'  => ['Reddedildi', 'danger'],
                'ended'     => ['Bitti', 'danger'],
                'sold'      => ['Satıldı', 'seller'],
                'cancelled' => ['İptal', 'warning'],
            ];
            [$statusLabel, $statusType] = $statusMap[$auction->status] ?? ['—', 'info'];
        @endphp
        <span class="a-badge {{ $statusType }}">{{ $statusLabel }}</span>

        @if($auction->isActive())
        <span class="live-pill"><span class="live-dot"></span> Canlı</span>
        @endif

        <span class="viewer-pill">
            <i class="bi bi-eye" style="font-size:12px;"></i>
            <span id="viewer-count">—</span> izleyici
        </span>
    </div>
</div>

{{-- ── Main Grid ── --}}
<div class="auction-grid">

    {{-- ════════════════════════════════
         SOL KOLON — Medya + Detaylar
    ════════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Mode Toggle --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div class="mode-toggle">
                <button class="mode-btn active" id="tab-gallery" onclick="switchTab('gallery')">
                    <i class="bi bi-images"></i> Fotoğraflar
                </button>
                @auth
                @if($auction->user_id === auth()->id())
                <button class="mode-btn" id="tab-stream" onclick="switchTab('stream')">
                    <i class="bi bi-camera-video"></i> Canlı Yayın
                </button>
                @endif
                @endauth
                @guest
                <button class="mode-btn" id="tab-stream" onclick="switchTab('stream')">
                    <i class="bi bi-camera-video"></i> Canlı İzle
                </button>
                @endguest
                @auth
                @if($auction->user_id !== auth()->id())
                <button class="mode-btn" id="tab-stream" onclick="switchTab('stream')">
                    <i class="bi bi-camera-video"></i> Canlı İzle
                </button>
                @endif
                @endauth
            </div>
            <div style="font-size:12px;color:var(--muted);">
                <i class="bi bi-geo-alt" style="margin-right:4px;"></i>{{ $auction->location ?? '—' }}
                &nbsp;·&nbsp;
                <i class="bi bi-tag" style="margin-right:4px;"></i>{{ $auction->category?->name ?? '—' }}
            </div>
        </div>

        {{-- ── GALLERY PANEL ── --}}
        <div id="panel-gallery" class="section-panel active au-card" style="overflow:hidden;">
            <div style="padding:14px 18px;">
                <img id="mainImg"
                     src="{{ $auction->cover?->url() ?? asset('assets/media/placeholder.svg') }}"
                     class="gallery-main"
                     onclick="openLightbox(this.src)"
                     alt="{{ $auction->title }}">
            </div>
            @if($auction->images->count() > 1)
            <div class="gallery-thumbs">
                @foreach($auction->images as $i => $img)
                <img src="{{ $img->url() }}"
                     onclick="switchImg(this,'{{ $img->url() }}')"
                     class="gallery-thumb {{ $img->is_cover ? 'active' : '' }}"
                     alt="Görsel {{ $i+1 }}">
                @endforeach
            </div>
            @endif
        </div>

        {{-- ── STREAM PANEL ── --}}
        <div id="panel-stream" class="section-panel">
            <div class="camera-section">

                {{-- Camera off (default state) --}}
                <div class="cam-off-banner" id="cam-off-state">
                    <i class="bi bi-camera-video-off"></i>
                    @auth
                    @if($auction->user_id === auth()->id())
                    <p>Kamerayı açmak için aşağıdaki butona basın</p>
                    @else
                    <p>Satıcı henüz yayın başlatmadı</p>
                    @endif
                    @endauth
                    @guest
                    <p>Satıcı henüz yayın başlatmadı</p>
                    @endguest
                </div>

                {{-- Live video element --}}
                <video id="liveVideo" class="camera-video" autoplay muted playsinline style="display:none;"></video>

                {{-- Overlay --}}
                <div class="camera-overlay">

                    {{-- Top bar --}}
                    <div class="camera-top-bar">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="record-dot" id="record-indicator" style="display:none;">REC</span>
                            <span class="live-pill" id="stream-live-pill" style="display:none;">
                                <span class="live-dot"></span> CANLI
                            </span>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;" id="cam-top-controls" style="display:none;">
                            <button class="cam-btn-icon" onclick="toggleFullscreen()" title="Tam ekran">
                                <i class="bi bi-fullscreen" id="fs-icon"></i>
                            </button>
                            <button class="cam-btn-icon" onclick="toggleMute()" title="Ses aç/kapat">
                                <i class="bi bi-mic" id="mic-icon"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Bottom bar --}}
                    <div class="camera-bottom-bar">
                        @auth
                        @if($auction->user_id === auth()->id())
                        {{-- Seller controls --}}
                        <div style="display:flex;align-items:center;gap:8px;" id="seller-cam-controls">
                            <button class="cam-btn" id="start-cam-btn" onclick="startCamera()">
                                <i class="bi bi-camera-video"></i> Kamerayı Aç
                            </button>
                            <button class="cam-btn danger" id="stop-cam-btn" onclick="stopCamera()" style="display:none;">
                                <i class="bi bi-stop-circle"></i> Yayını Bitir
                            </button>
                        </div>
                        <div style="display:flex;gap:8px;" id="seller-extra-controls" style="display:none;">
                            <button class="cam-btn-icon" onclick="switchFacingMode()" title="Kamera değiştir">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button class="cam-btn-icon" id="screen-share-btn" onclick="toggleScreenShare()" title="Ekran paylaş">
                                <i class="bi bi-display"></i>
                            </button>
                        </div>
                        @else
                        {{-- Viewer controls --}}
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="viewer-pill">
                                <i class="bi bi-people" style="font-size:12px;"></i>
                                <span id="viewer-count-stream">—</span> izleyici
                            </span>
                        </div>
                        @endif
                        @endauth
                        @guest
                        <div></div>
                        @endguest
                        <div style="display:flex;gap:8px;" id="viewer-cam-btns">
                            <button class="cam-btn-icon" id="vol-btn" onclick="toggleStreamVolume()" title="Ses" style="display:none;">
                                <i class="bi bi-volume-up" id="vol-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Detaylar ── --}}
        <div class="au-card">
            <div class="au-card-head">
                <div class="au-card-title"><i class="bi bi-info-circle"></i> Ürün Detayları</div>
            </div>
            @foreach([
                ['bi-tag',        'Kategori',        $auction->category?->name ?? '—'],
                ['bi-arrow-up-circle', 'Min. Artış', number_format($auction->min_bid_increment,0,',','.').' ₺'],
                ['bi-star',       'Durum',           match($auction->condition){'new'=>'Sıfır','used'=>'İkinci El','refurbished'=>'Yenilenmiş',default=>'—'}],
                ['bi-geo-alt',    'Konum',           $auction->location ?? '—'],
                ['bi-calendar3',  'Başlangıç',       $auction->starts_at->format('d.m.Y H:i')],
                ['bi-calendar-x', 'Bitiş',           $auction->ends_at->format('d.m.Y H:i')],
                ['bi-eye',        'Görüntülenme',    number_format($auction->view_count).' kez'],
            ] as [$icon,$label,$value])
            <div class="detail-row">
                <div class="detail-icon"><i class="bi {{ $icon }}"></i></div>
                <div>
                    <div class="detail-lbl">{{ $label }}</div>
                    <div class="detail-val">{{ $value }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── Açıklama ── --}}
        <div class="au-card">
            <div class="au-card-head">
                <div class="au-card-title"><i class="bi bi-file-text"></i> Açıklama</div>
            </div>
            <div style="padding:18px;font-size:14px;line-height:1.75;color:var(--text);opacity:.9;">
                {{ $auction->description }}
            </div>
        </div>

    </div>

    {{-- ════════════════════════════════
         SAĞ KOLON — Teklif Paneli (Sticky)
    ════════════════════════════════ --}}
    <div class="bid-column">

        {{-- Price Hero --}}
        <div class="au-card" style="overflow:hidden;">
            <div class="price-hero">
                <div class="price-lbl">Güncel En Yüksek Teklif</div>
                <div class="price-value" id="live-price">{{ $auction->displayPrice() }}</div>
                <div class="price-start">Başlangıç: {{ number_format($auction->starting_price,0,',','.') }} ₺</div>

                @if($auction->buy_now_price)
                <div class="buy-now-box">
                    <div>
                        <div class="buy-now-lbl">⚡ Hemen Satın Al</div>
                        <div class="buy-now-val">{{ number_format($auction->buy_now_price,0,',','.') }} ₺</div>
                    </div>
                    <button class="cam-btn" style="background:rgba(251,191,36,.15);border-color:rgba(251,191,36,.4);color:#fbbf24;">
                        Hemen Al
                    </button>
                </div>
                @endif
            </div>

            {{-- Stats --}}
            <div class="stats-row">
                <div class="stat-cell">
                    <div class="stat-lbl">Teklif</div>
                    <div class="stat-val" id="live-bid-count">{{ $auction->bidCount() }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-lbl">Kalan</div>
                    <div class="stat-val" id="live-timer">{{ $auction->timeLeft() }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-lbl">İzleyici</div>
                    <div class="stat-val" id="live-viewer-stat">—</div>
                </div>
            </div>

            {{-- Bid Form --}}
            <div class="bid-form-area">
                @auth
                    @if($auction->isActive() && $auction->user_id !== auth()->id())
                    @php $minBid = $auction->current_price + $auction->min_bid_increment; @endphp

                    <div class="quick-grid" id="quick-btns">
                        <button class="quick-btn" onclick="setQuick({{ $minBid }})">
                            +{{ number_format($auction->min_bid_increment,0,',','.') }} ₺
                            <span>{{ number_format($minBid,0,',','.') }} ₺</span>
                        </button>
                        <button class="quick-btn" onclick="setQuick({{ $minBid + $auction->min_bid_increment * 4 }})">
                            +{{ number_format($auction->min_bid_increment*5,0,',','.') }} ₺
                            <span>{{ number_format($minBid + $auction->min_bid_increment*4,0,',','.') }} ₺</span>
                        </button>
                        <button class="quick-btn" onclick="setQuick({{ $minBid + $auction->min_bid_increment * 9 }})">
                            +{{ number_format($auction->min_bid_increment*10,0,',','.') }} ₺
                            <span>{{ number_format($minBid + $auction->min_bid_increment*9,0,',','.') }} ₺</span>
                        </button>
                    </div>

                    <div class="bid-input-wrap">
                        <input type="number" id="bid-input"
                               min="{{ $minBid }}" step="{{ $auction->min_bid_increment }}"
                               placeholder="Min: {{ number_format($minBid,0,',','.') }} ₺">
                        <div class="currency">₺</div>
                    </div>

                    <div class="bid-error" id="bid-error"></div>

                    <button class="bid-submit" id="bid-btn" onclick="submitBid()">
                        <i class="bi bi-lightning-charge-fill"></i>
                        <span id="bid-btn-text">Teklif Ver</span>
                    </button>

                    @elseif($auction->user_id === auth()->id())
                    <div class="alert alert-warning mb-0" style="font-size:13px;border-radius:10px;">
                        <i class="bi bi-info-circle me-1"></i> Kendi ilanınıza teklif veremezsiniz.
                    </div>
                    @else
                    <div class="alert alert-danger mb-0" style="font-size:13px;border-radius:10px;">
                        <i class="bi bi-clock me-1"></i> Bu müzayede sona erdi.
                    </div>
                    @endif
                @else
                <a href="{{ route('login') }}" class="bid-submit" style="text-decoration:none;">
                    <i class="bi bi-box-arrow-in-right"></i> Teklif vermek için giriş yap
                </a>
                @endauth
            </div>
        </div>

        {{-- Teklif Feed --}}
        <div class="au-card">
            <div class="au-card-head">
                <div class="au-card-title"><i class="bi bi-activity"></i> Teklif Akışı</div>
                <span class="a-badge info" id="bid-count-badge">{{ $auction->bidCount() }} teklif</span>
            </div>
            <div class="feed-scroll">
                <div id="bid-feed">
                    @forelse($auction->bids->take(15) as $index => $bid)
                    <div class="bid-item {{ $index===0?'bid-top':'' }}">
                        @if($index===0)<span class="top-label">En Yüksek</span>@endif
                        <span class="bid-rank {{ $index===0?'r1':($index===1?'r2':($index===2?'r3':'rn')) }}">{{ $index+1 }}</span>
                        <img class="bid-avatar"
                             src="https://ui-avatars.com/api/?name={{ urlencode($bid->user->name) }}&size=32&background=155eef&color=fff"
                             alt="{{ $bid->user->name }}">
                        <div style="flex:1;min-width:0;">
                            <div class="bid-name">{{ $bid->user->name }}</div>
                            <div class="bid-time">{{ $bid->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="bid-amount">{{ number_format($bid->amount,0,',','.') }} ₺</div>
                    </div>
                    @empty
                    <div class="feed-empty" id="feed-empty">
                        <i class="bi bi-inbox"></i>
                        <p>Henüz teklif yok. İlk teklifi sen ver!</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ══ MOBİL STICKY BAR ══ --}}
@auth
@if($auction->isActive() && $auction->user_id !== auth()->id())
@php $minBid = $auction->current_price + $auction->min_bid_increment; @endphp
<div class="bid-sticky-bar">
    <div class="sticky-price-row">
        <div>
            <div style="font-size:10px;color:var(--muted);margin-bottom:2px;">Güncel Teklif</div>
            <div class="sticky-price" id="live-price-mobile">{{ $auction->displayPrice() }}</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:10px;color:var(--muted);margin-bottom:2px;">Kalan Süre</div>
            <div class="sticky-timer" id="live-timer-mobile">{{ $auction->timeLeft() }}</div>
        </div>
    </div>
    <div class="sticky-input-row">
        <input type="number" id="bid-input-mobile"
               min="{{ $minBid }}" step="{{ $auction->min_bid_increment }}"
               placeholder="Min: {{ number_format($minBid,0,',','.') }} ₺">
        <button class="sticky-submit" onclick="submitBidMobile()">
            <i class="bi bi-lightning-charge-fill"></i> Teklif Ver
        </button>
    </div>
    <div class="bid-error" id="bid-error-mobile" style="margin-top:8px;margin-bottom:0;"></div>
</div>
@endif
@endauth

<div id="lightbox" onclick="closeLightbox()"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.92);
            align-items:center;justify-content:center;cursor:zoom-out;">
    <img id="lightbox-img" style="max-width:92vw;max-height:92vh;object-fit:contain;border-radius:12px;">
</div>

</div>
@endsection

@push('scripts')
@vite(['resources/js/app.js'])
<div id="oldLiveConfigRoot"
     data-auction-id="{{ (int) $auction->id }}"
     data-min-increment="{{ (int) $auction->min_bid_increment }}"
     data-bid-url="{{ route('bids.store', $auction) }}"
     data-csrf="{{ csrf_token() }}"
     data-current-min="{{ (int) ($auction->current_price + $auction->min_bid_increment) }}"></div>
<script src="{{ asset('assets/js/custom/old-live-config.js') }}"></script>
<script src="{{ asset('assets/js/custom/old-live.js') }}"></script>
@endpush




