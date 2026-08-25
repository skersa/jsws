
## Ortam yeniden kurulumu (2026-06, bu oturum — xguvxn/xsaz reposundan)
- Yeni Emergent pod'una GitHub repo (xguvxn/xsaz) klonlandı; gerçek Laravel projesi `/app/laravel_project/project/`.
- Kurulan stack: PHP 8.2 + eklentiler, Composer 2.10, MariaDB 10.11, Redis; `composer install`, `migrate` (28 migration), `db:seed` (+AuctionSeeder, LiveDataSeeder), `storage:link`.
- Frontend: `yarn install` + `yarn build`. Eksik `livekit-client` paketi eklendi (package.json'a yazıldı), Vite build başarılı.
- `.env` preview için oluşturuldu (APP_URL=preview, DB=auction/auction123, SESSION/QUEUE/CACHE=database, BROADCAST=log, LIVEKIT boş).
- Supervisor: `/etc/supervisor/conf.d/laravel.conf` → mariadb, redis, laravel (php artisan serve :3000, PHP_CLI_SERVER_WORKERS=6), laravel-queue. Scaffold frontend/backend/mongodb durduruldu.
- Doğrulama: ana sayfa preview'da HTTP 200 + görsel olarak yükleniyor (16 aktif ilan, hikayeler, kategoriler). LiveKit anahtarları yok → canlı yayın devre dışı.
- Bilinen: bazı seed görselleri "Görsel bulunamadı" (P2), LIVEKIT_* anahtarları girilmeli (canlı yayın için).

## Kıdemli inceleme oturumu (2026-06)
- Uygulanan+test edilen düzeltmeler: Bug1 (ilan silme redirect), Bug2 (yayın yalnız active), Bug3 (story profil scope), Bug5 (mobil teklif çip değeri + focus kaldırma), kritik confirm/alert→Swal.
- Test: /app/test_reports/iteration_1.json (5/6 pass) + iteration_2.json (Bug1 retest PASS). Tümü ✅.
- BLOCKED: Bug4 canlı yayın gerçek testi (LiveKit anahtarı yok).
- Bekleyen kararlar: bid race condition fix, store() approval-bypass; bekleyen raporlar: performance, SEO, Emergent klasör temizliği.
