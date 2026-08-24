<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark" data-image-fallback="{{ asset('assets/media/placeholder.svg') }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('assets/media/logos/favicon.svg') }}" type="image/x-icon" />

    <script>
        (function () {
            var t = localStorage.getItem('theme') || 'dark';
            var d = document.documentElement;
            d.classList.remove('light-mode', 'dark-mode');
            d.classList.add(t + '-mode');
            d.setAttribute('data-bs-theme', t === 'dark' ? 'dark' : 'light');
            d.classList.add('loaded');
        })();
    </script>

    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.bundle.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme-new.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/story-upload.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/auction-show.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/profile-show.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/balance-create.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/balance-show.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/seller-live-card.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/seller-auction-show.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-support-index.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-fixes.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true">

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('assets/js/custom/theme/image-fallback.js') }}"></script>
    <script src="{{ asset('assets/js/custom/theme/ajax-delete.js') }}"></script>

    {{-- Hikaye (story) görüntüleyici + yükleme modalı artık Vue bileşenleridir:
         resources/js/Components/StoryViewer.vue & StoryUpload.vue (AppLayout içinde, Teleport ile body altına).
         Böylece SPA gezinmede (guest→auth) her zaman yüklenir. --}}

    @inertia
</body>

</html>
