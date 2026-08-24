@extends('layouts.app')
@section('title', $auction->title)
@section('content')

<div class="container-fluid py-3 px-4">

    <div class="admin-toolbar mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="toolbar-title">{{ Str::limit($auction->title, 50) }}</div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.auctions.index') }}" class="pf-breadcrumb-link">İlanlar</a>
                        </li>
                        <li class="breadcrumb-item active">Detay</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2 flex-wrap">

    @if($auction->status === 'draft')
        <form method="POST" action="{{ route('admin.auctions.approve', $auction) }}">
            @csrf
            <button type="submit" class="btn-admin-pri" style="background:#22c55e;border-color:#22c55e;">
                <i class="bi bi-check-lg"></i> Onayla
            </button>
        </form>

        <button type="button"
                class="btn-admin-danger js-reject-btn"
                data-id="{{ $auction->id }}"
                data-title="{{ $auction->title }}">
            <i class="bi bi-x-lg"></i> Reddet
        </button>
    @endif

    <a href="{{ route('admin.auctions.edit', $auction) }}" class="btn-admin-pri">
        <i class="bi bi-pencil"></i> Düzenle
    </a>

    <form method="POST" action="{{ route('admin.auctions.destroy', $auction) }}"
          class="js-delete-form m-0">
        @csrf @method('DELETE')
        <button type="button"
                class="btn-admin-danger js-delete-btn"
                data-title="{{ $auction->title }}">
            <i class="bi bi-trash"></i> Sil
        </button>
    </form>

