@extends('layouts.app')
@section('title', $user->name . ' — Detay')
@section('content')

@php
    $roleKey   = $user->roles->first()?->name ?? 'user';
    $roleLabel = match($roleKey) { 'admin' => '👑 Admin', 'seller' => '🏪 Onaylı Satıcı', default => '🛍️ Üye' };
@endphp

<div class="pf-root">

    {{-- TOP HERO --}}
    <div class="pf-top">

        <div class="pf-cover"></div>

        <div class="pf-identity">
            <div class="pf-avatar-wrap">
                <div class="pf-avatar-outer">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="pf-avatar-img">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=155eef&color=fff&size=256"
                             alt="{{ $user->name }}" class="pf-avatar-img">
                    @endif
                </div>
            </div>

            <div class="pf-identity-right">
                <div>
                    <div class="pf-uname-row">
                        <span class="pf-uname">{{ $user->name }}</span>
                        <span class="pf-role-badge">{{ $roleLabel }}</span>
                    </div>
                    @if($user->username)
                        <div class="pf-handle">{{ "@" . $user->username }}</div>
                    @endif
                    <div class="pf-bio">{{ $user->email }}</div>
                </div>
            </div>
        </div>

        <div class="pf-stats-row">
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $user->auctions_count }}</div>
                <div class="pf-stat-label">İLAN</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $user->bids_count }}</div>
                <div class="pf-stat-label">TEKLİF</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $user->watchlist_count }}</div>
                <div class="pf-stat-label">TAKİP</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num status-indicator {{ $user->is_verified ? 'verified' : 'pending' }}">
                    {{ $user->is_verified ? '✓' : '⏳' }}
                </div>
                <div class="pf-stat-label">DURUM</div>
            </div>
        </div>

        {{-- Breadcrumb + Actions --}}
        <div class="pf-action-row breadcrumb-action-row">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="pf-link-primary">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="pf-link-primary">Kullanıcılar</a></li>
                    <li class="breadcrumb-item active pf-text-muted">{{ $user->name }}</li>
                </ol>
            </nav>
            <div class="pf-action-buttons">
                <a href="{{ route('admin.users.edit', $user) }}" class="pf-btn-save pf-btn-edit-custom">
                    <i class="bi bi-pencil"></i> Düzenle
                </a>
                <a href="{{ route('admin.users.index') }}" class="pf-btn-reset pf-btn-back-custom">
                    <i class="bi bi-arrow-left"></i> Geri
                </a>
            </div>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="pf-content-area">
        <div class="pf-tab-bar">
            <button class="pf-ptab active" onclick="switchPTab('bilgiler',this)">
                <i class="bi bi-person me-1"></i> Bilgiler
            </button>
            <button class="pf-ptab" onclick="switchPTab('ilanlar',this)">
                <i class="bi bi-grid-3x3-gap-fill me-1"></i> İlanlar
            </button>
            <button class="pf-ptab" onclick="switchPTab('teklifler',this)">
                <i class="bi bi-graph-up me-1"></i> Teklifler
            </button>
            <button class="pf-ptab" onclick="switchPTab('islemler',this)">
                <i class="bi bi-gear me-1"></i> İşlemler
            </button>
        </div>

        {{-- BİLGİLER --}}
        <div id="pc-bilgiler">
            <div class="pf-edit-drawer open info-drawer-clean">
                <div class="pf-epanel active info-panel-custom">
                    @foreach([
                        ['icon'=>'bi-hash',          'lbl'=>'Kullanıcı ID',  'val'=>'#' . $user->id],
                        ['icon'=>'bi-envelope',       'lbl'=>'E-posta',       'val'=>$user->email],
                        ['icon'=>'bi-phone',          'lbl'=>'Telefon',       'val'=>$user->phone ?? '—'],
                        ['icon'=>'bi-calendar3',      'lbl'=>'Üyelik Tarihi', 'val'=>$user->created_at->format('d M Y, H:i')],
                        ['icon'=>'bi-shield-check',   'lbl'=>'Doğrulama',     'val'=>$user->is_verified ? 'Doğrulanmış ✓' : 'Beklemede'],
                        ['icon'=>'bi-person-badge',   'lbl'=>'Kullanıcı Adı', 'val'=>$user->username ? '@'.$user->username : '—'],
                    ] as $row)
                    <div class="pf-sec-item pf-sec-item-spacing">
                        <div class="pf-sec-icon">
                            <i class="bi {{ $row['icon'] }} pf-icon-color"></i>
                        </div>
                        <div class="pf-sec-info">
                            <div class="pf-sec-title">{{ $row['lbl'] }}</div>
                            <div class="pf-sec-sub pf-sec-val-text">{{ $row['val'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- İLANLAR --}}
        <div id="pc-ilanlar" class="pf-tab-content-hidden">
            @if($user->auctions->count() > 0)
                <div class="pf-grid">
                    @foreach($user->auctions as $auction)
                        <a href="#" class="pf-auction-card">
                            <div class="pf-card-img-wrap">
                                <img src="{{ $auction->coverUrl() }}"
                                     alt="{{ $auction->title }}">
                                <div class="pf-card-price">
                                    {{ number_format($auction->current_price ?? 0, 0, ',', '.') }} ₺
                                </div>
                                <div class="pf-card-badge">
                                    @php
                                        $statusLabel = match($auction->status) {
                                            'active'    => 'Aktif',
                                            'draft'     => 'Taslak',
                                            'ended'     => 'Bitti',
                                            'cancelled' => 'İptal',
                                            default     => $auction->status
                                        };
                                    @endphp
                                    @if($auction->status === 'active')<span class="pf-pulse-dot"></span>@endif
                                    {{ $statusLabel }}
                                </div>
                            </div>
                            <div class="pf-card-body">
                                <div class="pf-card-title">{{ $auction->title }}</div>
                                <div class="pf-card-meta">
                                    <span><i class="bi bi-calendar3 me-1"></i>{{ $auction->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-box-seam"></i></div>
                    <div class="pf-empty-title">Henüz ilan yok</div>
                    <div class="pf-empty-sub">Bu kullanıcı henüz ilan yayınlamamış.</div>
                </div>
            @endif
        </div>

        {{-- TEKLİFLER --}}
        <div id="pc-teklifler" class="pf-tab-content-hidden">
            @if($user->bids->count() > 0)
            <div class="pf-table-responsive">
                <table class="pf-table-clean">
                    <thead>
                        <tr class="pf-table-border-bottom">
                            @foreach(['Müzayede','Tutar','Tarih'] as $th)
                            <th class="pf-table-th">
                                {{ $th }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->bids as $bid)
                        <tr class="pf-table-border-bottom">
                            <td class="pf-table-td-title">{{ Str::limit($bid->auction->title ?? '—', 55) }}</td>
                            <td class="pf-table-td-amount">{{ number_format($bid->amount, 2) }} ₺</td>
                            <td class="pf-table-td-date">{{ $bid->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-graph-up"></i></div>
                    <div class="pf-empty-title">Teklif bulunamadı</div>
                    <div class="pf-empty-sub">Bu kullanıcı henüz teklif vermemiş.</div>
                </div>
            @endif
        </div>

        {{-- İŞLEMLER --}}
        <div id="pc-islemler" class="pf-tab-content-hidden pf-actions-tab-padding">
            <div class="pf-toggle-list">

                @if($user->is_verified)
                <div class="pf-trow pf-trow-border">
                    <div class="pf-trow-info">
                        <div class="pf-trow-title">Doğrulama Durumu</div>
                        <div class="pf-trow-desc">Hesap şu an doğrulanmış</div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.unverify', $user) }}" class="verify-form">
                        @csrf
                        <button type="submit"
                                data-name="{{ $user->name }}" data-action="unverify"
                                class="pf-btn-status-unverify">
                            <i class="bi bi-shield-x"></i> Doğrulamayı Kaldır
                        </button>
                    </form>
                </div>
                @else
                <div class="pf-trow pf-trow-border">
                    <div class="pf-trow-info">
                        <div class="pf-trow-title">Doğrulama Durumu</div>
                        <div class="pf-trow-desc">Hesap henüz doğrulanmamış</div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.verify', $user) }}" class="verify-form">
                        @csrf
                        <button type="submit"
                                data-name="{{ $user->name }}" data-action="verify"
                                class="pf-btn-status-verify">
                            <i class="bi bi-shield-check"></i> Hesabı Doğrula
                        </button>
                    </form>
                </div>
                @endif

                <div class="pf-trow pf-trow-border">
                    <div class="pf-trow-info">
                        <div class="pf-trow-title">Kullanıcıyı Düzenle</div>
                        <div class="pf-trow-desc">Ad, e-posta, rol ve şifre güncelle</div>
                    </div>
                    <a href="{{ route('admin.users.edit', $user) }}" class="pf-btn-save pf-btn-action-edit">
                        <i class="bi bi-pencil"></i> Düzenle
                    </a>
                </div>

                @if($user->id !== auth()->id())
                <div class="pf-trow pf-trow-padding">
                    <div class="pf-trow-info">
                        <div class="pf-trow-title pf-text-danger">Hesabı Sil</div>
                        <div class="pf-trow-desc">Tüm veriler kalıcı olarak silinir</div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="delete-form">
                        @csrf @method('DELETE')
                        <button type="button" class="delete-btn pf-btn-action-delete" data-name="{{ $user->name }}">
                            <i class="bi bi-trash"></i> Kullanıcıyı Sil
                        </button>
                    </form>
                </div>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/admin-users-show.js') }}"></script>
@endpush
