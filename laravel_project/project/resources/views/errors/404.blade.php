<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>404 - Sayfa Bulunamadı | artirdim.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/errors.css') }}">
</head>
<body>

    <button class="theme-btn" onclick="toggleTheme()" aria-label="Tema değiştir">
        <i class="fa-solid fa-moon" id="theme-icon"></i>
    </button>

    <div class="card">
        <div class="logo">
            <div class="logo-bars">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
            <h1 class="logo-name">artirdim<span>.com</span></h1>
        </div>

        <div class="error-code">404</div>
        <h2>Aradığınız Sayfa Bulunamadı!</h2>
        <p class="desc">Görünüşe göre bu sayfa yayından kalkmış, adresi değişmiş ya da çekiç yanlış yere vurulmuş.</p>

        <div class="actions">
            <a href="/" class="btn btn-primary">
                <i class="fa-solid fa-gavel"></i> Ana Sayfa
            </a>
            <button onclick="window.history.back()" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Geri Dön
            </button>
        </div>
    </div>

    <script src="{{ asset('assets/js/custom/errors.js') }}"></script>
</body>
</html>
