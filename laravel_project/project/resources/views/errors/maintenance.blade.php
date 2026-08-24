<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>artirdim.com - Yakında Canlıdayız</title>
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

        <div class="badge">CANLI MÜZAYEDE SİSTEMİ</div>
        <h2>Çekici Deneyimler İçin Alanı Güncelliyoruz</h2>
        <p class="desc">Daha hızlı, güvenli ve kusursuz bir canlı açık artırma deneyimi için sistemimizi bakıma aldık. Kısa süre sonra çekiç yeniden vurulacak!</p>

        <div class="progress-wrap">
            <div class="progress-bar"></div>
        </div>
        <div class="progress-label">
            <span>İlerleme</span>
            <span>%75</span>
        </div>

        <div class="status-grid">
            <div class="status-item">
                <span class="status-title">Durum</span>
                <span class="status-value"><span class="dot"></span>Optimizasyon</span>
            </div>
            <div class="status-item">
                <span class="status-title">Hedef</span>
                <span class="status-value">%98 Hazır</span>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2026 artirdim.com. Tüm hakları saklıdır.</p>
        </div>
    </div>

    <script src="{{ asset('assets/js/custom/errors.js') }}"></script>
</body>
</html>
