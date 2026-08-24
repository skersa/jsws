@extends('layouts.app')
@section('title', 'Bakiye Yükle')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/balance-create.css') }}">
@endpush

@section('content')
@php $paymentMethod = old('payment_method', 'credit_card'); @endphp
<div class="au-page-wrap balance-create pm-{{ $paymentMethod }}">

    <div class="au-page-head mb-4">
        <div class="au-head-left">
            <a href="{{ route('general.balance.index') }}" class="au-back-link">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="au-page-title">Bakiye Yükle</h1>
                <div class="text-muted small">
                    <i class="bi bi-shield-lock-fill text-success me-1"></i> 256-Bit SSL korumalı güvenli ödeme altyapısı
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
                        {{ $errors->count() }} hata bulundu, lütfen bilgilerinizi kontrol edin.
                    @endif
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('general.balance.store') }}" id="paymentForm">
        @csrf

        <div class="admin-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-label">Yüklenecek Tutar</div>
                    <span class="a-badge info"><i class="bi bi-lock-fill"></i> Güvenli İşlem</span>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    @foreach($presets as $preset)
                        <button type="button"
                                class="btn-preset"
                                onclick="setAmount({{ $preset }}, this)">
                            {{ number_format($preset, 0, ',', '.') }} ₺
                        </button>
                    @endforeach
                </div>

                <div class="pf-field">
                    <label class="pf-label mb-2 fw-semibold">Tutar Girin <span class="text-danger">*</span></label>
                    <div class="input-group customs-input-group has-validation">
                        <span class="input-group-text fw-bold text-muted border-end-0">₺</span>
                        <input class="pf-input form-control border-start-0 dynamic-input amount-input-style @error('amount') is-invalid-error @enderror"
                               type="number"
                               id="amount"
                               name="amount"
                               value="{{ old('amount') }}"
                               min="10"
                               max="50000"
                               step="0.01"
                               placeholder="Örn: 500" required>
                    </div>

                    @error('amount')
                        <div class="pf-input-error-msg">
                            <i class="bi bi-exclamation-circle"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="text-muted mt-2" style="font-size: var(--fs-xs);">
                        <i class="bi bi-info-circle me-1"></i> Minimum 10 ₺ · Maksimum 50.000 ₺ arası anında bakiye yükleyebilirsiniz.
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-4">
            <div class="card-body p-4">
                <div class="section-label mb-3">Ödeme Yöntemi Seçin</div>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="payment-tile-modern {{ old('payment_method','credit_card')=='credit_card' ? 'active' : '' }}">
                            <input type="radio" name="payment_method" value="credit_card"
                                   {{ old('payment_method','credit_card')=='credit_card' ? 'checked' : '' }}
                                   onchange="togglePaymentFields(this.value)" class="d-none">
                            <div class="d-flex align-items-center gap-3">
                                <div class="tile-icon"><i class="bi bi-credit-card-2-front"></i></div>
                                <div>
                                    <div class="tile-title">Kredi / Banka Kartı</div>
                                    <div class="tile-desc">Anında Yüklenir</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="payment-tile-modern {{ old('payment_method')=='bank_transfer' ? 'active' : '' }}">
                            <input type="radio" name="payment_method" value="bank_transfer"
                                   {{ old('payment_method')=='bank_transfer' ? 'checked' : '' }}
                                   onchange="togglePaymentFields(this.value)" class="d-none">
                            <div class="d-flex align-items-center gap-3">
                                <div class="tile-icon"><i class="bi bi-bank"></i></div>
                                <div>
                                    <div class="tile-title">Havale / EFT / Fast</div>
                                    <div class="tile-desc">1-2 İş Günü</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="payment-tile-modern {{ old('payment_method')=='papara' ? 'active' : '' }}">
                            <input type="radio" name="payment_method" value="papara"
                                   {{ old('payment_method')=='papara' ? 'checked' : '' }}
                                   onchange="togglePaymentFields(this.value)" class="d-none">
                            <div class="d-flex align-items-center gap-3">
                                <div class="tile-icon"><i class="bi bi-wallet2"></i></div>
                                <div>
                                    <div class="tile-title">Papara ile Öde</div>
                                    <div class="tile-desc">7/24 Hızlı Transfer</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                @error('payment_method')
                    <div class="pf-input-error-msg mt-2">
                        <i class="bi bi-exclamation-circle"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
        </div>

        <div class="admin-card mb-4" id="cardFields">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="section-label">Kart Bilgileri</div>
                    <span class="text-muted small"><i class="bi bi-shield-check text-success"></i> 3D Secure Aktif</span>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="pf-label mb-2">Kart Sahibi</label>
                        <input class="pf-input form-control dynamic-input @error('card_holder') is-invalid-error @enderror" type="text" name="card_holder" value="{{ old('card_holder') }}" placeholder="Ad Soyad">
                        @error('card_holder')
                            <div class="pf-input-error-msg"><i class="bi bi-exclamation-circle"></i> <span>{{ $message }}</span></div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="pf-label mb-2">Kart Numarası</label>
                        <input class="pf-input form-control dynamic-input @error('card_number') is-invalid-error @enderror" type="text" name="card_number" value="{{ old('card_number') }}" placeholder="0000 0000 0000 0000" maxlength="19" oninput="formatCardNumber(this)">
                        @error('card_number')
                            <div class="pf-input-error-msg"><i class="bi bi-exclamation-circle"></i> <span>{{ $message }}</span></div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label class="pf-label mb-2">Son Kullanma Tarihi (SKT)</label>
                        <input class="pf-input form-control dynamic-input @error('card_expiry') is-invalid-error @enderror" type="text" name="card_expiry" value="{{ old('card_expiry') }}" placeholder="AA/YY" maxlength="5" oninput="formatExpiry(this)">
                        @error('card_expiry')
                            <div class="pf-input-error-msg"><i class="bi bi-exclamation-circle"></i> <span>{{ $message }}</span></div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label class="pf-label mb-2">Güvenlik Kodu (CVV)</label>
                        <input class="pf-input form-control dynamic-input @error('card_cvv') is-invalid-error @enderror" type="password" name="card_cvv" placeholder="•••" maxlength="4">
                        @error('card_cvv')
                            <div class="pf-input-error-msg"><i class="bi bi-exclamation-circle"></i> <span>{{ $message }}</span></div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-4" id="bankFields">
            <div class="card-body p-4">
                <div class="section-label mb-3">Banka Transfer Bilgileri</div>

                <div class="p-3 mb-3 border rounded d-flex gap-2 text-muted alert-box-info">
                    <i class="bi bi-exclamation-circle-fill text-primary"></i>
                    <div>Transferi yaparken <b>Açıklama</b> alanına <b>Kullanıcı Adınızı veya ID</b> bilginizi eksikosiz yazmanız işlemleri hızlandıracaktır.</div>
                </div>

                <div class="p-3 border rounded d-flex justify-content-between align-items-center info-row-bg">
                    <div>
                        <div class="text-muted small fw-semibold">Alıcı / IBAN</div>
                        <div class="fw-bold tracking-wide mt-1 target-text-color">TR00 0000 0000 0000 0000 0000 00</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-copy-modern" onclick="copyValue('TR00000000000000000000000000', this)">
                        <i class="bi bi-clipboard me-1"></i> <span>Kopyala</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="admin-card mb-4" id="paparaFields">
            <div class="card-body p-4">
                <div class="section-label mb-3">Papara Hesap Bilgileri</div>

                <div class="p-3 mb-3 border rounded d-flex gap-2 text-muted alert-box-warning">
                    <i class="bi bi-lightning-charge-fill text-warning"></i>
                    <div>7/24 anında transfer. Açıklama kısmına kullanıcı ID numaranızı girmeyi unutmayınız.</div>
                </div>

                <div class="p-3 border rounded d-flex justify-content-between align-items-center info-row-bg">
                    <div>
                        <div class="text-muted small fw-semibold">Papara Numarası</div>
                        <div class="fw-bold tracking-wide mt-1 target-text-color">1234567890</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-copy-modern" onclick="copyValue('1234567890', this)">
                        <i class="bi bi-clipboard me-1"></i> <span>Kopyala</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <a href="{{ route('general.balance.index') }}" class="btn-admin-sec text-decoration-none">
                    Vazgeç
                </a>
                <button type="submit" class="btn-admin-pri">
                    <i class="bi bi-shield-check"></i> Güvenli Ödemeyi Başlat
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/balance-create.js') }}"></script>
@endpush
