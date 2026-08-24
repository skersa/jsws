@extends('auth.layouts.master')

@section('content')

    <div class="auth-form w-100">

        <div class="text-center mb-10">

            <div class="auth-header text-center mb-8">
        <img src="{{ asset('assets/media/logos/logo-light.svg') }}" class="logo-light auth-logo" alt="Artirdim">
        <img src="{{ asset('assets/media/logos/logo-dark.svg') }}" class="logo-dark auth-logo" alt="Artirdim">
    </div>

            <h1 class="fw-bold fs-2 mb-2">
                E-posta adresini doğrula
            </h1>

            <p class="text-muted fs-6">
                Hesabını kullanabilmek için e-posta doğrulaması gerekiyor
            </p>

        </div>

        <div class="alert alert-info mb-6">
            Kayıt olurken gönderilen doğrulama linkine tıklaman gerekiyor. Eğer mail gelmediyse tekrar gönderebilirsin.
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success mb-6">
                Yeni doğrulama linki e-posta adresine gönderildi.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-auth-primary btn-lg">
                Tekrar Gönder
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <div class="d-grid">
                <button type="submit" class="btn btn-auth-outline btn-lg">
                    Çıkış Yap
                </button>
            </div>
        </form>

        <div class="text-center mt-6">
            <span class="text-muted fs-7">
                Mail gelmediyse spam klasörünü kontrol et
            </span>
        </div>

    </div>

@endsection
