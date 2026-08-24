<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark" data-image-fallback="{{ asset('assets/media/placeholder.svg') }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | @yield('title', 'Müzayede')</title>
    <link rel="icon" href="{{ asset('assets/media/logos/favicon.svg') }}" type="image/x-icon" />

    <link rel="stylesheet" href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.bundle.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme-new.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="{{ asset('assets/js/custom/app-init.js') }}"></script>




    @stack('styles')
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true">

    <div class="loading">
        <svg width="48" height="48" viewBox="0 0 48 48">
            <g fill="none">
                <path fill="#155eef" d="M24,48 C10.7,48 0,37.2 0,24 C0,10.7 10.7,0 24,0 C37.2,0 48,10.7 48,24 C48,37.2 37.2,48 24,48 Z" />
            </g>
        </svg>
    </div>

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

            @include('layouts.partials.header')

            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

                @include('layouts.partials.sidebar')

                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-xxl">
                                @yield('content')
                            </div>
                        </div>
                    </div>

                    <div id="kt_app_footer" class="app-footer">
                        <div class="app-container container-xxl d-flex flex-column flex-md-row align-items-center justify-content-between py-4">

                            <div class="d-flex align-items-center text-gray-500 fw-semibold fs-7">
                                <span>©{{ date('Y') }}</span>

                                <a href="https://artirdim.com" target="_blank" class="mx-2 text-gray-800 text-hover-primary fw-bold fs-7 text-decoration-none">
                                    Artirdim.com
                                </a>

                                <span class="text-muted">
                                    Tüm hakları saklıdır.
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-4 mt-3 mt-md-0">

                                <a href="{{ route('corporate') }}" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7">
                                    Hakkımızda
                                </a>

                                <a href="{{ route('contact') }}" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7">
                                    İletişim
                                </a>

                                <a href="{{ route('privacy') }}" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7">
                                    Gizlilik Politikası
                                </a>

                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/theme/toggle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/theme/search.js') }}"></script>
    <script src="{{ asset('assets/js/custom/theme/notification.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    {{-- Kırık/eksik görseller için otomatik placeholder --}}
    <script src="{{ asset('assets/js/custom/theme/image-fallback.js') }}"></script>
    <script src="{{ asset('assets/js/custom/theme/ajax-delete.js') }}"></script>
    <script src="{{ asset('assets/js/custom/story-data.js') }}"></script>

    {{-- Select2 (iç içe kategori seçimleri vb.) --}}
    
    @stack('scripts')
</body>

</html>
