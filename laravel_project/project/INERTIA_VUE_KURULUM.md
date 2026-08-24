# artirdim — Inertia.js + Vue 3 Kurulum & Mimari Notları

Bu proje **Laravel 12 + Blade** yapısından **Laravel 12 + Inertia.js + Vue 3** yapısına dönüştürülmektedir.
Dönüşüm ilerlemesi için bkz. `CONVERSION_PROGRESS.md`.

## Teknoloji
- **Backend:** Laravel 12 (PHP 8.2), MySQL/MariaDB
- **Frontend:** Inertia.js + Vue 3 (Composition API) — Vite ile derlenir
- **Routing (JS tarafı):** Ziggy (`@routes` direktifi + `ZiggyVue` plugin) → Vue içinde `route('name')` kullanılır
- **UI:** Mevcut Metronic (KeenThemes) Bootstrap teması KORUNDU. Tailwind EKLENMEDİ; tüm class isimleri ve `public/assets/css/*` aynen kullanılıyor.

## Yeni Eklenen Paketler
| Paket | Neden |
|---|---|
| `inertiajs/inertia-laravel` | Laravel tarafı Inertia adaptörü (`Inertia::render`) |
| `tightenco/ziggy` | Laravel route isimlerini JS'e taşır (`route()` helper) |
| `@inertiajs/vue3` | Vue 3 için Inertia client |
| `vue`, `@vitejs/plugin-vue` | Vue 3 + Vite derleme |
| `ziggy-js` | `ZiggyVue` plugin (Vue içinde `route()`) |

## Kurulum (Sıfırdan)
```bash
cd laravel_project/project

# 1) Bağımlılıklar
composer install
npm install

# 2) Ortam
cp .env.example .env   # (yoksa .env'i elle oluştur — örnek anahtarlar aşağıda)
php artisan key:generate

# 3) Veritabanı (MySQL)
#   .env içinde: DB_DATABASE=auction DB_USERNAME=auction DB_PASSWORD=auction123
mysql -u root -e "CREATE DATABASE auction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --force
php artisan db:seed --force
php artisan db:seed --class=AuctionSeeder --force
php artisan storage:link

# 4) Frontend derleme (PRODUCTION build tercih edildi)
npm run build

# 5) Sunucu
php artisan serve --host=0.0.0.0 --port=8000
```

## Geliştirme Akışı
- Blade değişikliği yerine artık **Vue** dosyaları düzenlenir: `resources/js/Pages/**` ve `resources/js/Components/**`.
- Bir sayfa değişikliğinden sonra: `npm run build` (bu ortamda `sudo supervisorctl restart laravel`).
- Vite dev server (HMR) yerine **production build** kullanılıyor (kararlılık için).

## Mimari
- **Kök layout:** `resources/views/app.blade.php` → tek Inertia giriş noktası (`@inertia`, `@routes`, `@vite`). Metronic bundle JS/CSS burada global yükleniyor. Hikaye (story) modalları da burada (Vue dışı, kalıcı DOM).
- **JS giriş:** `resources/js/app.js` → `createInertiaApp` + Vue + `ZiggyVue`. Her Inertia gezinmesinden sonra Metronic bileşenleri (`KTComponents/KTMenu/KTDrawer...`) yeniden başlatılır (`window.initKT`).
- **Paylaşılan veri:** `app/Http/Middleware/HandleInertiaRequests.php` → `auth.user`, `flash`, `headerNotifications`, `ziggy` her istekte props olarak paylaşılır.
- **Layout bileşenleri:** `resources/js/Layouts/AppLayout.vue` (header+sidebar+footer), `AuthLayout.vue` (giriş/kayıt hero paneli).
- **Ortak bileşenler:** `AuctionCard`, `StoryBar`, `Pagination`, `OrderProgress`, `OrderTimeline`, `ReviewForm`, `useClock.js`.
- **Serialize yardımcıları:** `app/helpers.php` → `story_bar_data()`, `bid_row()`; `Auction::toCard()`.

## ⚠️ KARMA MOD (Mixed Mode)
Eski `resources/views/layouts/app.blade.php` **bilinçli olarak silinmedi**. Henüz Vue'ya çevrilmemiş sayfalar (bkz. `CONVERSION_PROGRESS.md` GRUP 3/5/6/7 + messages) hâlâ eski Blade layout ile sorunsuz çalışır. Böylece site dönüşüm boyunca %100 çalışır kalır. Bir grup tamamen çevrilince eski layout kaldırılabilir.

## Test Kullanıcıları (şifre: `password`)
- admin@test.com · seller@test.com · buyer@test.com
