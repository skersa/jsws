# 📡 Canlı Yayın (WebRTC) + Gerçek Zamanlı Chat — Kurulum & Production Rehberi

Bu belge, `artirdim.com` projesine eklenen **canlı yayın (LiveKit / WebRTC SFU)** ve
**gerçek zamanlı sohbet** sisteminin nasıl kurulacağını ve production'da nasıl
çalıştırılacağını anlatır.

---

## 1) Mimarî Özet

- **Video/Ses (yayın):** [LiveKit](https://livekit.io) — WebRTC **SFU**. Satıcı kamerasını **bir kez**
  yayınlar; LiveKit görüntüyü tüm izleyicilere dağıtır (Twitch/Kick mantığı, düşük gecikme, ölçeklenir).
- **Medya yolu:** Tarayıcı ↔ LiveKit sunucusu (WSS/WebRTC). **Medya Laravel/Nginx üzerinden geçmez.**
  Bu yüzden ek FFmpeg / kendi medya sunucusu / HLS **gerekmez**.
- **Token:** Laravel `POST /livekit/token` uç noktası, sunucu tarafında kısa ömürlü (1 saat) JWT üretir.
  **API secret asla istemciye gönderilmez.**
- **Chat:** `auction_chat_messages` tablosu + `ChatController` (poll/store). Şu an **polling** (3 sn) ile
  çalışır; spam koruması vardır (2 sn aralık, 10 sn'de 5 mesaj, aynı mesaj tekrarı engeli).
  İstenirse **Laravel Reverb** ile anlık push'a yükseltilebilir (aşağıda).

### İlgili dosyalar
| Katman | Dosya |
|---|---|
| Token üretimi | `app/Http/Controllers/LiveKitTokenController.php` |
| Route | `routes/web.php` → `POST /livekit/token` (`livekit.token`) |
| Client yardımcı | `resources/js/composables/useLiveKit.js` |
| Yayıncı sayfası | `resources/js/Pages/Seller/Broadcast.vue` |
| İzleyici entegrasyonu | `resources/js/Pages/Auctions/Show.vue` (`connectViewerStream`) |
| Chat backend | `app/Http/Controllers/General/ChatController.php` |
| Config | `config/services.php` → `services.livekit` |

---

## 2) LiveKit Kurulumu (2 seçenek)

### Seçenek A — LiveKit Cloud (önerilen, en kolay)
1. https://cloud.livekit.io → proje oluştur.
2. **Project Settings → Keys** → yeni key.
3. **Project Settings → Project URL** (wss:// ile başlar).
4. `.env`'e ekle:

```dotenv
LIVEKIT_URL=wss://<projen>.livekit.cloud
LIVEKIT_API_KEY=API...
LIVEKIT_API_SECRET=<secret>
```

### Seçenek B — Kendi Sunucunda LiveKit (self-host, açık kaynak)
Ubuntu/VPS için Docker ile:
```bash
docker run -d --name livekit --restart unless-stopped \
  -p 7880:7880 -p 7881:7881 -p 50000-60000:50000-60000/udp \
  -e LIVEKIT_KEYS="APIKEY: SECRETKEY" \
  livekit/livekit-server --node-ip <SUNUCU_PUBLIC_IP>
```
Nginx ile `wss://livekit.senin-domainin.com` reverse-proxy'le (WebSocket upgrade + `/rtc`).
`.env` → `LIVEKIT_URL=wss://livekit.senin-domainin.com`, aynı KEY/SECRET.

> UDP 50000-60000 portları WebRTC medya içindir; firewall'da açık olmalı.

Kurulumdan sonra:
```bash
php artisan config:clear
```

---

## 3) Gerçek Zamanlı Chat — Production Seçenekleri

**Varsayılan (çalışır durumda):** Polling. Ek servis gerekmez, stabildir.

**İsteğe bağlı yükseltme — Laravel Reverb (anlık push):**
`ChatController@store` zaten `broadcast(new ChatMessageSent($chat))->toOthers();` yayınlıyor.
Reverb'i açmak için:

1. `.env`:
```dotenv
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=senin-domainin.com
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```
2. Reverb sunucusunu başlat (Supervisor — aşağıda).
3. Frontend'de Laravel Echo'yu Reverb'e bağlayıp `auction.{id}` kanalını dinle
   (mevcut polling'i fallback olarak bırakabilirsin).

> **Önizleme (Emergent) ortamı notu:** Bu ortamın ingress'i yalnızca 3000 ve /api(8001) portlarını
> dışarı açar; Reverb'in 8080 WS'i **dışarıdan erişilemez**. Bu yüzden önizlemede chat **polling** ile
> çalışır. Reverb yalnızca kendi production sunucunda tam çalışır. (LiveKit medyası buluta gittiği için
> yayın önizlemede de sorunsuz çalışır.)

---

## 4) Production Servisleri (Supervisor)

`/etc/supervisor/conf.d/artirdim.conf`:
```ini
[program:artirdim-queue]
command=php /var/www/artirdim/artisan queue:work --sleep=1 --tries=3 --timeout=120
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/artirdim-queue.log

; Reverb kullanacaksan:
[program:artirdim-reverb]
command=php /var/www/artirdim/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/artirdim-reverb.log
```
```bash
sudo supervisorctl reread && sudo supervisorctl update
```

**Scheduler (cron — zorunlu):** açık artırma kapanışı, sipariş otomasyonu, story temizliği için:
```bash
* * * * * cd /var/www/artirdim && php artisan schedule:run >> /dev/null 2>&1
```

**Nginx (Reverb WS reverse proxy — Reverb kullanıyorsan):**
```nginx
location /app {           # laravel-echo/reverb ws yolu
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
}
```
> Laravel uygulamasının kendisi için Nginx + PHP-FPM ayarları `KURULUM.md`'de.

---

## 5) Gereken Bağımlılıklar (özet)

| Bileşen | Zorunlu mu | Not |
|---|---|---|
| PHP 8.2 + Composer | ✅ | Laravel |
| MySQL/MariaDB | ✅ | Ana veritabanı |
| Redis | ✅ (önerilen) | cache/queue/chat throttle |
| Node.js + Vite build | ✅ | `yarn build` (frontend) |
| **LiveKit (Cloud veya self-host)** | ✅ (yayın için) | WebRTC SFU |
| Laravel Reverb | ⛔ opsiyonel | Anlık chat push; yoksa polling |
| FFmpeg / kendi media server | ⛔ gereksiz | LiveKit hallediyor |
| Queue worker + Scheduler | ✅ | `KURULUM.md` bölüm 2/f-g |

---

## 6) Test Hesapları
Şifre hepsi `password`: `admin@test.com`, `seller@test.com`, `buyer@test.com`.
Satıcı yayın: `/seller/auctions/{slug}/broadcast` → "Yayını Başlat".
İzleyici: `/auctions/{slug}` → yayın canlıysa "Canlı İzle" otomatik açılır.

## 7) Güvenlik
- `LIVEKIT_API_SECRET`, `REVERB_APP_SECRET` yalnızca `.env`'de; koda/`VITE_*`'a **asla** yazma.
- Token TTL kısa (1 sa). Yayıncı token'ı yalnızca ilan sahibi satıcıya verilir (sunucuda kontrol edilir).
- İzleyici token'ı yalnızca `canSubscribe` yetkisiyle üretilir.
