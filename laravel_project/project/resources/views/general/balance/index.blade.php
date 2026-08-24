@extends('layouts.app')
@section('title', 'Bakiyem')

@section('content')
<div class="au-page-wrap">

    <div class="au-page-head mb-4 d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div class="au-head-left d-flex align-items-center gap-2">
            <a href="{{ route('dashboard') }}" class="au-back-link">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="au-page-title m-0">Bakiyem</h1>
        </div>
        <div class="au-head-right">
            @if($user->isSeller())
                <a href="{{ route('general.balance.withdraw.create') }}" class="btn-admin-pri w-100 justify-content-center" data-testid="withdraw-link">
                    <i class="bi bi-cash-coin me-1"></i> Para Çek
                </a>
            @else
                <a href="{{ route('general.balance.create') }}" class="btn-admin-pri w-100 justify-content-center" data-testid="topup-link">
                    <i class="bi bi-plus-circle me-1"></i> Bakiye Yükle
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-au success mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-au danger mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill text-danger"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <div class="card mb-4" style="box-shadow: none !important;">
        <div class="card-body p-4">
            <div class="row align-items-center text-center text-sm-start">
                <div class="col-sm-6 mb-3 mb-sm-0">
                    <div class="section-label m-0">Hesap Özeti</div>
                    <div class="text-muted small fw-semibold">Mevcut Kullanılabilir Bakiye</div>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <div class="display-6 fw-bold" style="color: var(--primary);">
                        {{ $user->formatted_balance }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-head d-flex justify-content-between align-items-center">
            <div class="admin-card-title m-0">
                <i class="bi bi-wallet2" style="color: var(--primary);"></i> İşlem Geçmişi
            </div>
        </div>

        <div class="table-responsive d-none d-md-block p-0">
            <table class="admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Yön</th>
                        <th>Açıklama</th>
                        <th>Tarih / Saat</th>
                        <th>Durum</th>
                        <th class="pe-4 text-end">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr onclick="window.location='{{ route('general.balance.show', $tx) }}';" style="cursor: pointer;">
                            <td class="ps-4">
                                <span class="a-badge {{ $tx->isCredit() ? 'success' : 'danger' }}">
                                    <i class="bi bi-{{ $tx->isCredit() ? 'arrow-down-left' : 'arrow-up-right' }}"></i>
                                </span>
                            </td>
                            <td class="fw-semibold" style="color: var(--text);">{{ $tx->description }}</td>
                            <td class="text-muted small">{{ $tx->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <span class="a-badge {{ $tx->status == 'completed' ? 'success' : 'warning' }}">
                                    {{ $tx->status_label }}
                                </span>
                            </td>
                            <td class="pe-4 text-end fw-bold">
                                <span class="{{ $tx->isCredit() ? 'text-success' : 'text-danger' }}">
                                    {{ $tx->formatted_amount }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inboxes display-6 d-block mb-3 opacity-25"></i>
                                Henüz kayıtlı bir hesap hareketi bulunmuyor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-md-none">
            <div class="list-group list-group-flush bg-transparent">
                @forelse($transactions as $tx)
                    <div class="list-group-item p-3"
                         onclick="window.location='{{ route('general.balance.show', $tx) }}';"
                         style="cursor: pointer; background: transparent !important; border-bottom: 1px solid var(--border) !important;">

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="a-badge {{ $tx->isCredit() ? 'success' : 'danger' }} p-2">
                                    <i class="bi bi-{{ $tx->isCredit() ? 'arrow-down-left' : 'arrow-up-right' }}"></i>
                                </span>
                                <span class="fw-semibold text-truncate" style="color: var(--text); max-width: 180px;">
                                    {{ $tx->description }}
                                </span>
                            </div>
                            <div class="fw-bold fs-6">
                                <span class="{{ $tx->isCredit() ? 'text-success' : 'text-danger' }}">
                                    {{ $tx->formatted_amount }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between small text-muted">
                            <div>
                                <i class="bi bi-clock me-1"></i>{{ $tx->created_at->format('d.m.Y H:i') }}
                            </div>
                            <div>
                                <span class="a-badge {{ $tx->status == 'completed' ? 'success' : 'warning' }} px-2 py-1">
                                    {{ $tx->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inboxes display-6 d-block mb-3 opacity-25"></i>
                        Henüz kayıtlı bir hesap hareketi bulunmuyor.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <div class="mt-4 d-flex justify-content-center justify-content-md-end">
        {{ $transactions->links() }}
    </div>

</div>
@endsection
