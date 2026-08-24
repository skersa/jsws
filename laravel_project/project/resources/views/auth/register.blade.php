@extends('auth.layouts.master')
@section('title', 'Kaydol')

@section('content')
@php
    $errStep = 1;
    $finalLabel = '';
    if ($errors->any()) {
        if ($errors->hasAny(['tax_number','iban','company_name','id_document'])) {
            $errStep = 2;
        } elseif ($errors->hasAny(['password'])) {
            $errStep = 3;
            $finalLabel = old('role') === 'seller' ? 'Adım 3 / 3' : 'Adım 2 / 2';
        }
    }
@endphp

<div id="authRegisterRoot"
     data-has-errors="{{ $errors->any() ? '1' : '0' }}"
     data-error-step="{{ $errStep }}"
     data-final-step-label="{{ $finalLabel }}"></div>

<form class="form w-100" id="kt_sign_up_form" method="post"
    action="{{ route('register') }}" enctype="multipart/form-data">

    <div class="auth-header text-center mb-8">
        <img src="{{ asset('assets/media/logos/logo-light.svg') }}" class="logo-light auth-logo" alt="Artirdim">
        <img src="{{ asset('assets/media/logos/logo-dark.svg') }}" class="logo-dark auth-logo" alt="Artirdim">
    </div>

    @csrf


    <div id="step_1">

        <div class="mb-6">
            <label class="form-label text-muted fs-7 fw-semibold mb-3">Hesap Türü</label>
            <div class="row g-3">
               <div class="col-6">
    <label class="d-block cursor-pointer">
        <input type="radio" name="role" value="buyer" class="d-none role-radio"
            {{ old('role', 'buyer') === 'buyer' ? 'checked' : '' }}>
        <div class="role-card p-4 rounded-2 text-center border border-dashed
            {{ old('role', 'buyer') === 'buyer' ? 'border-primary bg-light-primary' : 'border-secondary bg-transparent' }}">
            <div class="symbol symbol-40px mx-auto mb-3">
                <div class="symbol-label rounded-circle role-icon-wrap
                    {{ old('role', 'buyer') === 'buyer' ? 'bg-primary' : 'bg-secondary' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        class="{{ old('role', 'buyer') === 'buyer' ? 'text-white' : 'text-muted' }}"
                        viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.029 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                    </svg>
                </div>
            </div>
            <div class="fw-bold role-label {{ old('role', 'buyer') === 'buyer' ? 'text-primary' : 'text-muted' }}">Alıcı</div>
            <div class="text-muted fs-8 mt-1">Teklif ver, satın al</div>
        </div>
    </label>
</div>
<div class="col-6">
    <label class="d-block cursor-pointer">
        <input type="radio" name="role" value="seller" class="d-none role-radio"
            {{ old('role') === 'seller' ? 'checked' : '' }}>
        <div class="role-card p-4 rounded-2 text-center border border-dashed
            {{ old('role') === 'seller' ? 'border-primary bg-light-primary' : 'border-secondary bg-transparent' }}">
            <div class="symbol symbol-40px mx-auto mb-3">
                <div class="symbol-label rounded-circle role-icon-wrap
                    {{ old('role') === 'seller' ? 'bg-primary' : 'bg-secondary' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        class="{{ old('role') === 'seller' ? 'text-white' : 'text-muted' }}"
                        viewBox="0 0 16 16">
                        <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.371 2.371 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5M4 15h3v-5H4zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm3 0h-2v3h2z"/>
                    </svg>
                </div>
            </div>
            <div class="fw-bold role-label {{ old('role') === 'seller' ? 'text-primary' : 'text-muted' }}">Satıcı</div>
            <div class="text-muted fs-8 mt-1">İlan ver, sat</div>
        </div>
    </label>
</div>
            </div>
            @error('role')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-4">
            <div class="form-floating">
                <input type="text" name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Ad Soyad" value="{{ old('name') }}">
                <label>Ad Soyad</label>
            </div>
            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="fv-row mb-4">
            <div class="form-floating">
                <input type="text" name="username" id="username"
                    class="form-control @error('username') is-invalid @enderror"
                    placeholder="kullanici_adi"
                    value="{{ old('username') }}"
                    maxlength="30"
                    autocomplete="username">
                <label>Kullanıcı Adı</label>
            </div>
            @error('username') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="fv-row mb-4">
            <div class="form-floating">
                <input type="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="E-posta" value="{{ old('email') }}">
                <label>E-posta</label>
            </div>
            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="fv-row mb-6">
            <div class="form-floating">
                <input type="text" name="phone"
                    class="form-control @error('phone') is-invalid @enderror"
                    placeholder="Telefon" value="{{ old('phone') }}">
                <label>GSM Numarası</label>
            </div>
            @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <button type="button" id="btn_next_1" class="btn btn-auth-primary btn-lg w-100">
            Devam et
            <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="15px" fill="#e3e3e3"><path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/></svg>
        </button>
        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="btn btn-auth-outline btn-lg w-100">
                Zaten hesabın var mı? Giriş yap
            </a>
        </div>
    </div>

    <div id="step_2" style="display:none">

        @if($errors->hasAny(['tax_number','iban','company_name','id_document']))
            <div class="alert alert-danger mb-6">
                <ul class="mb-0">
                    @foreach(['tax_number','iban','company_name','id_document'] as $field)
                        @error($field) <li>{{ $message }}</li> @enderror
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted fs-8">Adım 2 / 3</span>
            <span class="text-primary fs-8 fw-semibold">Satıcı Doğrulama</span>
        </div>
        <div class="progress h-6px mb-8">
            <div class="progress-bar bg-primary" style="width:66%"></div>
        </div>

        <div class="fv-row mb-4">
            <div class="form-floating">
                <input type="text" name="company_name"
                    class="form-control @error('company_name') is-invalid @enderror"
                    placeholder="Şirket Adı" value="{{ old('company_name') }}">
                <label>Şirket Adı <span class="text-muted fs-8">(opsiyonel)</span></label>
            </div>
            @error('company_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="fv-row mb-4">
            <div class="form-floating">
                <input type="text" name="tax_number"
                    class="form-control @error('tax_number') is-invalid @enderror"
                    placeholder="Vergi Numarası" value="{{ old('tax_number') }}">
                <label>Vergi Numarası</label>
            </div>
            @error('tax_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="fv-row mb-4">
            <div class="form-floating">
                <input type="text" name="iban"
                    class="form-control @error('iban') is-invalid @enderror"
                    placeholder="IBAN" value="{{ old('iban') }}" maxlength="34"
                    style="text-transform:uppercase;letter-spacing:1px">
                <label>IBAN</label>
            </div>
            @error('iban') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="fv-row mb-6">
            <label class="form-label text-muted fs-7 fw-semibold mb-2">
                Kimlik Belgesi
                <span class="text-muted fs-8">(JPG, PNG veya PDF — maks. 5MB)</span>
            </label>
            <input type="file" name="id_document"
                class="form-control @error('id_document') is-invalid @enderror"
                accept=".jpg,.jpeg,.png,.pdf">
            @error('id_document') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex gap-3">
            <button type="button" id="btn_back_2" class="btn btn-auth-outline btn-lg py-3 fw-semibold" style="width:30%">Geri</button>
            <button type="button" id="btn_next_2" class="btn btn-auth-primary btn-lg py-3 fw-semibold flex-grow-1">
                Devam et
                <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="15px" fill="#e3e3e3"><path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/></svg>
            </button>
        </div>
    </div>

    <div id="step_3" style="display:none">

        @if($errors->hasAny(['password']))
            <div class="alert alert-danger mb-6">
                <ul class="mb-0">
                    @error('password') <li>{{ $message }}</li> @enderror
                </ul>
            </div>
        @endif

        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted fs-8" id="step_label">Adım 2 / 2</span>
            <span class="text-success fs-8 fw-semibold">Şifre</span>
        </div>
        <div class="progress h-6px mb-8">
            <div class="progress-bar bg-success" style="width:100%"></div>
        </div>

        <div class="fv-row mb-4">
            <div class="form-floating position-relative" data-kt-password-meter="true">
                <input type="password" name="password" id="password"
                    class="form-control auth-input pe-5 @error('password') is-invalid @enderror"
                    placeholder="Şifre" autocomplete="off">
                <label for="password">Şifre</label>
                <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0"
                    data-kt-password-meter-control="visibility">
                    <i class="bi bi-eye-slash fs-2"></i>
                    <i class="bi bi-eye fs-2 d-none"></i>
                </span>
            </div>
        </div>

        <div class="mb-4">
            <div style="height:4px;border-radius:2px;background:var(--border,#e4e6ef);overflow:hidden;margin-bottom:5px">
                <div id="password_strength_bar" style="height:100%;width:0;border-radius:2px;transition:width .3s,background .3s"></div>
            </div>
            <span id="password_strength_text" style="font-size:11px;font-weight:600"></span>
        </div>

        <div class="fv-row mb-6">
            <div class="form-floating position-relative" data-kt-password-meter="true">
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="form-control auth-input pe-5"
                    placeholder="Şifre Tekrar" autocomplete="off">
                <label for="password_confirmation">Şifre Tekrar</label>
                <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0"
                    data-kt-password-meter-control="visibility">
                    <i class="bi bi-eye-slash fs-2"></i>
                    <i class="bi bi-eye fs-2 d-none"></i>
                </span>
            </div>
            <div id="password_mismatch_error" class="text-danger small mt-1" style="display:none">Şifreler eşleşmiyor.</div>
        </div>

        <div class="mb-8">
            <label class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" name="terms" id="terms_check">
                <span class="form-check-label text-muted fs-7">
                    <a href="#" class="text-primary">Kullanım koşullarını</a> okudum ve kabul ediyorum
                </span>
            </label>
            <div id="terms_error" class="text-danger small mt-1" style="display:none">Kullanım koşullarını kabul etmelisiniz.</div>
        </div>

        <div class="d-flex gap-3">
            <button type="button" id="btn_back_3" class="btn btn-auth-outline btn-lg py-3 fw-semibold" style="width:30%">Geri</button>
            <button type="submit" id="kt_sign_up_submit" class="btn btn-auth-primary btn-lg py-3 fw-semibold flex-grow-1">
                <span class="indicator-label">Kayıt Ol</span>
                <span class="indicator-progress">
                    Lütfen bekleyin...
                    <span class="spinner-border spinner-border-sm ms-2 align-middle"></span>
                </span>
            </button>
        </div>
    </div>

</form>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/authentication/sign-up/general.js') }}"></script>
<script src="{{ asset('assets/js/custom/auth-register.js') }}"></script>
@endpush
