@extends('layouts.app')
@section('title', 'Para Çek')

@section('content')
<div class="au-page-wrap">

    <div class="au-page-head mb-4">
        <div class="au-head-left">
            <a href="{{ route('general.balance.index') }}" class="au-back-link">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="au-page-title">Para Çek</h1>
                <div class="text-muted small">
                    <i class="bi bi-bank me-1"></i> Kazancını IBAN adresine aktar
                </div>
            </div>
        </div>
    </div>

    @if($errors->any() || session('error'))
        <div class="admin-card mb-4 alert-card-danger">
            <div class="card-body p-3 text-danger d-flex align-items-center gap-2">
                <i class="bi bi-shield-exclamation fs-5"></i>
                <div>
                    @if(session('error'))
                        {{ session('error') }}
                    @else
                        {{ $errors->first() }}
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-4" style="box-shadow:none !important;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="section-label m-0">Kullanılabilir Bakiye</div>
                <div class="text-muted small fw-semibold">Çekebileceğin maksimum tutar</div>
            </div>
            <div class="display-6 fw-bold" style="color: var(--primary);">{{ $user->formatted_balance }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('general.balance.withdraw') }}" id="withdrawForm">
        @csrf

        <div class="admin-card mb-4">
            <div class="card-body p-4">
                <div class="section-label mb-3">Çekilecek Tutar</div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    @foreach($presets as $preset)
                        <button type="button" class="btn-preset" onclick="setWithdraw({{ $preset }}, this)">
                            {{ number_format($preset, 0, ',', '.') }} ₺
                        </button>
                    @endforeach
                </div>

                <div class="pf-field mb-4">
                    <label class="pf-label mb-2 fw-semibold">Tutar (₺) <span class="text-danger">*</span></label>
                    <input class="pf-input form-control @error('amount') is-invalid @enderror"
                           type="number" step="0.01" min="10" name="amount" id="withdrawAmount"
                           value="{{ old('amount') }}" placeholder="0,00" data-testid="withdraw-amount">
                    @error('amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label mb-2 fw-semibold">IBAN <span class="text-danger">*</span></label>
                    <input class="pf-input form-control @error('iban') is-invalid @enderror"
                           type="text" name="iban" value="{{ old('iban') }}"
                           placeholder="TR__ ____ ____ ____ ____ ____ __" maxlength="34" data-testid="withdraw-iban">
                    @error('iban')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn-admin-pri w-100 justify-content-center" data-testid="withdraw-submit">
            <i class="bi bi-cash-coin me-1"></i> Çekim Talebi Oluştur
        </button>
    </form>
</div>

@push('scripts')
<script src="{{ asset('assets/js/custom/balance-withdraw.js') }}"></script>
@endpush
@endsection
