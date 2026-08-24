@extends('auth.layouts.master')
@section('title', 'Şifre Sıfırlama')

@section('content')

    @php
        $token = $request->route('token');
    @endphp

    <form class="form w-100" method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="text-center mb-10">
            <h1 class="text-muted fw-bolder mb-3">Yeni Şifre Oluştur</h1>
            <div class="text-gray-500 fw-semibold fs-6">
                Hesabın için güçlü bir şifre belirle
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success mb-6">
                {{ session('status') }}
            </div>
        @endif

        <div class="form-floating mb-4">
            <input type="email" name="email" class="form-control auth-input @error('email') is-invalid @enderror"
                placeholder="E-Posta" value="{{ old('email', $request->email) }}" required>

            <label>E-posta adresi</label>

            @error('email')
                <div class="invalid-feedback d-block text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-floating mb-4 position-relative" data-kt-password-meter="true">
            <input type="password" name="password" class="form-control pe-10 @error('password') is-invalid @enderror"
                placeholder="Şifre" value="{{old('password')}}" required>

            <label>Şifre</label>
              @error('password')
                <div class="invalid-feedback d-block text-danger small">
                    {{ $message }}
                </div>
            @enderror

            <span
                class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 {{ $errors->has('password') ? 'pb-5' : '' }}"
                data-kt-password-meter-control="visibility">
                <i class="bi bi-eye-slash fs-2"></i>
                <i class="bi bi-eye fs-2 d-none"></i>
            </span>
        </div>

        <div class="form-floating mb-4 position-relative" data-kt-password-meter="true">
            <input type="password" name="password_confirmation" class="form-control pe-10" placeholder="Şifre tekrar" value="{{old('password_confirmation')}}"
                required>

            <label>Şifre Tekrar</label>
              @error('password_confirmation')
                <div class="invalid-feedback d-block text-danger small">
                    {{ $message }}
                </div>
            @enderror
            <span
                class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 {{ $errors->has('password_confirmation') ? 'pb-5' : '' }}"
                data-kt-password-meter-control="visibility">
                <i class="bi bi-eye-slash fs-2"></i>
                <i class="bi bi-eye fs-2 d-none"></i>
            </span>
        </div>

        <div class="d-grid mb-4">
            <button class="btn btn-auth-primary btn-lg fw-bold" type="submit">
                Şifreyi Güncelle
            </button>
        </div>

        <div class="d-grid">
            <a href="{{ route('login') }}" class="btn btn-auth-outline btn-lg">
                ← Geri Dön
            </a>
        </div>

    </form>

@endsection
