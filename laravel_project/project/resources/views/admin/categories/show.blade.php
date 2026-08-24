@extends('layouts.app')
@section('title', $category->name . ' — Kategori Detay')
@section('content')
<div class="pf-root">
    <div class="pf-top">
        <div class="pf-cover"></div>

        <div class="pf-identity">
            <div class="pf-avatar-wrap">
                <div class="pf-avatar-outer pf-cat-avatar-outer">
                    <img src="{{ $category->image_url }}"
                         alt="{{ $category->name }}"
                         class="pf-avatar-img pf-cat-avatar-img">
                </div>
            </div>
            <div class="pf-identity-right">
                <div>
                    <div class="pf-uname-row">
                        <span class="pf-uname">{{ $category->name }}</span>
                        @if($category->is_active)
                            <span class="pf-role-badge pf-badge-active">
                                <span class="pf-pulse-dot"></span> Aktif
                            </span>
                        @else
                            <span class="pf-role-badge pf-badge-passive">⏸ Pasif</span>
                        @endif
                    </div>
                    <div class="pf-handle">/{{ $category->slug }}</div>
                    <div class="pf-bio">
                        {{ $category->description ?? 'Açıklama eklenmemiş.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="pf-stats-row">
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $category->auctions_count }}</div>
                <div class="pf-stat-label">İLAN</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $category->children_count }}</div>
                <div class="pf-stat-label">ALT KAT.</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $category->sort_order }}</div>
                <div class="pf-stat-label">SIRA</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $category->created_at->format('Y') }}</div>
                <div class="pf-stat-label">OLUŞTURMA</div>
            </div>
        </div>

        <div class="pf-action-row pf-action-row-custom">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="pf-link-primary">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}" class="pf-link-primary">Kategoriler</a></li>
                    <li class="breadcrumb-item active pf-text-muted">{{ $category->name }}</li>
                </ol>
            </nav>
            <div class="pf-btn-gap">
                <a href="{{ route('admin.categories.edit', $category) }}"
                   class="pf-btn-save pf-btn-edit-custom">
                    <i class="bi bi-pencil"></i> Düzenle
                </a>
                <a href="{{ route('admin.categories.index') }}"
                   class="pf-btn-reset pf-btn-back-custom">
                    <i class="bi bi-arrow-left"></i> Geri
                </a>
            </div>
        </div>
    </div>

    @if(session('category_success'))
    <div class="pf-alert-success">
        <i class="bi bi-check-circle-fill"></i> {{ session('category_success') }}
    </div>
    @endif

    <div class="pf-content-area">
        <div class="pf-tab-bar">
            <button class="pf-ptab active" onclick="switchPTab('bilgiler',this)">
                <i class="bi bi-info-circle me-1"></i> Bilgiler
            </button>
            <button class="pf-ptab" onclick="switchPTab('altlar',this)">
                <i class="bi bi-folder2 me-1"></i> Alt Kategoriler
                @if($category->children_count > 0)
                    <span class="pf-badge pf-badge-cyan ms-1">
                        {{ $category->children_count }}
                    </span>
                @endif
            </button>
            <button class="pf-ptab" onclick="switchPTab('islemler',this)">
                <i class="bi bi-gear me-1"></i> İşlemler
            </button>
        </div>

        <div id="pc-bilgiler">
            <div class="pf-edit-drawer open pf-drawer-clean">
                <div class="pf-epanel active pf-panel-custom">
                    @foreach([
                        ['icon'=>'bi-hash',          'lbl'=>'ID',             'val'=>'#' . $category->id],
                        ['icon'=>'bi-tag',           'lbl'=>'Adı',            'val'=>$category->name],
                        ['icon'=>'bi-link-45deg',     'lbl'=>'Slug',           'val'=>'/' . $category->slug],
                        ['icon'=>'bi-folder2-open',   'lbl'=>'Üst Kategori',   'val'=>$category->parent?->name ?? '— Ana Kategori —'],
                        ['icon'=>'bi-sort-down',      'lbl'=>'Sıralama',       'val'=>$category->sort_order],
                        ['icon'=>'bi-calendar3',      'lbl'=>'Oluşturulma',    'val'=>$category->created_at->format('d M Y, H:i')],
                        ['icon'=>'bi-pencil-square',  'lbl'=>'Güncellenme',    'val'=>$category->updated_at->format('d M Y, H:i')],
                    ] as $row)
                    <div class="pf-sec-item pf-sec-item-spacing">
                        <div class="pf-sec-icon">
                            <i class="bi {{ $row['icon'] }} pf-icon-color"></i>
                        </div>
                        <div class="pf-sec-info">
                            <div class="pf-sec-title">{{ $row['lbl'] }}</div>
                            <div class="pf-sec-sub pf-sec-val-text {{ in_array($row['lbl'], ['Slug', 'ID']) ? 'pf-font-mono' : '' }}">
                                {{ $row['val'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @if($category->description)
                    <div class="pf-sec-item pf-sec-item-spacing">
                        <div class="pf-sec-icon">
                            <i class="bi bi-text-paragraph pf-icon-color"></i>
                        </div>
                        <div class="pf-sec-info">
                            <div class="pf-sec-title">Açıklama</div>
                            <div class="pf-sec-sub pf-desc-text">{{ $category->description }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div id="pc-altlar" class="pf-tab-content-hidden">
            @if($category->children->count() > 0)
            <div class="pf-table-responsive">
                <table class="pf-table-clean">
                    <thead>
                        <tr class="pf-table-border-bottom">
                            @foreach(['Kategori','İlan','Durum','Sıra',''] as $th)
                                <th class="pf-table-th {{ $loop->last ? 'text-end' : 'text-start' }}">
                                    {{ $th }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->children as $child)
                        <tr class="pf-table-border-bottom">
                            <td class="pf-table-td">
                                <div class="pf-child-info-row">
                                    <img src="{{ $child->image_url }}" alt="{{ $child->name }}" class="pf-child-thumb">
                                    <div>
                                        <div class="pf-child-name">{{ $child->name }}</div>
                                        <div class="pf-child-slug">/{{ $child->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="pf-table-td pf-child-count">{{ $child->auctions_count }}</td>
                            <td class="pf-table-td">
                                @if($child->is_active)
                                    <span class="pf-badge-status status-active">Aktif</span>
                                @else
                                    <span class="pf-badge-status status-passive">Pasif</span>
                                @endif
                            </td>
                            <td class="pf-table-td pf-text-muted pf-font-size-13">{{ $child->sort_order }}</td>
                            <td class="pf-table-td">
                                <div class="pf-table-actions">
                                    <a href="{{ route('admin.categories.show', $child) }}" class="pf-btn-icon pf-icon-btn-custom">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.categories.edit', $child) }}" class="pf-btn-save pf-icon-btn-save-custom">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="pf-empty">
                <div class="pf-empty-icon"><i class="bi bi-folder2"></i></div>
                <div class="pf-empty-title">Alt kategori yok</div>
                <div class="pf-empty-sub">Bu kategoriye alt kategori ekleyebilirsin.</div>
                <a href="{{ route('admin.categories.create') }}?parent={{ $category->id }}"
                   class="pf-btn-save mt-3 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Alt Kategori Ekle
                </a>
            </div>
            @endif
        </div>

        <div id="pc-islemler" class="pf-actions-tab-padding pf-tab-content-hidden">
            <div class="pf-toggle-list">

                <div class="pf-trow pf-trow-border">
                    <div class="pf-trow-info">
                        <div class="pf-trow-title">Aktif / Pasif</div>
                        <div class="pf-trow-desc">Kategori şu an {{ $category->is_active ? 'aktif' : 'pasif' }}</div>
                    </div>
                    <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                        @csrf
                        <button type="submit" class="pf-btn-toggle-status {{ $category->is_active ? 'active' : 'passive' }}">
                            <i class="bi bi-{{ $category->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                            {{ $category->is_active ? 'Pasife Al' : 'Aktif Et' }}
                        </button>
                    </form>
                </div>

                <div class="pf-trow pf-trow-border">
                    <div class="pf-trow-info">
                        <div class="pf-trow-title">Kategoriyi Düzenle</div>
                        <div class="pf-trow-desc">Ad, görsel, açıklama ve ayarları güncelle</div>
                    </div>
                    <a href="{{ route('admin.categories.edit', $category) }}" class="pf-btn-save pf-btn-action-edit">
                        <i class="bi bi-pencil"></i> Düzenle
                    </a>
                </div>

                <div class="pf-trow pf-trow-padding">
                    <div class="pf-trow-info">
                        <div class="pf-trow-title pf-text-danger">Kategoriyi Sil</div>
                        <div class="pf-trow-desc">
                            {{ $category->children_count > 0 ? $category->children_count . ' alt kategori de silinir.' : 'Bu işlem geri alınamaz.' }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="delete-form">
                        @csrf @method('DELETE')
                        <button type="button" class="delete-btn pf-btn-action-delete"
                                data-name="{{ $category->name }}"
                                data-children="{{ $category->children_count }}">
                            <i class="bi bi-trash"></i> Sil
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/admin-categories-show.js') }}"></script>
@endpush
