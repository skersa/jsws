
@extends('layouts.app')
@section('title', 'Kategori Yönetimi')
@section('content')

<div class="pf-root container-fluid px-4 py-4">

    <div class="pf-toolbar mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="pf-toolbar-title mb-1">Kategori Yönetimi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="pf-breadcrumb-link">Admin</a></li>
                        <li class="breadcrumb-item active pf-breadcrumb-active">Kategoriler</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="pf-btn-save pf-btn-with-icon">
                <i class="bi bi-plus-lg"></i> Yeni Kategori
            </a>
        </div>

        <div class="row g-3 mt-2">
            @foreach([
                ['lbl'=>'Toplam',    'num'=>$stats['total'],   'icon'=>'bi-grid-3x3-gap-fill', 'color'=>'var(--primary)', 'bg'=>'rgba(21,94,239,.1)', 'col' => '12'],
                ['lbl'=>'Aktif',     'num'=>$stats['active'],  'icon'=>'bi-check-circle',       'color'=>'#10b981',        'bg'=>'rgba(16,185,129,.1)', 'col' => '3'],
                ['lbl'=>'Pasif',     'num'=>$stats['passive'], 'icon'=>'bi-pause-circle',       'color'=>'#fbbf24',        'bg'=>'rgba(251,191,36,.1)', 'col' => '3'],
                ['lbl'=>'Ana Kat.',  'num'=>$stats['roots'],   'icon'=>'bi-folder2-open',       'color'=>'#06b6d4',        'bg'=>'rgba(6,182,212,.1)', 'col' => '3'],
                ['lbl'=>'Alt Kat.',  'num'=>$stats['subs'],    'icon'=>'bi-folder2',            'color'=>'#f87171',        'bg'=>'rgba(248,113,113,.1)', 'col' => '3'],
            ] as $s)
            <div class="col-6 col-md-4 col-xl-{{ $s['col'] }}">
                <div class="pf-stat-card">
                    <div class="pf-stat-icon-wrapper" style="background: {{ $s['bg'] }};">
                        <i class="bi {{ $s['icon'] }}" style="color: {{ $s['color'] }};"></i>
                    </div>
                    <div>
                        <div class="pf-stat-number">{{ number_format($s['num']) }}</div>
                        <div class="pf-stat-label">{{ $s['lbl'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if(session('category_success'))
    <div class="pf-alert-success mb-4">
        <i class="bi bi-check-circle-fill"></i> {{ session('category_success') }}
    </div>
    @endif

    <div class="pf-main-card">

        <div class="pf-filter-wrapper">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="pf-filter-form">

                <div class="pf-search-input-wrapper">
                    <i class="bi bi-search pf-search-icon"></i>
                    <input type="text" name="q" class="pf-input pf-input-search" placeholder="Kategori adı, slug..." value="{{ request('q') }}">
                </div>

                <select name="status" class="pf-input pf-select-status">
                    <option value="">Tüm Durum</option>
                    <option value="active"  {{ request('status')==='active' ?'selected':'' }}>✓ Aktif</option>
                    <option value="passive" {{ request('status')==='passive'?'selected':'' }}>⏸ Pasif</option>
                </select>

                <select name="type" class="pf-input pf-select-type">
                    <option value="">Tüm Türler</option>
                    <option value="root" {{ request('type')==='root'?'selected':'' }}>📁 Ana Kategori</option>
                    <option value="sub"  {{ request('type')==='sub' ?'selected':'' }}>📂 Alt Kategori</option>
                </select>

                <button type="submit" class="pf-btn-save pf-btn-filter">
                    <i class="bi bi-funnel me-1"></i> Filtrele
                </button>

                @if(request()->hasAny(['q','status','type']))
                <a href="{{ route('admin.categories.index') }}" class="pf-btn-reset pf-btn-clear">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="pf-table">
                <thead>
                    <tr>
                        @foreach(['Kategori','Üst Kategori','İlan','Alt Kat.','Sıra','Durum','İşlemler'] as $th)
                        <th class="{{ $loop->last ? 'text-end text-nowrap' : 'text-start text-nowrap' }}">
                            {{ $th }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr class="pf-table-row">

                        <td>
                            <div class="pf-cat-info">
                                <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="pf-cat-img">
                                <div>
                                    <div class="pf-cat-name">{{ $cat->name }}</div>
                                    <div class="pf-cat-slug">/{{ $cat->slug }}</div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($cat->parent)
                                <span class="pf-badge pf-badge-cyan">
                                    {{ $cat->parent->name }}
                                </span>
                            @else
                                <span class="pf-text-muted-sm">—</span>
                            @endif
                        </td>

                        <td class="pf-table-count">
                            {{ $cat->auctions_count }}
                        </td>

                        <td class="pf-table-count">
                            {{ $cat->children_count }}
                        </td>

                        <td class="pf-table-order">
                            {{ $cat->sort_order }}
                        </td>

                        <td>
                            @if($cat->is_active)
                                <span class="pf-badge pf-badge-success">
                                    <i class="bi bi-check-circle-fill"></i> Aktif
                                </span>
                            @else
                                <span class="pf-badge pf-badge-warning">
                                    <i class="bi bi-pause-circle-fill"></i> Pasif
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="pf-actions-wrapper">

                                <a href="{{ route('admin.categories.show', $cat) }}" class="pf-btn-icon pf-action-btn" title="Detay">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{ route('admin.categories.edit', $cat) }}" class="pf-btn-save pf-action-btn pf-action-edit" title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form method="POST" action="{{ route('admin.categories.toggle', $cat) }}" class="m-0">
                                    @csrf
                                    <button type="submit"
                                            title="{{ $cat->is_active ? 'Pasife al' : 'Aktif et' }}"
                                            class="pf-action-btn-toggle {{ $cat->is_active ? 'status-active' : 'status-passive' }}">
                                        <i class="bi bi-{{ $cat->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="delete-form m-0">
                                    @csrf @method('DELETE')
                                    <button type="button" class="delete-btn pf-action-btn-delete"
                                            data-name="{{ $cat->name }}"
                                            data-children="{{ $cat->children_count }}"
                                            title="Sil">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="pf-empty pf-empty-container text-center">
                                <div class="pf-empty-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                                <div class="pf-empty-title">Kategori bulunamadı</div>
                                <div class="pf-empty-sub">Filtreni değiştir veya yeni kategori oluştur.</div>
                                <a href="{{ route('admin.categories.create') }}" class="pf-btn-save mt-3 pf-btn-with-icon d-inline-flex">
                                    <i class="bi bi-plus-lg"></i> Yeni Kategori
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
        <div class="pf-pagination-wrapper">
            <span class="pf-pagination-info">
                <strong class="text-dark-custom">{{ $categories->firstItem() }}–{{ $categories->lastItem() }}</strong> / {{ $categories->total() }} kategori
            </span>
            <div class="d-flex gap-1">
                @if(!$categories->onFirstPage())
                <a href="{{ $categories->previousPageUrl() }}" class="pf-btn-icon pf-pagination-nav-btn">
                    <i class="bi bi-chevron-left"></i>
                </a>
                @endif

                @foreach($categories->getUrlRange(max(1,$categories->currentPage()-2),min($categories->lastPage(),$categories->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="pf-pagination-item {{ $page === $categories->currentPage() ? 'active' : '' }}">
                    {{ $page }}
                </a>
                @endforeach

                @if($categories->hasMorePages())
                <a href="{{ $categories->nextPageUrl() }}" class="pf-btn-icon pf-pagination-nav-btn">
                    <i class="bi bi-chevron-right"></i>
                </a>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/admin-categories-index.js') }}"></script>
@endpush
