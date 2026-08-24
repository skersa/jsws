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
            <span id="viewer-count" data-testid="viewer-count">—</span> izleyici
        </span>
    </div>
</div>

{{-- ── Main Grid ── --}}
<div class="auction-grid">

    {{-- SOL KOLON --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Mode Toggle --}}
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div class="mode-toggle">
                <button class="mode-btn active" id="tab-gallery" onclick="switchTab('gallery')">
                    <i class="bi bi-images"></i> Fotoğraflar
                </button>
                <button class="mode-btn {{ ($auction->usesPromoVideo() || ($auction->is_live && ! $auction->hasFinished())) ? '' : 'd-none' }}"
                        id="tab-stream" onclick="switchTab('stream')">
                    @if($auction->usesPromoVideo())
                        <i class="bi bi-film"></i> Tanıtım Videosu
                    @else
                        <i class="bi bi-camera-video"></i> Canlı İzle
                    @endif
                </button>
            </div>
            <div style="font-size:12px;color:var(--muted);">
                <i class="bi bi-geo-alt" style="margin-right:4px;"></i>{{ $auction->location ?? '—' }}
                &nbsp;·&nbsp;
                <i class="bi bi-tag" style="margin-right:4px;"></i>{{ $auction->category?->name ?? '—' }}
            </div>
        </div>

        {{-- GALLERY PANEL --}}
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

        {{-- STREAM PANEL --}}
        <div id="panel-stream" class="section-panel">
            @if($auction->usesPromoVideo())
            {{-- Tanıtım Videosu modu — canlı yayın yerine video --}}
            <div class="camera-section" data-testid="promo-video-section">
                @if($auction->isDirectVideoFile())
                    <video src="{{ $auction->promo_video_url }}" class="camera-video" controls style="display:block;object-fit:contain;background:#000;"></video>
                @else
                    <iframe src="{{ $auction->embedVideoUrl() }}" style="width:100%;height:100%;border:0;display:block;" allow="autoplay; encrypted-media; fullscreen" allowfullscreen></iframe>
                @endif
            </div>
            @else
            <div class="camera-section">
                <div class="cam-off-banner" id="cam-off-state">
                    <i class="bi bi-camera-video-off"></i>
                    <p>Satıcı henüz yayın başlatmadı</p>
                </div>
                <video id="liveVideo" class="camera-video" autoplay playsinline style="display:none;" muted></video>

                {{-- Satış geri sayım bandı --}}
                <div id="viewer-sell-bar" style="display:none;position:absolute;bottom:0;left:0;right:0;z-index:15;background:rgba(220,38,38,.88);backdrop-filter:blur(6px);padding:10px 18px;display:none;align-items:center;justify-content:center;gap:10px;font-size:14px;font-weight:700;color:#fff;">
                    <i class="bi bi-hourglass-split"></i>
                    <span id="viewer-sell-bar-text">3 saniye sonra satış tamamlanacak…</span>
                </div>

                {{-- Satıldı overlay --}}
                <div id="viewer-sold-overlay" style="display:none;position:absolute;inset:0;background:rgba(0,0,0,.82);z-index:20;border-radius:16px;flex-direction:column;align-items:center;justify-content:center;gap:14px;">
                    <div style="font-size:56px;">🎉</div>
                    <div style="font-size:26px;font-weight:800;color:#10b981;">Satış Tamamlandı!</div>
                    <div id="viewer-sold-sub" style="font-size:14px;color:rgba(255,255,255,.65);">—</div>
                </div>
                <div class="camera-overlay">
                    <div class="camera-top-bar">
                        <div>
                            <span class="live-pill" id="stream-live-pill" style="display:none;">
                                <span class="live-dot"></span> CANLI
                            </span>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <button class="cam-btn-icon" id="vol-btn" onclick="toggleStreamVolume()"
                                    title="Ses aç/kapat" style="display:none;">
                                <i class="bi bi-volume-mute" id="vol-icon"></i>
                            </button>
                            <button class="cam-btn-icon" onclick="toggleFullscreen()"
                                    id="fs-btn" style="display:none;">
                                <i class="bi bi-fullscreen" id="fs-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="camera-bottom-bar">
                        <span class="viewer-pill">
                            <i class="bi bi-people" style="font-size:12px;"></i>
                            <span id="viewer-count-stream">—</span> izleyici
                        </span>
                    </div>
                </div>
            </div>
            @endif

            {{-- CANLI SOHBET (satıcıya sor — Twitch tarzı, spam korumalı) --}}
            <div class="au-card mt-3" data-testid="viewer-chat-card" style="margin-top:16px;">
                <div class="au-card-head">
                    <div class="au-card-title"><i class="bi bi-chat-dots"></i> Canlı Sohbet · Satıcıya Sor</div>
                </div>
                <div id="chatMessages" data-testid="viewer-chat-messages"
                     style="height:240px;overflow-y:auto;padding:10px 16px;display:flex;flex-direction:column;">
                    <div id="chatEmpty" style="margin:auto;text-align:center;color:var(--muted);font-size:12px;">
                        <i class="bi bi-chat" style="font-size:24px;display:block;margin-bottom:6px;opacity:.3;"></i>
                        İlk mesajı sen yaz
                    </div>
                </div>
                @auth
                    @if($auction->hasFinished())
                        <div style="padding:12px 16px;border-top:1px solid var(--border);color:var(--muted);font-size:12px;">
                            <i class="bi bi-lock"></i> Yayın sona erdi, sohbet kapalı.
                        </div>
                    @else
                        <form id="chatForm" style="display:flex;gap:8px;padding:12px 16px;border-top:1px solid var(--border);">
                            <input id="chatInput" type="text" maxlength="300" autocomplete="off"
                                   data-testid="viewer-chat-input"
                                   placeholder="Satıcıya bir soru sor..."
                                   style="flex:1;padding:9px 12px;border-radius:10px;border:1px solid var(--border);background:var(--card);color:var(--text);font-size:13px;">
                            <button type="submit" data-testid="viewer-chat-send"
                                    style="padding:9px 16px;border-radius:10px;border:none;background:var(--primary);color:#fff;font-weight:700;cursor:pointer;">
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                        <div id="chatError" style="display:none;padding:0 16px 10px;color:#ef4444;font-size:12px;"></div>
                    @endif
                @else
                    <div style="padding:12px 16px;border-top:1px solid var(--border);font-size:12px;">
                        Sohbete katılmak için <a href="{{ route('login') }}" style="color:var(--primary);">giriş yap</a>.
                    </div>
                @endauth
            </div>
        </div>

        {{-- Açıklama --}}
        <div class="au-card">
            <div class="au-card-head">
                <div class="au-card-title"><i class="bi bi-file-text"></i> Açıklama</div>
            </div>
            <div class="au-desc-body">
                {{ $auction->description }}
            </div>
        </div>

        {{-- Detaylar --}}
        <div class="au-card">
            <div class="au-card-head">
                <div class="au-card-title"><i class="bi bi-info-circle"></i> Ürün Özellikleri</div>
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

    </div>

    {{-- SAĞ KOLON — Teklif Paneli --}}
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
                    <button class="cam-btn" style="background:rgba(251,191,36,.15);border-color:rgba(251,191,36,.4);color:#fbbf24;">Hemen Al</button>
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
                    <div class="stat-val" id="live-timer">—</div>
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
                        <input type="number" id="bid-input" name="amount" data-testid="bid-amount-input"
                               min="{{ $minBid }}" step="{{ $auction->min_bid_increment }}"
                               placeholder="Min: {{ number_format($minBid,0,',','.') }} ₺">
                        <div class="currency">₺</div>
                    </div>
                    <div class="bid-error" id="bid-error"></div>
                    <button class="bid-submit" id="bid-btn" onclick="submitBid()" data-testid="bid-submit-btn">
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

        {{-- Satıcı Kartı --}}
        @php $seller = $auction->user; @endphp
        <div class="au-card seller-card" data-testid="seller-card">
            <div class="au-card-head">
                <div class="au-card-title"><i class="bi bi-shop"></i> Satıcı</div>
            </div>
            <div class="seller-card-body">
                <a href="{{ route('profile.public', $seller->username) }}" class="seller-ava-link">
                    <img class="seller-ava" src="{{ $seller->profile_img }}" alt="{{ $seller->name }}">
                </a>
                <div class="seller-meta">
                    <a href="{{ route('profile.public', $seller->username) }}" class="seller-name" data-testid="seller-name">{{ $seller->name }}</a>
                    <div class="seller-handle">&#64;{{ $seller->username }}</div>
                    <div class="seller-rating" data-testid="seller-rating">
                        @include('partials.stars', ['rating' => $seller->sellerRating()])
                        <span class="seller-rating-num">{{ number_format($seller->sellerRating(), 1) }}</span>
                        <span class="seller-rating-cnt">({{ $seller->sellerReviewCount() }} değerlendirme)</span>
                    </div>
                </div>
            </div>
            <div class="seller-actions">
                @auth
                    @if(auth()->id() !== $seller->id)
                    <form action="{{ route('messages.start') }}" method="POST" class="seller-msg-form">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $seller->id }}">
                        <button type="submit" class="seller-btn-primary" data-testid="message-seller-btn">
                            <i class="bi bi-chat-dots"></i> Satıcıya Mesaj Gönder
                        </button>
                    </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="seller-btn-primary" data-testid="message-seller-btn">
                        <i class="bi bi-chat-dots"></i> Satıcıya Mesaj Gönder
                    </a>
                @endauth
                <a href="{{ route('profile.public', $seller->username) }}" class="seller-btn-ghost">
                    <i class="bi bi-person"></i> Profili Gör
                </a>
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

