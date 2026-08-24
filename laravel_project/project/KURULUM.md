# 🚀 artirdim.com — Kurulum ve Çalıştırma Rehberi

Bu proje **Laravel 12 (PHP 8.2) + MariaDB/MySQL + Redis + Vite** ile yazılmıştır.
Aşağıda hem **kendi bilgisayarında (local)** hem de **sunucuda (VPS / production)** nasıl
çalıştıracağın adım adım anlatılmıştır.

> ⚠️ Not: Emergent önizleme ortamı Laravel'i **kalıcı barındırmaz** (pod yeniden başlayınca
> PHP/veritabanı sıfırlanır). Bu yüzden canlıya almak için aşağıdaki **Sunucu** adımlarını kullan.

---

## 🖥️ 1) LOCAL (Kendi Bilgisayarında)

### Gereksinimler
- PHP **8.2+** (`php -v`)
- Composer (`composer -V`)
- Node.js 18+ ve npm/yarn (`node -v`)
- MySQL veya MariaDB
- (Opsiyonel) Redis

### Adımlar

```bash
# 1. Kodu al
git clone <REPO_URL> artirdim
cd artirdim/auction-project      # Laravel projesi bu klasörde

# 2. PHP bağımlılıkları
composer install

# 3. Ortam dosyası
cp .env.example .env
php artisan key:generate

# 4. .env dosyasını düzenle (veritabanı bilgilerin):
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=auction
#    DB_USERNAME=root
#    DB_PASSWORD=(kendi mysql şifren)
#    APP_URL=http://localhost:8000

# 5. Veritabanını oluştur (MySQL'e gir):
#    CREATE DATABASE auction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 6. Tabloları ve örnek veriyi kur
php artisan migrate
php artisan db:seed
php artisan db:seed --class=AuctionSeeder
php artisan storage:link

# 7. Frontend'i derle
npm install && npm run build      # (veya: yarn install && yarn build)

# 8. Sunucuyu başlat
php artisan serve
```

➡️ Tarayıcıda aç: **http://localhost:8000**

**Geliştirme yaparken** (canlı yeniden derleme için ayrı bir terminalde):
```bash
npm run dev
```

### Test Hesapları (şifre hepsi: `password`)
| Rol   | E-posta          |
|-------|------------------|
| Admin | admin@test.com   |
| Satıcı| seller@test.com  |
| Alıcı | buyer@test.com   |

---

## 🌐 2) SUNUCU (Ubuntu 22.04 VPS / Production)

### a) Sistem paketleri
```bash
sudo apt update
sudo apt install -y nginx php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-redis \
  mariadb-server redis-server unzip git curl

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js (Vite derlemesi için)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### b) Veritabanı
```bash
sudo mysql -e "CREATE DATABASE auction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'auction'@'localhost' IDENTIFIED BY 'GÜÇLÜ_ŞİFRE';"
sudo mysql -e "GRANT ALL ON auction.* TO 'auction'@'localhost'; FLUSH PRIVILEGES;"
```

### c) Projeyi kur
```bash
cd /var/www
sudo git clone <REPO_URL> artirdim
cd artirdim/auction-project

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

# .env düzenle:  APP_ENphV=production  APP_DEBUG=false
#                APP_URL=https://senin-domainin.com
#                DB_DATABASE=auction  DB_USERNAME=auction  DB_PASSWORD=GÜÇLÜ_ŞİFRE
#                SESSION_SECURE_COOKIE=true   (HTTPS kullanıyorsan)

php artisan migrate --force
php artisan db:seed --force            # ilk kurulumda örnek veri (isteğe bağlı)
php artisan storage:link

npm install && npm run build

# İzinler
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Production cache
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### d) Nginx yapılandırması
`/etc/nginx/sites-available/artirdim` :
```nginx
server {
    listen 80;
    server_name senin-domainin.com;
    root /var/www/artirdim/auction-project/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```
```bash
sudo ln -s /etc/nginx/sites-available/artirdim /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### e) HTTPS (ücretsiz SSL)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d senin-domainin.com
```

### f) Kuyruk (queue) worker'ı — supervisor ile
`/etc/supervisor/conf.d/artirdim-queue.conf` :
```ini
[program:artirdim-queue]
command=php /var/www/artirdim/auction-project/artisan queue:work --sleep=1 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/artirdim-queue.log
```
```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start artirdim-queue:*
```

### g) ⏰ ZAMANLANMIŞ GÖREVLER (cron) — YENİ, ZORUNLU

Aşağıdaki özellikler Laravel **scheduler**'ına bağlıdır ve **cron kurulmadan çalışmaz**:
- `auctions:close` → süresi dolan açık artırmaları kapatır, kazananı belirler ve **sipariş oluşturur** (her dakika).
- `orders:auto-release` → kargolanmış ama alıcının onaylamadığı siparişleri 7 gün sonra otomatik tamamlar, ödemeyi satıcıya aktarır (saatlik).
- `stories:prune` → 24 saati dolan hikayeleri siler (saatlik).

Sunucuda **tek bir cron satırı** ekle (`crontab -e` — www-data kullanıcısıyla):
```bash
* * * * * cd /var/www/artirdim/auction-project && php artisan schedule:run >> /dev/null 2>&1
```

> Bu satır olmadan: açık artırmalar bitince otomatik kapanmaz/sipariş oluşmaz, siparişlerde 7 günlük otomatik teslim çalışmaz, süresi dolan hikayeler silinmez.
> (Önizleme ortamında cron yoktur; bu görevler sayfa erişildikçe "fırsatçı" olarak da tetiklenir, ama production'da yukarıdaki cron şarttır.)

✅ Artık site **https://senin-domainin.com** üzerinden yayında.

---

## 🔁 Hızlı Kurulum Scripti (Ubuntu/Debian)
Her şeyi otomatik yapan bir script eklendi:
```bash
cd auction-project
bash setup.sh
php artisan serve --host=0.0.0.0 --port=8000
```

## 🆘 Sık Karşılaşılan Sorunlar
- **419 / CSRF hatası:** `.env` içindeki `APP_URL` gerçek adresinle aynı olmalı; `php artisan config:clear`.
- **Görseller görünmüyor:** `php artisan storage:link` çalıştır.
- **Login 403 (proxy arkasında):** `.env`'de `APP_URL`'yi https adresinle ayarla; proje proxy güvenini (`trustProxies`) zaten içeriyor.
- **Sayfa boş/beyaz:** `php artisan optimize:clear`, ardından `storage/logs/laravel.log`'a bak.
- **Açık artırma bitince kapanmıyor / sipariş oluşmuyor:** Scheduler cron'u eklenmemiştir (bkz. bölüm **2/g**). `* * * * * php artisan schedule:run` satırını ekle.
- **Ödeme / bakiye:** Şu an bakiye yükleme **demo** amaçlıdır (gerçek ödeme sağlayıcı bağlı değildir). Canlıya almadan önce İyzico/PayTR/Stripe entegre edilmelidir.
