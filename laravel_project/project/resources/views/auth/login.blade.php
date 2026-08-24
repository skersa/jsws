@extends('auth.layouts.master')

@section('content')
    <form class="form w-100 auth-form" method="post" action="{{ route('login') }}">
        @csrf

        <div class="text-center mb-10">

    <div class="mb-6">

        <img
            src="{{ asset('assets/media/logos/logo-light.svg') }}"
            class="logo-light auth-logo"
            alt="Artirdim"
        >

        <img
            src="{{ asset('assets/media/logos/logo-dark.svg') }}"
            class="logo-dark auth-logo"
            alt="Artirdim"
        >

    </div>


    <h1 class="fw-bold fs-2 mb-2">
        Tekrar Hoşgeldin
    </h1>

    <p class="text-muted fs-6">
        Hesabına giriş yap ve canlı müzayedelere katıl
    </p>

</div>
        <div class="d-grid mb-4">
            <a href="{{ route('google.redirect') }}" class="btn btn-light btn-lg border d-flex align-items-center justify-content-center gap-2" data-testid="google-login-button">
                <svg width="18" height="18" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.00018 3.16667C9.18018 3.16667 10.2368 3.57333 11.0702 4.36667L13.3535 2.08333C11.9668 0.793333 10.1568 0 8.00018 0C4.87352 0 2.17018 1.79333 0.853516 4.40667L3.51352 6.47C4.14352 4.57333 5.91352 3.16667 8.00018 3.16667Z" fill="#EA4335"></path>
                    <path d="M15.66 8.18335C15.66 7.66002 15.61 7.15335 15.5333 6.66669H8V9.67335H12.3133C12.12 10.66 11.56 11.5 10.72 12.0667L13.2967 14.0667C14.8 12.6734 15.66 10.6134 15.66 8.18335Z" fill="#4285F4"></path>
                    <path d="M3.51 9.53001C3.35 9.04668 3.25667 8.53334 3.25667 8.00001C3.25667 7.46668 3.34667 6.95334 3.51 6.47001L0.85 4.40668C0.306667 5.48668 0 6.70668 0 8.00001C0 9.29334 0.306667 10.5133 0.853333 11.5933L3.51 9.53001Z" fill="#FBBC05"></path>
                    <path d="M8.0001 16C10.1601 16 11.9768 15.29 13.2968 14.0633L10.7201 12.0633C10.0034 12.5467 9.0801 12.83 8.0001 12.83C5.91343 12.83 4.14343 11.4233 3.5101 9.52667L0.850098 11.59C2.1701 14.2067 4.87343 16 8.0001 16Z" fill="#34A853"></path>
                </svg>
                Google ile devam et
            </a>
        </div>
        <div class="d-flex align-items-center my-6">
            <div class="border-bottom w-100"></div>
            <span class="px-3 text-muted small">veya</span>
            <div class="border-bottom w-100"></div>
        </div>

        <div class="form-floating mb-4">
            <input type="text" name="email" id="email"
                class="form-control auth-input @error('email') is-invalid @enderror" placeholder="E-posta adresi veya kullanıcı adı"
                value="{{ old('email') }}" autocomplete="off" required>
            <label for="email">E-posta adresi veya kullanıcı adı</label>

            @error('email')
                <div class="invalid-feedback d-block text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-floating mb-4 position-relative" data-kt-password-meter="true">
            <input type="password" name="password" id="password"
                class="form-control auth-input pe-5 @error('password') is-invalid @enderror" placeholder="Şifre"
                autocomplete="off" required>
            <label for="password">Şifre</label>

            <span
                class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 {{ $errors->has('password') ? 'pb-5' : '' }}"
                data-kt-password-meter-control="visibility">
                <i class="bi bi-eye-slash fs-2"></i>
                <i class="bi bi-eye fs-2 d-none"></i>
            </span>

            @error('password')
                <div class="invalid-feedback d-block text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-8">

            <label class="form-check text-muted">
                <input class="form-check-input" type="checkbox" name="remember" value="1">
                <span class="ms-2">Beni Hatırla</span>
            </label>

            <a href="{{ route('password.request') }}" class="text-primary text-decoration-none">
                Şifremi unuttum?
            </a>

        </div>

        <div class="d-grid mb-4">
            <button type="submit" class="btn btn-auth-primary btn-lg">
                Giriş Yap
            </button>
        </div>

        <div class="d-grid">
            <a href="{{ route('register') }}" class="btn btn-auth-outline btn-lg">
                Hesabın yok mu? Kayıt ol
            </a>
        </div>

    </form>
@endsection
