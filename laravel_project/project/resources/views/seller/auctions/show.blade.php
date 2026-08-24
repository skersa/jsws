@extends('layouts.app')
@section('title', $auction->title)

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/seller-auction-show.css') }}">
@endpush
<div class="container-fluid py-3">

    <div class="admin-toolbar mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="toolbar-title">{{ Str::limit($auction->title, 50) }}</div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('seller.auctions.index') }}" class="pf-breadcrumb-link">İlanlarım</a>
                        </li>
                        <li class="breadcrumb-item active">Detay</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('seller.auctions.edit', $auction) }}" class="btn-admin-pri">
                    <i class="bi bi-pencil"></i> Düzenle
                </a>
                <form method="POST" action="{{ route('seller.auctions.destroy', $auction) }}"
                      data-ajax-delete
                      data-confirm-title="İlanı silmek istediğine emin misin?"
                      data-confirm-text="Bu işlem geri alınamaz."
                      data-redirect="{{ route('seller.auctions.index') }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-admin-danger">
                        <i class="bi bi-trash"></i> Kaldır
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('profile_success'))
        <div class="pf-alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('profile_success') }}</span>
        </div>
    @endif

    {{-- YAYIN YÖNETİMİ — canlı yayın başlatma / tanıtım videosu (ilana özel yer) --}}
    @php $canBroadcast = in_array($auction->status, ['active','draft']); @endphp
    <div class="admin-card mb-3" data-testid="stream-manage-card">
        <div class="admin-card-head">
            <div class="admin-card-title"><i class="bi bi-broadcast"></i> Yayın Yönetimi</div>
            @if($auction->is_live)
                <span class="a-badge" style="background:rgba(220,38,38,.15);color:#ef4444;">● CANLI</span>
            @endif
        </div>
        <div class="p-3">
            @if($errors->any())
                <div class="pf-error mb-2"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('seller.auctions.stream-settings', $auction) }}" id="streamSettingsForm">
                @csrf
                <div class="pf-label mb-2">Yayın Türü</div>
                <div class="d-flex gap-2 mb-2 flex-wrap">
                    <label class="stream-mode-opt {{ $auction->stream_mode !== 'video' ? 'active' : '' }}" id="opt-live">
                        <input type="radio" name="stream_mode" value="live" data-testid="stream-mode-live" {{ $auction->stream_mode !== 'video' ? 'checked' : '' }} onchange="_toggleStreamMode()">
                        <i class="bi bi-camera-video"></i> Canlı Yayın
                    </label>
                    <label class="stream-mode-opt {{ $auction->stream_mode === 'video' ? 'active' : '' }}" id="opt-video">
                        <input type="radio" name="stream_mode" value="video" data-testid="stream-mode-video" {{ $auction->stream_mode === 'video' ? 'checked' : '' }} onchange="_toggleStreamMode()">
                        <i class="bi bi-film"></i> Tanıtım Videosu
                    </label>
                </div>

                <div id="videoUrlField" style="{{ $auction->stream_mode === 'video' ? '' : 'display:none;' }}">
                    <label class="pf-label">Tanıtım Videosu Linki (YouTube, Vimeo veya .mp4)</label>
                    <input type="url" name="promo_video_url" class="pf-input" data-testid="promo-video-url-input"
                           value="{{ old('promo_video_url', $auction->promo_video_url) }}"
                           placeholder="https://www.youtube.com/watch?v=...">
                    <div class="pf-hint mt-1">İzleyiciler canlı yayın yerine bu videoyu izler.</div>
                </div>

                <button type="submit" class="pf-btn-save mt-3" data-testid="save-stream-settings-btn">
                    <i class="bi bi-floppy me-1"></i> Yayın Ayarını Kaydet
                </button>
            </form>

            <hr style="border-color:var(--border);margin:16px 0;">

            @if($canBroadcast)
                <a href="{{ route('seller.auctions.broadcast', $auction) }}"
                   class="btn-admin-pri w-100 justify-content-center" data-testid="start-broadcast-btn"
                   id="goLiveBtn"
                   style="background:#10b981;border-color:#10b981;{{ $auction->stream_mode === 'video' ? 'opacity:.5;pointer-events:none;' : '' }}">
                    <i class="bi bi-broadcast"></i> Canlı Yayına Başla
                </a>
                <div class="pf-hint mt-2" id="liveDisabledHint" style="{{ $auction->stream_mode === 'video' ? '' : 'display:none;' }}">
                    Canlı yayına başlamak için yukarıdan "Canlı Yayın" türünü seç ve kaydet.
                </div>
            @else
                <div class="pf-hint">Bu ilan yayına uygun değil (durum: {{ $auction->status }}).</div>
            @endif

            @if($auction->usesPromoVideo())
                <div class="mt-3">
                    <div class="pf-label mb-1">Tanıtım Videosu Önizleme</div>
                    <div style="border-radius:10px;overflow:hidden;aspect-ratio:16/9;background:#000;" data-testid="promo-video-preview">
                        @if($auction->isDirectVideoFile())
                            <video src="{{ $auction->promo_video_url }}" controls style="width:100%;height:100%;object-fit:contain;"></video>
                        @else
                            <iframe src="{{ $auction->embedVideoUrl() }}" style="width:100%;height:100%;border:0;" allowfullscreen></iframe>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-3">

        <div class="col-lg-7">

            <div class="admin-card mb-3">
                <img id="mainImg"
                     src="{{ $auction->cover?->url() ?? asset('assets/media/placeholder.svg') }}"
                     class="w-100"
                     style="height:380px;object-fit:contain;background:var(--bg);border-radius:16px;">

                @if($auction->images->count() > 1)
                    <div class="d-flex gap-2 p-3" style="overflow-x:auto;">
                        @foreach($auction->images as $img)
                            <img src="{{ $img->url() }}"
                                 onclick="switchImg(this, '{{ $img->url() }}')"
                                 class="thumb-img {{ $img->is_cover ? 'thumb-active' : '' }}"
                                 style="width:64px;height:64px;flex-shrink:0;object-fit:cover;
                                        border-radius:8px;cursor:pointer;border:2px solid transparent;
                                        transition:.15s;opacity:{{ $img->is_cover ? '1' : '.6' }};">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="admin-card">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-file-text"></i> Açıklama
                    </div>
                </div>
                <div class="p-3">
                    <p class="pf-desc-text mb-0">{{ $auction->description }}</p>
                </div>
            </div>

        </div>

        <div class="col-lg-5">

            <div class="admin-card mb-3">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-bar-chart"></i> Özet
                    </div>
                    @php
                        $statusMap = [
                           'draft'     => ['Bekliyor',   'warning'],
                            'active'    => ['Aktif',      'success'],
                            'rejected'  => ['Reddedildi', 'danger'],
                            'ended'     => ['Bitti',      'danger'],
                            'sold'      => ['Satıldı',    'seller'],
                            'cancelled' => ['İptal',      'warning'],
                        ];
                        [$statusLabel, $statusType] = $statusMap[$auction->status] ?? ['—', 'info'];
                    @endphp
                    <span class="a-badge {{ $statusType }}">{{ $statusLabel }}</span>
                </div>
                <div class="p-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="pf-stat-card">
                                <div class="pf-stat-icon-wrapper" style="background:rgba(21,94,239,.12);">
                                    <i class="bi bi-currency-dollar" style="color:var(--primary);"></i>
                                </div>
                                <div>
                                    <div class="pf-stat-number" style="font-size:18px;">{{ $auction->displayPrice() }}</div>
                                    <div class="pf-stat-label">Mevcut Fiyat</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pf-stat-card">
                                <div class="pf-stat-icon-wrapper" style="background:rgba(16,185,129,.12);">
                                    <i class="bi bi-people" style="color:#10b981;"></i>
                                </div>
                                <div>
                                    <div class="pf-stat-number" style="font-size:18px;">{{ $auction->bidCount() }}</div>
                                    <div class="pf-stat-label">Teklif</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pf-stat-card">
                                <div class="pf-stat-icon-wrapper" style="background:rgba(251,191,36,.12);">
                                    <i class="bi bi-eye" style="color:#fbbf24;"></i>
                                </div>
                                <div>
                                    <div class="pf-stat-number" style="font-size:18px;">{{ number_format($auction->view_count) }}</div>
                                    <div class="pf-stat-label">Görüntülenme</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pf-stat-card">
                                <div class="pf-stat-icon-wrapper" style="background:rgba(239,68,68,.1);">
                                    <i class="bi bi-clock" style="color:#f87171;"></i>
                                </div>
                                <div>
                                    <div class="pf-stat-number" style="font-size:18px;">{{ $auction->timeLeft() }}</div>
                                    <div class="pf-stat-label">Kalan Süre</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card mb-3">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-info-circle"></i> Detaylar
                    </div>
                </div>
                <div class="p-3">
                    @foreach([
                        ['bi-tag',          'Kategori',         $auction->category?->name ?? '—'],
                        ['bi-currency-lira','Başlangıç',        number_format($auction->starting_price,0,',','.').' ₺'],
                        ['bi-arrow-up',     'Min. artış',       number_format($auction->min_bid_increment,0,',','.').' ₺'],
                        ['bi-shield-lock',  'Taban fiyat',      $auction->reserve_price ? number_format($auction->reserve_price,0,',','.').' ₺' : '—'],
                        ['bi-lightning',    'Hemen al',         $auction->buy_now_price  ? number_format($auction->buy_now_price,0,',','.').' ₺'  : '—'],
                        ['bi-star',         'Ürün durumu',      match($auction->condition) { 'new' => 'Sıfır', 'used' => 'İkinci El', 'refurbished' => 'Yenilenmiş', default => '—' }],
                        ['bi-geo-alt',      'Konum',            $auction->location ?? '—'],
                        ['bi-calendar',     'Başlangıç',        $auction->starts_at->format('d.m.Y H:i')],
                        ['bi-calendar-x',   'Bitiş',            $auction->ends_at->format('d.m.Y H:i')],
                    ] as [$icon, $label, $value])
                    <div class="admin-info-row">
                        <div class="admin-info-icon">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="admin-info-lbl">{{ $label }}</div>
                            <div class="admin-info-val">{{ $value }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($auction->bids->isNotEmpty())
            <div class="admin-card">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-list-ol"></i> Son Teklifler
                    </div>
                    <span class="a-badge info">{{ $auction->bidCount() }} teklif</span>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kullanıcı</th>
                            <th>Tutar</th>
                            <th>Zaman</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auction->bids->take(8) as $bid)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $bid->user->profile_img ?? 'https://ui-avatars.com/api/?name='.urlencode($bid->user->name).'&size=32&background=155eef&color=fff' }}"
                                         class="a-avatar" style="border-radius:50%;">
                                    <div>
                                        <div style="font-weight:600;font-size:var(--fs-sm);">{{ $bid->user->name }}</div>
                                        @if($bid->is_auto)
                                            <span class="a-badge info">Otomatik</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="pf-table-td-amount">
                                {{ number_format($bid->amount, 0, ',', '.') }} ₺
                            </td>
                            <td class="pf-text-muted-sm">
                                {{ $bid->created_at->diffForHumans() }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/custom/seller-auction-show.js') }}"></script>
@endpush

@endsection
