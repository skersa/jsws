@extends('auth.layouts.master')
@section('title', 'Şifremi unuttum')
@section('content')
    <form class="form w-100" method="post" action="{{ route('password.email') }}">
        @csrf
        <div class="text-center mb-10">
            <h1 class="text-muted fw-bolder mb-3">Şifrenizi mi unuttunuz?</h1>
            <div class="text-gray-500 fw-semibold fs-6">Şifrenizi sıfırlamak için e-posta adresinizi girin.</div>
        </div>
        @if (session()->has('status'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                <span class="svg-icon svg-icon-2hx svg-icon-success me-4">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.3"
                            d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z"
                            fill="currentColor"></path>
                        <path
                            d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z"
                            fill="currentColor"></path>
                    </svg>
                </span>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-success">İşlem Başarılı.</h4>
                    <span>E-posta adresinize şifre yenileme bağlantısı içeren bir posta gönderdik.</span>
                </div>
            </div>
        @endif
        <div class="form-floating mb-5">

            <input type="email" name="email" class="form-control auth-input @error('email') is-invalid @enderror"
                id="email" placeholder="E-Posta" value="{{ old('email') }}" autocomplete="off" required>

            <label class="text-muted">E-posta adresi</label>

            @error('email')
                <div class="invalid-feedback d-block text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-grid mb-4">
            <button class="btn btn-auth-primary btn-lg fw-bold" type="submit">
                Gönder
            </button>
        </div>

        <div class="d-grid">
            <a href="{{ route('login') }}" class="btn btn-auth-outline btn-lg">
                
<svg xmlns="http://www.w3.org/2000/svg" height="12px" viewBox="0 -960 960 960" width="15px" fill="#e3e3e3"><path d="M640-80 240-480l400-400 71 71-329 329 329 329-71 71Z"/></svg> Geri dön
            </a>
        </div>
    </form>
@endsection
