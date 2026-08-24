<!DOCTYPE html>
<html lang="tr">

<head>
    <title>{{ config('app.name') }} | @yield('title', 'Giriş Yap')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="tr_TR" />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/auth.css') }}" rel="stylesheet" />
    <link rel="icon" href="{{ asset('assets/media/logos/favicon.svg') }}" type="image/x-icon" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <script src="{{ asset('assets/js/custom/auth-header.js') }}"></script>
</head>

<body id="kt_body" class="app-blank">

<div class="loading">
    <svg width="48" height="48" viewBox="0 0 48 48">
        <g fill="none">
            <path fill="#155eef"
                d="M24,48 C10.7,48 0,37.2 0,24 C0,10.7 10.7,0 24,0 C37.2,0 48,10.7 48,24 C48,37.2 37.2,48 24,48 Z"/>
        </g>
    </svg>
</div>

<div class="d-flex flex-column flex-root" id="kt_app_root">

    <div class="d-flex flex-column flex-lg-row flex-column-fluid">

        <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 auth-left order-2 order-lg-1">

            <div class="auth-container d-flex flex-center flex-column flex-lg-row-fluid">

                <div class="w-lg-500px w-100">
