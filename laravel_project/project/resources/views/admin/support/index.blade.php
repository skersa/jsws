@extends('layouts.app')
@section('title', 'Destek Yönetimi')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin-support-index.css') }}">
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="pf-toolbar mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="pf-toolbar-title mb-1">Destek Yönetimi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="pf-breadcrumb-link">Admin</a></li>
                        <li class="breadcrumb-item active pf-breadcrumb-active">Destek</li>
                    </ol>
                </nav>
            </div>

        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach([
            ['Toplam',   $counts['all'],         'bi-headset',     'info'],
            ['Açık',     $counts['open'],        'bi-circle',      'danger'],
            ['İşlemde',  $counts['in_progress'], 'bi-arrow-repeat','warning'],
            ['Kapalı',   $counts['closed'],      'bi-check-circle','success'],
        ] as [$lbl, $val, $icon, $type])
        <div class="col-6 col-md-3">
            <div class="admin-stat">
                <div class="admin-stat-icon bg-primary-soft">
                    <i class="bi {{ $icon }} text-primary"></i>
                </div>
                <div>
                    <div class="admin-stat-num">{{ $val }}</div>
                    <div class="admin-stat-lbl">{{ $lbl }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="admin-card mb-3 p-3">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <div class="admin-input-wrap flex-grow-1" style="min-width:180px;">
                <i class="bi bi-search admin-input-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}"
                       class="admin-filter-input w-100" placeholder="Konu ara...">
            </div>
            <select name="status" class="admin-filter-select" onchange="this.form.submit()">
                <option value="">Tüm Durumlar</option>
                <option value="open"        {{ request('status')=='open'        ?'selected':'' }}>Açık</option>
                <option value="in_progress" {{ request('status')=='in_progress' ?'selected':'' }}>İşlemde</option>
                <option value="closed"      {{ request('status')=='closed'      ?'selected':'' }}>Kapalı</option>
            </select>
            <select name="priority" class="admin-filter-select" onchange="this.form.submit()">
                <option value="">Tüm Öncelikler</option>
                <option value="high"   {{ request('priority')=='high'   ?'selected':'' }}>Yüksek</option>
                <option value="medium" {{ request('priority')=='medium' ?'selected':'' }}>Orta</option>
                <option value="low"    {{ request('priority')=='low'    ?'selected':'' }}>Düşük</option>
            </select>
            <button type="submit" class="btn-admin-pri"><i class="bi bi-search"></i></button>
            @if(request()->hasAny(['q','status','priority']))
            <a href="{{ route('admin.support.index') }}" class="btn-admin-sec"><i class="bi bi-x"></i></a>
            @endif
        </form>
    </div>

    <div class="admin-card">
        <div class="admin-card-head">
            <div class="admin-card-title"><i class="bi bi-list-ul"></i> Tüm Talepler</div>
            <span class="a-badge info">{{ $tickets->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kullanıcı</th>
                        <th>Konu</th>
                        <th>Öncelik</th>
                        <th>Durum</th>
                        <th>Güncelleme</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td class="text-muted">#{{ $ticket->id }}</td>
                        <td>
                            <div class="admin-info-val">{{ $ticket->user->name }}</div>
                            <div class="pf-hint">{{ $ticket->user->email }}</div>
                        </td>
                        <td>
                            <div class="admin-info-val">{{ Str::limit($ticket->subject, 48) }}</div>
                            <div class="pf-hint">{{ $ticket->messages_count }} mesaj</div>
                        </td>
                        <td><span class="a-badge {{ $ticket->priorityBadge() }}">{{ $ticket->priorityLabel() }}</span></td>
                        <td><span class="a-badge {{ $ticket->statusBadge() }}">{{ $ticket->statusLabel() }}</span></td>
                        <td>
                            <div class="pf-hint">{{ $ticket->updated_at->diffForHumans() }}</div>
                            @if($ticket->last_reply_by === 'user')
                            <div class="support-awaiting">● Yanıt bekliyor</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.support.show', $ticket) }}" class="btn btn-outline-primary btn-sm">
                                Görüntüle
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-muted text-center py-5">Talep bulunamadı.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
        <div class="fl-pagination border-top">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('styles')

@endpush
