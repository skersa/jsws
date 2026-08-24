#!/usr/bin/env bash
# =========================================================
#  artirdim.com — Laravel Açık Artırma · Otomatik Kurulum
#  Ubuntu/Debian için. (Emergent pod'unda da çalışır.)
#  Kullanım:  bash setup.sh
# =========================================================
set -e
cd "$(dirname "$0")"

echo "==> 1/8  Sistem paketleri kuruluyor (PHP 8.2, MariaDB, Redis, Composer)..."
if ! command -v php >/dev/null; then
  apt-get update -y
  DEBIAN_FRONTEND=noninteractive apt-get install -y \
    php8.2-cli php8.2-common php8.2-bcmath php8.2-curl php8.2-gd php8.2-intl \
    php8.2-mbstring php8.2-mysql php8.2-redis php8.2-xml php8.2-zip \
    mariadb-server redis-server unzip curl
fi
if ! command -v composer >/dev/null; then
  php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
  php composer-setup.php --quiet && mv composer.phar /usr/local/bin/composer && rm -f composer-setup.php
fi

echo "==> 2/8  MariaDB & Redis başlatılıyor..."
mkdir -p /var/lib/mysql /var/run/mysqld && chown -R mysql:mysql /var/lib/mysql /var/run/mysqld
[ -d /var/lib/mysql/mysql ] || mariadb-install-db --user=mysql --datadir=/var/lib/mysql >/dev/null 2>&1
pgrep -x mariadbd >/dev/null || (nohup mariadbd --user=mysql --datadir=/var/lib/mysql >/tmp/mariadb.log 2>&1 &)
pgrep -x redis-server >/dev/null || (nohup redis-server >/tmp/redis.log 2>&1 &)
sleep 5

echo "==> 3/8  Veritabanı oluşturuluyor..."
mysql -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS auction CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'auction'@'localhost' IDENTIFIED BY 'auction123';
CREATE USER IF NOT EXISTS 'auction'@'127.0.0.1' IDENTIFIED BY 'auction123';
GRANT ALL PRIVILEGES ON auction.* TO 'auction'@'localhost';
GRANT ALL PRIVILEGES ON auction.* TO 'auction'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

echo "==> 4/8  Composer bağımlılıkları..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "==> 5/8  .env & uygulama anahtarı..."
[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=.\+' .env || php artisan key:generate --force

echo "==> 6/8  Migrasyon + örnek veri..."
php artisan migrate --force
php artisan db:seed --force
php artisan db:seed --class=AuctionSeeder --force || true
php artisan storage:link || true

echo "==> 7/8  Frontend (Vite) derleniyor..."
if command -v yarn >/dev/null; then yarn install && yarn build;
elif command -v npm >/dev/null; then npm install && npm run build; fi

echo "==> 8/8  Test hesapları..."
php artisan tinker --execute='
use App\Models\User; use App\Models\SellerProfile;
$s=User::updateOrCreate(["email"=>"seller@test.com"],["name"=>"Test Satici","username"=>"testsatici","password"=>bcrypt("password"),"is_verified"=>1]); $s->syncRoles(["seller"]);
SellerProfile::updateOrCreate(["user_id"=>$s->id],["company_name"=>"Test Muzayede","tax_number"=>"1234567890","iban"=>"TR000000000000000000000000","verification_status"=>"approved","verified_at"=>now()]);
$b=User::updateOrCreate(["email"=>"buyer@test.com"],["name"=>"Test Alici","username"=>"testalici","password"=>bcrypt("password"),"is_verified"=>1]); $b->syncRoles(["buyer"]);
echo "ok";
' || true

echo ""
echo "======================================================"
echo " Kurulum tamam!  Sunucuyu başlatmak için:"
echo "   php artisan serve --host=0.0.0.0 --port=8000"
echo " Giriş:  seller@test.com / buyer@test.com  (şifre: password)"
echo "======================================================"
