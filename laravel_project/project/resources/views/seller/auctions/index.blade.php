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
                        <li class="breadcrumb-item">
                            <a href="{{ route('seller.dashboard') }}" class="pf-breadcrumb-link">Admin</a>
                        </li>
                        <li class="breadcrumb-item active pf-breadcrumb-active">İlanlar</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row g-3 mt-2">
            @foreach([
                ['Toplam',     $counts['all'],      'bi-box-seam',  '#155eef', 'rgba(21,94,239,.1)', null],
                ['Bekleyen',   $counts['draft'],    'bi-hourglass', '#f59e0b', 'rgba(245,158,11,.1)', 'draft'],
                ['Aktif',      $counts['active'],   'bi-broadcast', '#10b981', 'rgba(16,185,129,.1)', 'active'],
                ['Reddedilen', $counts['rejected'], 'bi-x-circle',  '#ef4444', 'rgba(239,68,68,.1)',  'rejected'],
                ['Biten',      $counts['ended'],    'bi-flag',      '#6b7280', 'rgba(107,114,128,.1)','ended'],
            ] as [$lbl, $num, $icon, $color, $bg, $filter])
            <div class="col-6 col-md">
                <a href="{{ $filter ? route('seller.auctions.index', ['status' => $filter]) : route('admin.auctions.index') }}"
                   class="pf-stat-card text-decoration-none d-flex {{ request('status') === $filter ? 'ring-active' : '' }}"
                   style="{{ request('status') === $filter ? 'border:1.5px solid '.$color.';' : '' }}">
                    <div class="pf-stat-icon-wrapper" style="background:{{ $bg }}">
                        <i class="bi {{ $icon }}" style="color:{{ $color }}"></i>
                    </div>
                    <div>
                        <div class="pf-stat-number">{{ number_format($num) }}</div>
                        <div class="pf-stat-label">{{ $lbl }}</div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>

    </div>

    @if(session('success'))
        <div class="pf-alert-success mb-4">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="pf-main-card">

        @if($auctions->isEmpty())

            <div class="pf-empty pf-empty-container text-center py-5">
                <div class="pf-empty-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="pf-empty-title">Gösterilecek ilan yok</div>
                <div class="pf-empty-sub">Arama kriterlerini değiştirmeyi dene.</div>
            </div>

        @else

            <div class="p-3">
                <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="pf-input"
                           style="max-width:280px;"
                           placeholder="İlan başlığı ara...">
                    <button class="pf-btn-save" type="submit">
                        <i class="bi bi-search me-1"></i> Ara
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('seller.auctions.index') }}" class="pf-btn-reset">
                            <i class="bi bi-x me-1"></i> Temizle
                        </a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="pf-table">

                    <thead>
                        <tr>
                            <th>İlan</th>
                            <th>Satıcı</th>
                            <th class="text-center">Başlangıç Fiyatı</th>
                            <th class="text-center">Durum</th>
                            <th class="text-center">Tarih</th>
                            <th class="text-end">İşlemler</th>
                        </tr>
                    </thead>

                    <tbody>

                        @php
                            $statusMap = [
                                'draft'     => ['Bekliyor',   'pf-badge-warning'],
                                'active'    => ['Aktif',      'pf-badge-success'],
                                'rejected'  => ['Reddedildi', 'pf-badge-danger'],
                                'ended'     => ['Bitti',      'pf-badge-dark'],
                                'sold'      => ['Satıldı',    'pf-badge-cyan'],
                                'cancelled' => ['İptal',      'pf-badge-danger'],
                            ];
                        @endphp

                        @foreach($auctions as $auction)

                            @php
                                [$slabel, $sclass] = $statusMap[$auction->status] ?? ['—', 'pf-badge-dark'];
                            @endphp

                            <tr class="pf-table-row">

                                <td>
                                    <div class="pf-cat-info">
                                        <img src="{{ $auction->coverUrl() }}"
                                             alt="{{ $auction->title }}"
                                             class="pf-cat-img"
                                             style="border-radius:14px;object-fit:cover;">
                                        <div>
                                            <div class="pf-cat-name">
                                                {{ Str::limit($auction->title, 45) }}
                                            </div>
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
                                    <div class="pf-cat-name">{{ $auction->user->name }}</div>
                                    <div class="pf-cat-slug" style="text-transform:none;">
                                        {{ $auction->user->email }}
                                    </div>
                                </td>

                                <td class="text-center fw-semibold">
                                    {{ number_format($auction->starting_price, 0, ',', '.') }} ₺
                                </td>

                                <td class="text-center">
                                    <span class="pf-badge {{ $sclass }}">{{ $slabel }}</span>
                                </td>

                                <td class="text-center pf-text-muted-sm">
                                    {{ $auction->created_at->format('d.m.Y') }}
                                </td>

                                <td>
                                    <div class="pf-actions-wrapper justify-content-end">

                                        <a href="{{ route('seller.auctions.show', $auction) }}"
                                           class="pf-btn-icon pf-action-btn"
                                           title="İncele">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="{{ route('seller.auctions.edit', $auction) }}"
                                           class="pf-btn-save pf-action-btn pf-action-edit"
                                           title="Düzenle">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form method="POST"
                                              action="{{ route('seller.auctions.destroy', $auction) }}"
                                              class="js-delete-form m-0">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                    class="pf-action-btn-delete js-delete-btn"
                                                    data-title="{{ $auction->title }}"
                                                    title="Sil">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>
            </div>

            @if($auctions->hasPages())
            <div class="pf-pagination-wrapper">
                <span class="pf-pagination-info">
                    <strong class="text-dark-custom">
                        {{ $auctions->firstItem() }}–{{ $auctions->lastItem() }}
                    </strong>
                    / {{ $auctions->total() }} ilan
                </span>
                <div class="d-flex gap-1">
                    @if(!$auctions->onFirstPage())
                        <a href="{{ $auctions->previousPageUrl() }}"
                           class="pf-btn-icon pf-pagination-nav-btn">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    @endif
                    @foreach(
                        $auctions->getUrlRange(
                            max(1, $auctions->currentPage() - 2),
                            min($auctions->lastPage(), $auctions->currentPage() + 2)
                        ) as $page => $url
                    )
                        <a href="{{ $url }}"
                           class="pf-pagination-item {{ $page === $auctions->currentPage() ? 'active' : '' }}">
                            {{ $page }}
                        </a>
                    @endforeach
                    @if($auctions->hasMorePages())
                        <a href="{{ $auctions->nextPageUrl() }}"
                           class="pf-btn-icon pf-pagination-nav-btn">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>
            @endif

        @endif

    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/seller-auctions-index.js') }}"></script>
@endpush