</div>
        </div>
    </div>

    @if(session('success'))
        <div class="pf-alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-3">

        <div class="col-lg-7">

            <div class="admin-card mb-3">
                <img id="mainImg"
                     src="{{ $auction->cover?->url() ?? asset('assets/media/placeholder.svg') }}"
                     class="w-100"
                     style="height:360px;object-fit:contain;background:var(--bg);border-radius:16px;">

                @if($auction->images->count() > 1)
                    <div class="d-flex gap-2 p-3" style="overflow-x:auto;">
                        @foreach($auction->images as $img)
                            <img src="{{ $img->url() }}"
                                 onclick="switchImg(this, '{{ $img->url() }}')"
                                 class="thumb-img {{ $img->is_cover ? 'thumb-active' : '' }}"
                                 style="width:64px;height:64px;flex-shrink:0;object-fit:cover;
                                        border-radius:8px;cursor:pointer;
                                        border:2px solid {{ $img->is_cover ? 'var(--primary)' : 'transparent' }};
                                        opacity:{{ $img->is_cover ? '1' : '.6' }};transition:.15s;">
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
                        [$slabel, $stype] = $statusMap[$auction->status] ?? ['—', 'info'];
                    @endphp
                    <span class="a-badge {{ $stype }}">{{ $slabel }}</span>
                </div>
                <div class="p-3">
                    <div class="row g-2">
                        @foreach([
                            ['bi-currency-dollar', 'rgba(21,94,239,.12)', 'var(--primary)',  $auction->displayPrice(),                    'Mevcut Fiyat'],
                            ['bi-people',          'rgba(16,185,129,.12)', '#10b981',         $auction->bidCount().' teklif',              'Teklif'],
                            ['bi-eye',             'rgba(251,191,36,.12)', '#fbbf24',         number_format($auction->view_count),         'Görüntülenme'],
                            ['bi-clock',           'rgba(239,68,68,.1)',   '#f87171',         $auction->timeLeft(),                        'Kalan Süre'],
                        ] as [$icon, $bg, $color, $val, $lbl])
                        <div class="col-6">
                            <div class="pf-stat-card">
                                <div class="pf-stat-icon-wrapper" style="background:{{ $bg }}">
                                    <i class="bi {{ $icon }}" style="color:{{ $color }}"></i>
                                </div>
                                <div>
                                    <div class="pf-stat-number" style="font-size:17px;">{{ $val }}</div>
                                    <div class="pf-stat-label">{{ $lbl }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Satıcı Bilgisi --}}
            <div class="admin-card mb-3">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-person"></i> Satıcı
                    </div>
                </div>
                <div class="p-3 d-flex align-items-center gap-3">
                    <div style="width:46px;height:46px;border-radius:50%;overflow:hidden;flex-shrink:0;">
                        @if($auction->user->avatar)
                            <img src="{{ asset('storage/'.$auction->user->avatar) }}"
                                 style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <div class="bg-primary text-white fw-bold d-flex align-items-center justify-content-center w-100 h-100">
                                {{ strtoupper(mb_substr($auction->user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:14px;">{{ $auction->user->name }}</div>
                        <div style="font-size:12px;opacity:.5;">{{ $auction->user->email }}</div>
                    </div>
                    <a href="{{ route('admin.users.show', $auction->user) }}"
                       class="pf-btn-icon ms-auto" title="Kullanıcıya git">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            {{-- İlan Detayları --}}
            <div class="admin-card mb-3">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-info-circle"></i> Detaylar
                    </div>
                </div>
                <div class="p-3">
                    @foreach([
                        ['bi-tag',           'Kategori',       $auction->category?->name ?? '—'],
                        ['bi-currency-lira', 'Başlangıç',      number_format($auction->starting_price,0,',','.').' ₺'],
                        ['bi-arrow-up',      'Min. artış',     number_format($auction->min_bid_increment,0,',','.').' ₺'],
                        ['bi-shield-lock',   'Taban fiyat',    $auction->reserve_price ? number_format($auction->reserve_price,0,',','.').' ₺' : '—'],
                        ['bi-lightning',     'Hemen al',       $auction->buy_now_price  ? number_format($auction->buy_now_price,0,',','.').' ₺'  : '—'],
                        ['bi-star',          'Ürün durumu',    match($auction->condition ?? '') { 'new' => 'Sıfır', 'used' => 'İkinci El', 'refurbished' => 'Yenilenmiş', default => '—' }],
                        ['bi-geo-alt',       'Konum',          $auction->location ?? '—'],
                        ['bi-calendar',      'Başlangıç',      $auction->starts_at->format('d.m.Y H:i')],
                        ['bi-calendar-x',    'Bitiş',          $auction->ends_at->format('d.m.Y H:i')],
                    ] as [$icon, $label, $value])
                    <div class="admin-info-row">
                        <div class="admin-info-icon"><i class="bi {{ $icon }}"></i></div>
                        <div class="flex-grow-1">
                            <div class="admin-info-lbl">{{ $label }}</div>
                            <div class="admin-info-val">{{ $value }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Son Teklifler --}}
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
                                    <img src="{{ $bid->user->avatar
                                        ? asset('storage/'.$bid->user->avatar)
                                        : 'https://ui-avatars.com/api/?name='.urlencode($bid->user->name).'&size=32&background=155eef&color=fff' }}"
                                         style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                                    <div>
                                        <div style="font-weight:600;font-size:12.5px;">{{ $bid->user->name }}</div>
                                        @if($bid->is_auto ?? false)
                                            <span class="a-badge info" style="font-size:10px;">Otomatik</span>
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

{{-- Reddet Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content"
             style="border-radius:16px;border:1px solid var(--search-border);background:var(--search-bg);">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">İlanı Reddet</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3" id="rejectModalDesc" style="font-size:13px;"></p>
                    <label class="pf-label">
                        Gerekçe
                        <span class="pf-hint ms-1">(isteğe bağlı, kullanıcıya iletilir)</span>
                    </label>
                    <textarea name="reason" class="pf-input mt-1" rows="3"
                              placeholder="Örn: Görsel kalitesi yetersiz, açıklama eksik..."></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="pf-btn-reset" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Reddet</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/custom/admin-auctions-show.js') }}"></script>
@endpush
@endsection
