@extends('layouts.app')
@section('title', 'İlan Yönetimi')
@section('content')

<div class="pf-root container-fluid px-4 py-4">

    <div class="pf-toolbar mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="pf-toolbar-title mb-1">İlan Yönetimi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="pf-breadcrumb-link">Admin</a></li>
                        <li class="breadcrumb-item active pf-breadcrumb-active">İlanlar</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row g-3 mt-2">
            @foreach([
                ['lbl'=>'Toplam',     'num'=>$counts['all'],      'icon'=>'bi-box-seam',  'color'=>'var(--primary)', 'bg'=>'rgba(21,94,239,.1)', 'filter'=>null, 'col' => '12'],
                ['lbl'=>'Bekleyen',   'num'=>$counts['draft'],    'icon'=>'bi-hourglass', 'color'=>'#f59e0b',        'bg'=>'rgba(245,158,11,.1)', 'filter'=>'draft', 'col' => '3'],
                ['lbl'=>'Aktif',      'num'=>$counts['active'],   'icon'=>'bi-broadcast', 'color'=>'#10b981',        'bg'=>'rgba(16,185,129,.1)', 'filter'=>'active', 'col' => '3'],
                ['lbl'=>'Reddedilen', 'num'=>$counts['rejected'], 'icon'=>'bi-x-circle',  'color'=>'#ef4444',        'bg'=>'rgba(239,68,68,.1)',  'filter'=>'rejected', 'col' => '3'],
                ['lbl'=>'Biten',      'num'=>$counts['ended'],    'icon'=>'bi-flag',       'color'=>'#6b7280',       'bg'=>'rgba(107,114,128,.1)','filter'=>'ended', 'col' => '3'],
            ] as $s)
            <div class="col-6 col-md-4 col-xl-{{ $s['col'] }}">
                <a href="{{ $s['filter'] ? route('admin.auctions.index', ['status' => $s['filter']]) : route('admin.auctions.index') }}"
                   class="pf-stat-card text-decoration-none {{ request('status') === $s['filter'] ? 'pf-stat-card-active' : '' }}"
                   @if(request('status') === $s['filter']) style="border:1.5px solid {{ $s['color'] }};" @endif>
                    <div class="pf-stat-icon-wrapper" style="background: {{ $s['bg'] }};">
                        <i class="bi {{ $s['icon'] }}" style="color: {{ $s['color'] }};"></i>
                    </div>
                    <div>
                        <div class="pf-stat-number">{{ number_format($s['num']) }}</div>
                        <div class="pf-stat-label">{{ $s['lbl'] }}</div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>

    @if(session('success'))
    <div class="pf-alert-success mb-4">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    <div class="pf-main-card">

        <div class="pf-filter-wrapper">
            <form method="GET" action="{{ route('admin.auctions.index') }}" class="pf-filter-form">

                <div class="pf-search-input-wrapper">
                    <i class="bi bi-search pf-search-icon"></i>
                    <input type="text" name="search" class="pf-input pf-input-search" placeholder="İlan başlığı ara..." value="{{ request('search') }}">
                </div>

                <select name="status" class="pf-input pf-select-status">
                    <option value="">Tüm Durum</option>
                    <option value="draft"     {{ request('status')==='draft'     ?'selected':'' }}>⏳ Bekliyor</option>
                    <option value="active"    {{ request('status')==='active'    ?'selected':'' }}>✓ Aktif</option>
                    <option value="rejected"  {{ request('status')==='rejected'  ?'selected':'' }}>✕ Reddedildi</option>
                    <option value="ended"     {{ request('status')==='ended'     ?'selected':'' }}>⚑ Bitti</option>
                    <option value="sold"      {{ request('status')==='sold'      ?'selected':'' }}>✓ Satıldı</option>
                    <option value="cancelled" {{ request('status')==='cancelled' ?'selected':'' }}>⏸ İptal</option>
                </select>

                <button type="submit" class="pf-btn-save pf-btn-filter">
                    <i class="bi bi-funnel me-1"></i> Filtrele
                </button>

                @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.auctions.index') }}" class="pf-btn-reset pf-btn-clear">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="pf-table">
                <thead>
                    <tr>
                        @foreach(['İlan','Satıcı','Başlangıç Fiyatı','Durum','Tarih','İşlemler'] as $th)
                        <th class="{{ $loop->last ? 'text-end text-nowrap' : 'text-start text-nowrap' }}">
                            {{ $th }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($auctions as $auction)
                        @php
                            $statusMap = [
                                'draft'     => ['Bekliyor',   'pf-badge-warning'],
                                'active'    => ['Aktif',      'pf-badge-success'],
                                'rejected'  => ['Reddedildi', 'pf-badge-danger'],
                                'ended'     => ['Bitti',      'pf-badge-info'],
                                'sold'      => ['Satıldı',    'pf-badge-cyan'],
                                'cancelled' => ['İptal',      'pf-badge-warning'],
                            ];
                            [$slabel, $sclass] = $statusMap[$auction->status] ?? ['—', 'pf-badge-muted'];
                        @endphp
                        <tr class="pf-table-row">

                            <td>
                                <div class="pf-cat-info">
                                    <img src="{{ $auction->coverUrl() }}" alt="{{ $auction->title }}" class="pf-cat-img">
                                    <div>
                                        <div class="pf-cat-name">{{ Str::limit($auction->title, 45) }}</div>
                                        <div class="pf-cat-slug">
                                            {{ $auction->category?->name ?? '—' }}
                                            @if($auction->location)
                                                · {{ $auction->location }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="pf-cat-name" style="font-size:13px;">{{ $auction->user->name }}</div>
                                <div class="pf-cat-slug">{{ $auction->user->email }}</div>
                            </td>

                            <td class="pf-table-count text-nowrap">
                                {{ number_format($auction->starting_price, 0, ',', '.') }} ₺
                            </td>

                            <td>
                                <span class="pf-badge {{ $sclass }}">{{ $slabel }}</span>
                            </td>

                            <td class="pf-text-muted-sm text-nowrap">
                                {{ $auction->created_at->format('d.m.Y') }}
                            </td>

                            <td>
                                <div class="pf-actions-wrapper">

                                    <a href="{{ route('admin.auctions.show', $auction) }}" class="pf-btn-icon pf-action-btn" title="İncele">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.auctions.edit', $auction) }}" class="pf-btn-save pf-action-btn pf-action-edit" title="Düzenle">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if($auction->status === 'draft')
                                        <form method="POST" action="{{ route('admin.auctions.approve', $auction) }}" class="m-0">
                                            @csrf
                                            <button type="submit" class="pf-action-btn-toggle status-active" title="Onayla">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>

                                        <button type="button"
                                                class="pf-action-btn-toggle status-passive js-reject-btn"
                                                data-id="{{ $auction->id }}"
                                                data-title="{{ $auction->title }}"
                                                title="Reddet">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif

                                    <form method="POST" action="{{ route('admin.auctions.destroy', $auction) }}" class="delete-form m-0">
                                        @csrf @method('DELETE')
                                        <button type="button" class="delete-btn pf-action-btn-delete"
                                                data-name="{{ $auction->title }}"
                                                data-children="0"
                                                title="Sil">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="pf-empty pf-empty-container text-center">
                                <div class="pf-empty-icon"><i class="bi bi-inbox"></i></div>
                                <div class="pf-empty-title">İlan bulunamadı</div>
                                <div class="pf-empty-sub">Filtreni değiştir veya farklı bir arama dene.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auctions->hasPages())
        <div class="pf-pagination-wrapper">
            <span class="pf-pagination-info">
                <strong class="text-dark-custom">{{ $auctions->firstItem() }}–{{ $auctions->lastItem() }}</strong> / {{ $auctions->total() }} ilan
            </span>
            <div class="d-flex gap-1">
                @if(!$auctions->onFirstPage())
                <a href="{{ $auctions->previousPageUrl() }}" class="pf-btn-icon pf-pagination-nav-btn">
                    <i class="bi bi-chevron-left"></i>
                </a>
                @endif

                @foreach($auctions->getUrlRange(max(1,$auctions->currentPage()-2),min($auctions->lastPage(),$auctions->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="pf-pagination-item {{ $page === $auctions->currentPage() ? 'active' : '' }}">
                    {{ $page }}
                </a>
                @endforeach

                @if($auctions->hasMorePages())
                <a href="{{ $auctions->nextPageUrl() }}" class="pf-btn-icon pf-pagination-nav-btn">
                    <i class="bi bi-chevron-right"></i>
                </a>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

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

@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/admin-auctions-index.js') }}"></script>
@endpush