{{-- MOBİL STICKY BAR --}}
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
            <div class="sticky-timer" id="live-timer-mobile">—</div>
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

{{-- Lightbox --}}
<div id="lightbox" onclick="closeLightbox()"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.92);
            align-items:center;justify-content:center;cursor:zoom-out;">
    <img id="lightbox-img" style="max-width:92vw;max-height:92vh;object-fit:contain;border-radius:12px;">
</div>

</div>
@endsection

@push('scripts')
@vite(['resources/js/app.js'])
<div id="auctionNewConfigRoot"
     data-auction-id="{{ (int) $auction->id }}"
     data-min-increment="{{ (int) $auction->min_bid_increment }}"
     data-bid-url="{{ route('bids.store', $auction) }}"
     data-csrf="{{ csrf_token() }}"
     data-seller-id="{{ (int) $auction->user_id }}"
     data-remaining-secs="{{ (int) max(0, $auction->ends_at->diffInSeconds(now(), false) * -1) }}"
     data-live-state-url="{{ route('auctions.live-state', $auction) }}"
     data-chat-poll-url="{{ route('auctions.chat.poll', $auction) }}"
     data-chat-store-url="{{ route('auctions.chat.store', $auction) }}"
     data-is-finished="{{ $auction->hasFinished() ? '1' : '0' }}"
     data-uses-video="{{ $auction->usesPromoVideo() ? '1' : '0' }}"
     data-last-bid-id="{{ (int) ($auction->bids->max('id') ?? 0) }}"
     data-sold-handled="{{ in_array($auction->status, ['sold','ended']) ? '1' : '0' }}"
     data-is-auth="{{ auth()->check() ? '1' : '0' }}"
     data-current-user-id="{{ auth()->check() ? (int) auth()->id() : '' }}"
     data-current-min="{{ (int) ($auction->current_price + $auction->min_bid_increment) }}"></div>
<script src="{{ asset('assets/js/custom/auctions-new-config.js') }}"></script>
<script src="{{ asset('assets/js/custom/auction-show.js') }}"></script>
@endpush
