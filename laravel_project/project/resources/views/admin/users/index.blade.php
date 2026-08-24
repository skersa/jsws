@extends('layouts.app')
@section('title', 'Kullanıcı Yönetimi')
@section('content')

<div class="pf-root container-fluid px-4 py-4">

    <div class="pf-toolbar mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="pf-toolbar-title mb-1">Kullanıcı Yönetimi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="pf-breadcrumb-link">Admin</a></li>
                        <li class="breadcrumb-item active pf-breadcrumb-active">Kullanıcılar</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row g-3 mt-2">
            @foreach([
                ['lbl'=>'Toplam Üye',  'num'=>$stats['total'],    'icon'=>'bi-people',        'color'=>'var(--primary)',  'bg'=>'rgba(21,94,239,.1)','col' => '12'],
                ['lbl'=>'Doğrulanmış', 'num'=>$stats['verified'], 'icon'=>'bi-shield-check',  'color'=>'#10b981',         'bg'=>'rgba(16,185,129,.1)', 'col' => '3'],
                ['lbl'=>'Beklemede',   'num'=>$stats['pending'],  'icon'=>'bi-clock',           'color'=>'#fbbf24',         'bg'=>'rgba(251,191,36,.1)', 'col' => '3'],
                ['lbl'=>'Satıcı',      'num'=>$stats['sellers'],  'icon'=>'bi-shop',            'color'=>'#06b6d4',         'bg'=>'rgba(6,182,212,.1)', 'col' => '3'],
                ['lbl'=>'Alıcı',       'num'=>$stats['buyers'],   'icon'=>'bi-person',          'color'=>'var(--primary)',  'bg'=>'rgba(21,94,239,.08)', 'col' => '3'],
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

    <div class="pf-main-card">

        <div class="pf-filter-wrapper">
            <form method="GET" action="{{ route('admin.users.index') }}" class="pf-filter-form">

                <div class="pf-search-input-wrapper">
                    <i class="bi bi-search pf-search-icon"></i>
                    <input type="text" name="q" class="pf-input pf-input-search" placeholder="İsim, e-posta..." value="{{ request('q') }}">
                </div>

                <select name="role" class="pf-input pf-select-status">
                    <option value="">Tüm Roller</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role')===$role->name?'selected':'' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>

                <select name="verified" class="pf-input pf-select-type">
                    <option value="">Tüm Durum</option>
                    <option value="yes" {{ request('verified')==='yes'?'selected':'' }}>✓ Doğrulanmış</option>
                    <option value="no"  {{ request('verified')==='no' ?'selected':'' }}>⏳ Beklemede</option>
                </select>

                <button type="submit" class="pf-btn-save pf-btn-filter">
                    <i class="bi bi-funnel me-1"></i> Filtrele
                </button>

                @if(request()->hasAny(['q','role','verified']))
                <a href="{{ route('admin.users.index') }}" class="pf-btn-reset pf-btn-clear">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="pf-table">
                <thead>
                    <tr>
                        @foreach(['Kullanıcı','Rol','İlan','Teklif','Durum','Kayıt','İşlemler'] as $th)
                        <th class="{{ $loop->last ? 'text-end text-nowrap' : 'text-start text-nowrap' }}">
                            {{ $th }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="pf-table-row">

                        <td>
                            <div class="pf-cat-info">
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="pf-cat-img" style="border-radius: 50%;">
                                @else
                                    <div class="pf-cat-img d-flex align-items-center justify-content-center fw-bold text-uppercase" style="background: rgba(21,94,239,.15); color: var(--primary); border-radius: 50%; font-size: 14px;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="pf-cat-name">{{ $user->name }}</div>
                                    <div class="pf-cat-slug" style="text-transform: none;">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @foreach($user->roles as $role)
                                @php
                                    $badgeClass = match($role->name) {
                                        'admin'  => 'pf-badge-warning',
                                        'seller' => 'pf-badge-cyan',
                                        default  => 'pf-badge-success',
                                    };
                                @endphp
                                <span class="pf-badge {{ $badgeClass }}">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </td>

                        <td class="pf-table-count">{{ $user->auctions_count }}</td>
                        <td class="pf-table-count">{{ $user->bids_count }}</td>

                        <td>
                            @if($user->is_verified)
                                <span class="pf-badge pf-badge-success">
                                    <i class="bi bi-shield-check"></i> Doğrulandı
                                </span>
                            @else
                                <span class="pf-badge pf-badge-warning">
                                    <i class="bi bi-clock"></i> Beklemede
                                </span>
                            @endif
                        </td>

                        <td class="pf-text-muted-sm">
                            {{ $user->created_at->format('d M Y') }}
                        </td>

                        <td>
                            <div class="pf-actions-wrapper">

                                <a href="{{ route('admin.users.show', $user) }}" class="pf-btn-icon pf-action-btn" title="Detay">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{ route('admin.users.edit', $user) }}" class="pf-btn-save pf-action-btn pf-action-edit" title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @if($user->is_verified)
                                <form method="POST" action="{{ route('admin.users.unverify', $user) }}" class="verify-form m-0">
                                    @csrf
                                    <button type="submit"
                                            data-name="{{ $user->name }}" data-action="unverify"
                                            title="Doğrulamayı kaldır"
                                            class="pf-action-btn-toggle status-passive">
                                        <i class="bi bi-shield-x"></i>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.users.verify', $user) }}" class="verify-form m-0">
                                    @csrf
                                    <button type="submit"
                                            data-name="{{ $user->name }}" data-action="verify"
                                            title="Doğrula"
                                            class="pf-action-btn-toggle status-active">
                                        <i class="bi bi-shield-check"></i>
                                    </button>
                                </form>
                                @endif

                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="delete-form m-0">
                                    @csrf @method('DELETE')
                                    <button type="button" class="delete-btn pf-action-btn-delete"
                                            data-name="{{ $user->name }}"
                                            title="Sil">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="pf-empty pf-empty-container text-center">
                                <div class="pf-empty-icon"><i class="bi bi-people"></i></div>
                                <div class="pf-empty-title">Kullanıcı bulunamadı</div>
                                <div class="pf-empty-sub">Arama kriterlerini değiştirmeyi dene.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="pf-pagination-wrapper">
            <span class="pf-pagination-info">
                <strong class="text-dark-custom">{{ $users->firstItem() }}–{{ $users->lastItem() }}</strong> / {{ $users->total() }} kullanıcı
            </span>
            <div class="d-flex gap-1">
                @if(!$users->onFirstPage())
                <a href="{{ $users->previousPageUrl() }}" class="pf-btn-icon pf-pagination-nav-btn">
                    <i class="bi bi-chevron-left"></i>
                </a>
                @endif

                @foreach($users->getUrlRange(max(1,$users->currentPage()-2),min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="pf-pagination-item {{ $page === $users->currentPage() ? 'active' : '' }}">
                    {{ $page }}
                </a>
                @endforeach

                @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="pf-btn-icon pf-pagination-nav-btn">
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
<script src="{{ asset('assets/js/custom/admin-users-index.js') }}"></script>
@endpush
