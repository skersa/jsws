# 🔄 Laravel Blade → Inertia.js + Vue 3 Dönüşüm Takibi

> Bu dosya oturum/kredi kesintisine karşı ilerlemeyi kalıcı tutar.
> Her yeniden başlarken ÖNCE bu dosyayı oku, kaldığın yerden devam et.
> Her partiden sonra `git add . && git commit` yap.

**Proje:** artirdim.com — Laravel 12 açık artırma platformu
**Hedef:** Tüm Blade view'ları Inertia.js + Vue 3'e çevir. Controller `view()` → `Inertia::render()`.
**Kurallar:** Tailwind EKLENMEYECEK. Mevcut `public/assets/css/*` ve class isimleri AYNEN korunacak. Route isimleri DEĞİŞMEYECEK (Ziggy ile çağrılacak). Form/validation/flash davranışı BİREBİR korunacak.

**Toplam Blade dosyası:** 79
- Vue Page'e çevrilecek: ~52
- Vue Component'e çevrilecek (partial/layout): ~16
- Blade olarak KALACAK (server-render): 5 → `emails/*` (3) + `errors/*` (2)

**Legend:** `[ ]` yapılmadı · `[x]` tamam · `[~]` devam ediyor

> ## ✅ TAMAMLANAN (son oturum)
> - **GRUP 0 — Altyapı: TAMAM** → Inertia + Vue 3 + Ziggy kuruldu, `HandleInertiaRequests` middleware, root `app.blade.php`, `resources/js/app.js`, `AppLayout.vue` (header+sidebar+footer), `AuthLayout.vue`, `AuctionCard.vue`, `StoryBar.vue`, `Pagination.vue`, `useClock.js`. KT (Metronic) bileşenleri her Inertia gezinmesinde yeniden başlatılıyor.
> - **GRUP 1 — Public: TAMAM** → Index, Browse/Auctions, Browse/Live, Browse/Explore, Contact, Corporate, Privacy. Controller'lar `Inertia::render`'a çevrildi (HomeController, BrowseController, PageController).
> - **GRUP 2 — Auth: TAMAM** → Login, Register (3-adım wizard), ForgotPassword, ResetPassword, ConfirmPassword, VerifyEmail, PendingApproval. Auth controller'ları + `EnsureUserIsVerified` middleware + web.php verify-email route Inertia'ya çevrildi. Login akışı test edildi (302 → dashboard ✅).
> - **GRUP 4 — Alıcı: KISMEN (5/6)** → Dashboard, Buyer/MyBids, Buyer/Favorites, Buyer/Orders/Index, Buyer/Orders/Show TAMAM (+ OrderProgress, OrderTimeline, ReviewForm bileşenleri). Controller/route'lar Inertia'ya çevrildi, curl ile component doğrulandı. **KALAN: messages/index** (harici `messages-index.js` polling içerdiğinden sonraki oturuma bırakıldı).
> - **GRUP 5 — Genel: KISMEN (4/8)** → General/Notifications, General/Support/{Index,Create,Show} TAMAM (+ FaqItem bileşeni). NotificationController + SupportController Inertia'ya çevrildi, curl ile doğrulandı. Support reply/notif read-all fetch+`router.reload` ile korundu. **KALAN: Balance (index/create/withdraw/show) — BalanceController henüz Inertia'ya çevrilmedi.**
>
> ### 🔑 ÖNEMLİ: KARMA MOD (Mixed mode) çalışıyor
> Eski `resources/views/layouts/app.blade.php` **SİLİNMEDİ**. Henüz çevrilmemiş sayfalar (GRUP 3-7) hâlâ eski Blade layout ile sorunsuz render ediliyor. Yani site şu an %100 çalışır durumda. Bir grubu bitirince ilgili controller'ı `Inertia::render`'a çevir; gerisi otomatik.
>
> ### ▶️ SONRAKİ OTURUM NASIL DEVAM EDER
> 1. `cd /app/laravel_project/project`
> 2. İlgili blade + controller + route üçlüsünü aç (tablodaki eşleme hazır)
> 3. `resources/js/Pages/...vue` oluştur (`layout: AppLayout` pattern'i — bkz. Index.vue)
> 4. Controller'da `return view(...)` → `return Inertia::render(...)`, tüm compact/with verilerini props yap
> 5. `npm run build` → `sudo supervisorctl restart laravel` → test → commit
> 6. Auction listeleri için `\$auction->toCard()` + `<AuctionCard>` kullan (hazır)

---

## 🟦 GRUP 0 — Altyapı (ÖNCE bu bitmeli)
- [ ] `composer require inertiajs/inertia-laravel tightenco/ziggy`
- [ ] `npm i @inertiajs/vue3 vue @vitejs/plugin-vue`
- [ ] `vite.config.js` → vue plugin + `resources/js/app.js` giriş noktası
- [ ] `resources/views/app.blade.php` → tek Inertia kök layout (`@inertia`, `@routes`, `@vite`)
- [ ] `app/Http/Middleware/HandleInertiaRequests.php` (auth user, flash, csrf, ziggy share)
- [ ] `bootstrap/app.php`'a Inertia middleware kaydı
- [ ] `resources/js/app.js` → `createInertiaApp` + Vue + Ziggy `ZiggyVue`
- [ ] **Layout:** `resources/js/Layouts/AppLayout.vue` ← `layouts/app` + `partials/header` + `partials/sidebar`
- [ ] **Layout:** `resources/js/Layouts/AuthLayout.vue` ← `auth/layouts/{master,header,footer}`

## 🟩 GRUP 1 — Public / Front Sayfalar (7)
| Sayfa | Blade | Controller | Route | Vue | Ctrl→Inertia | Route ✓ | Test ✓ |
|---|---|---|---|---|---|---|---|
| Ana Sayfa | `index` | `HomeController@index` | `index` | [ ] | [ ] | [ ] | [ ] |
| Müzayedeler | `browse/auctions` | `BrowseController@auctions` | `browse.auctions` | [ ] | [ ] | [ ] | [ ] |
| Canlı | `browse/live` | `BrowseController@live` | `browse.live` | [ ] | [ ] | [ ] | [ ] |
| Keşfet | `browse/explore` | `BrowseController@explore` | `browse.explore` | [ ] | [ ] | [ ] | [ ] |
| Kurumsal | `corporta` | `PageController@corporate` | `corporate` | [ ] | [ ] | [ ] | [ ] |
| Gizlilik | `privay-policy` | `PageController@privacy_policy` | `privacy` | [ ] | [ ] | [ ] | [ ] |
| İletişim | `contact` | `PageController@contact` | `contact` | [ ] | [ ] | [ ] | [ ] |

## 🟨 GRUP 2 — Auth Sayfalar (7)
| Sayfa | Blade | Controller | Route | Vue | Ctrl→Inertia | Route ✓ | Test ✓ |
|---|---|---|---|---|---|---|---|
| Giriş | `auth/login` | `AuthenticatedSessionController@create` | `login` | [ ] | [ ] | [ ] | [ ] |
| Kayıt | `auth/register` | `RegisteredUserController@create` | `register` | [ ] | [ ] | [ ] | [ ] |
| Şifremi Unuttum | `auth/forgot-password` | `PasswordResetLinkController@create` | `password.request` | [ ] | [ ] | [ ] | [ ] |
| Şifre Sıfırla | `auth/reset-password` | `NewPasswordController@create` | `password.reset` | [ ] | [ ] | [ ] | [ ] |
| Şifre Onayla | `auth/confirm-password` | `ConfirmablePasswordController@show` | `password.confirm` | [ ] | [ ] | [ ] | [ ] |
| E-posta Doğrula | `auth/verify-email` | `EmailVerificationPromptController` | `verification.notice` | [ ] | [ ] | [ ] | [ ] |
| Onay Bekliyor | `auth/pending-approval` | (verified.account middleware) | — | [ ] | [ ] | [ ] | [ ] |

## 🟧 GRUP 3 — İlan Detay & Profil (5)
> **İlan Detay: TAMAM ✅** (`Auctions/Show.vue`) — `BidController@show` → `Inertia::render('Auctions/Show')`, tam serialize props (`a`+`config`). Canlı davranış (teklif, polling live-state, sohbet, geri sayım, lightbox, satıcı kartı) mevcut `auction-show.js`/`auctions-new-config.js` AYNEN korunarak sağlandı; JS'e Inertia köprüsü eklendi (`__auctionShowInit`/`__auctionShowCleanup`, script tek sefer yüklenir, remount'ta init tekrar çağrılır → SPA yeniden-giriş çökmesi çözüldü). `auction-show.css` global head'e alındı (FOUC giderildi). Broadcast `log` driver'a çekildi + frontend Echo devre dışı (Reverb yok) → teklifte Pusher hatası giderildi. Testing agent %100 (iteration_9).
> **KALAN:** Canlı Yayın (satıcı/WebRTC).
>
> **Profil Sayfaları: TAMAM ✅** (`Profile/Show.vue` — public `show` + owner `edit` tek serializer; `Profile/FollowList.vue` — followers/following). `profile-show.js` SPA-güvenli `__profileShowInit`'e sarıldı; formlar native POST (birebir), flash/validation props ile taşınıyor; Güvenlik sekmesinde e-posta/şifre hata & başarı geri bildirimi için ilgili inline form otomatik açılıyor. Follow toggle FollowList'te native Vue fetch. Testing agent %100 (iteration_10/11).
>
> **UI düzeltmesi:** Sticky footer — çift-scroll düzeltmesinde kaldırılan yükseklik çıpası `#kt_app_root { min-height:100vh }` ile geri getirildi; kısa sayfalarda footer artık dibe yapışıyor, çift scrollbar geri gelmiyor. Testing agent %100 (iteration_12).
| Sayfa | Blade | Controller | Route | Vue | Ctrl→Inertia | Route ✓ | Test ✓ |
|---|---|---|---|---|---|---|---|
| İlan Detay | `auctionsnew` | `BidController@show` | `auctions.show` | [ ] | [ ] | [ ] | [ ] |
| Canlı Yayın (satıcı) | `auctions` | `BroadcastController@show` | `seller.auctions.broadcast` | [ ] | [ ] | [ ] | [ ] |
| Profil (public) | `profile/show` | `ProfileController@show` | `profile.public` | [ ] | [ ] | [ ] | [ ] |
| Profil Düzenle | `profile/edit` | `ProfileController@edit` | `profile.edit` | [ ] | [ ] | [ ] | [ ] |
| Takip Listesi | `profile/follow-list` | `FollowController@followers/following` | `profile.followers/following` | [ ] | [ ] | [ ] | [ ] |

## 🟪 GRUP 4 — Alıcı & Mesajlaşma (6)
| Sayfa | Blade | Controller | Route | Vue | Ctrl→Inertia | Route ✓ | Test ✓ |
|---|---|---|---|---|---|---|---|
| Dashboard | `dashboard` | (inline route) | `dashboard` | [ ] | [ ] | [ ] | [ ] |
| Tekliflerim | `buyer/my-bids` | (inline route) | `my-bids` | [ ] | [ ] | [ ] | [ ] |
| Favoriler | `buyer/favorites` | (inline route) | `favorites` | [ ] | [ ] | [ ] | [ ] |
| Siparişlerim | `buyer/orders/index` | `OrderController@index` | `orders.index` | [ ] | [ ] | [ ] | [ ] |
| Sipariş Detay | `buyer/orders/show` | `OrderController@show` | `orders.show` | [ ] | [ ] | [ ] | [ ] |
| Mesajlar | `messages/index` | `MessageController@index` | `messages.index` | [ ] | [ ] | [ ] | [ ] |

## 🟫 GRUP 5 — Bakiye / Bildirim / Destek (8)
| Sayfa | Blade | Controller | Route | Vue | Ctrl→Inertia | Route ✓ | Test ✓ |
|---|---|---|---|---|---|---|---|
| Bakiye | `general/balance/index` | `BalanceController@index` | `general.balance.index` | [ ] | [ ] | [ ] | [ ] |
| Bakiye Yükle | `general/balance/create` | `BalanceController@create` | `general.balance.create` | [ ] | [ ] | [ ] | [ ] |
| Para Çek | `general/balance/withdraw` | `BalanceController@withdrawCreate` | `general.balance.withdraw.create` | [ ] | [ ] | [ ] | [ ] |
| İşlem Detay | `general/balance/show` | `BalanceController@show` | `general.balance.show` | [ ] | [ ] | [ ] | [ ] |
| Bildirimler | `general/notifications` | `NotificationController@index` | `notifications.index` | [ ] | [ ] | [ ] | [ ] |
| Destek | `general/support/index` | `SupportController@index` | `support.index` | [ ] | [ ] | [ ] | [ ] |
| Destek Oluştur | `general/support/create` | `SupportController@create` | `support.create` | [ ] | [ ] | [ ] | [ ] |
| Destek Detay | `general/support/show` | `SupportController@show` | `support.show` | [ ] | [ ] | [ ] | [ ] |

## 🟥 GRUP 6 — Satıcı Paneli (7)
| Sayfa | Blade | Controller | Route | Vue | Ctrl→Inertia | Route ✓ | Test ✓ |
|---|---|---|---|---|---|---|---|
| Satıcı Dashboard | `seller/panel/dashboard` | `Seller\DashboardController@index` | `seller.dashboard` | [ ] | [ ] | [ ] | [ ] |
| İlanlarım | `seller/auctions/index` | `Seller\AuctionController@index` | `seller.auctions.index` | [ ] | [ ] | [ ] | [ ] |
| İlan Oluştur | `seller/auctions/create` | `Seller\AuctionController@create` | `seller.auctions.create` | [x] | [x] | [x] | [x] |
| İlan Düzenle | `seller/auctions/edit` | `Seller\AuctionController@edit` | `seller.auctions.edit` | [x] | [x] | [x] | [x] |
| İlan Göster | `seller/auctions/show` | `Seller\AuctionController@show` | `seller.auctions.show` | [x] | [x] | [x] | [x] |
| Satışlarım | `seller/sales/index` | `Seller\SaleController@index` | `seller.sales.index` | [ ] | [ ] | [ ] | [ ] |
| Satış Detay | `seller/sales/show` | `Seller\SaleController@show` | `seller.sales.show` | [ ] | [ ] | [ ] | [ ] |
| Satıcı Profil | `seller/profile/edit` | `Seller\ProfileController@edit` | `seller.profile.edit` | [ ] | [ ] | [ ] | [ ] |

## ⬛ GRUP 7 — Admin Paneli (16)
| Sayfa | Blade | Controller | Route | Vue | Ctrl→Inertia | Route ✓ | Test ✓ |
|---|---|---|---|---|---|---|---|
| Admin Dashboard | `admin/dashboard` | `Admin\DashboardController@index` | `admin.dashboard` | [x] | [x] | [x] | [x] |
| Kullanıcılar | `admin/users/index` | `Admin\UserController@index` | `admin.users.index` | [x] | [x] | [x] | [x] |
| Kullanıcı Göster | `admin/users/show` | `Admin\UserController@show` | `admin.users.show` | [x] | [x] | [x] | [x] |
| Kullanıcı Düzenle | `admin/users/edit` | `Admin\UserController@edit` | `admin.users.edit` | [x] | [x] | [x] | [x] |
| Kategoriler | `admin/categories/index` | `Admin\CategoryController@index` | `admin.categories.index` | [x] | [x] | [x] | [x] |
| Kategori Oluştur | `admin/categories/create` | `Admin\CategoryController@create` | `admin.categories.create` | [x] | [x] | [x] | [x] |
| Kategori Düzenle | `admin/categories/edit` | `Admin\CategoryController@edit` | `admin.categories.edit` | [x] | [x] | [x] | [x] |
| Kategori Göster | `admin/categories/show` | `Admin\CategoryController@show` | `admin.categories.show` | [x] | [x] | [x] | [x] |
| Admin İlanlar | `admin/auctions/index` | `Admin\AuctionController@index` | `admin.auctions.index` | [x] | [x] | [x] | [x] |
| Admin İlan Göster | `admin/auctions/show` | `Admin\AuctionController@show` | `admin.auctions.show` | [x] | [x] | [x] | [x] |
| Admin İlan Düzenle | `admin/auctions/edit` | `Admin\AuctionController@edit` | `admin.auctions.edit` | [x] | [x] | [x] | [x] |
| Admin Siparişler | `admin/orders/index` | `Admin\OrderController@index` | `admin.orders.index` | [x] | [x] | [x] | [x] |
| Admin Sipariş Detay | `admin/orders/show` | `Admin\OrderController@show` | `admin.orders.show` | [x] | [x] | [x] | [x] |
| Admin Destek | `admin/support/index` | `Admin\SupportController@index` | `admin.support.index` | [x] | [x] | [x] | [x] |
| Admin Destek Detay | `admin/support/show` | `Admin\SupportController@show` | `admin.support.show` | [x] | [x] | [x] | [x] |
| Ayarlar | `admin/settings/index` | `Admin\SettingsController@index` | `admin.settings.index` | [x] | [x] | [x] | [x] |

## 🧩 Ortak Component'ler (sayfalarla birlikte yapılacak)
- [ ] `partials/stars` → `Components/Stars.vue`
- [ ] `partials/order-progress` → `Components/OrderProgress.vue`
- [ ] `partials/order-timeline` → `Components/OrderTimeline.vue`
- [ ] `partials/review-form` → `Components/ReviewForm.vue`
- [ ] `partials/story-bar` → `Components/StoryBar.vue`
- [x] `partials/story-viewer` → `Components/StoryViewer.vue`
- [x] `partials/story-upload` → `Components/StoryUpload.vue`
- [ ] `partials/profile-stories` → `Components/ProfileStories.vue`
- [ ] `partials/category-select-options` → `Components/CategorySelectOptions.vue`
- [ ] `browse/card` → `Components/AuctionCard.vue`

## 📭 Blade olarak KALACAK (dönüştürülmeyecek)
- `emails/contact`, `emails/reset-password`, `emails/verify-custom` (mail render)
- `errors/404`, `errors/maintenance` (Laravel error render)
- `auctions.blade.php` yerine geçen `old-live.blade.php` → LEGACY (kullanılmıyorsa dokunma)

---

## 🐞 Bilinen sorunlar / notlar
- Reverb (WebSocket) canlı yayın var; Inertia'ya geçerken Echo entegrasyonu component `onMounted`'a taşınacak.
- Bazı sayfalar AJAX polling kullanıyor (chat, live-state, messages poll) — bunlar Inertia partial reload veya mevcut fetch mantığıyla korunacak.
- `public/assets/js/custom/*` içindeki mevcut JS davranışları component'lere taşınacak, silinmeyecek.

## ✅ Genel İlerleme
- [x] GRUP 0 · [x] GRUP 1 · [x] GRUP 2 · [~] GRUP 3 (profil/ilan detay tamam; canlı yayın/WebRTC KALAN) · [x] GRUP 4 (6/6) · [x] GRUP 5 (8/8) · [x] GRUP 6 (8/8) · [x] GRUP 7 (16/16)

## 🟢 Aşama 2 (2026-08, oturum devamı) — TAMAM olanlar
- **GRUP 4 — Mesajlar TAMAM ✅** (`Messages/Index.vue`, `MessageController@index`→Inertia). Konuşma listesi + aktif sohbet + AJAX mesaj gönderme (`store` JSON korundu) + 4sn polling (`poll` JSON). Polling `onMounted`'da başlar, `onBeforeUnmount`'ta `clearInterval` ile temizlenir. `store/poll/start` controller metotları AYNEN korundu. Testing agent %100 (iteration_13).
- **GRUP 5 — Bakiye TAMAM ✅** (`General/Balance/{Index,Create,Withdraw,Show}.vue`, `BalanceController` index/create/withdrawCreate/show→Inertia; `store`/`withdraw` redirect+flash korundu). Create: preset butonları, ödeme yöntemi toggle (kart/havale/papara), kart no/SKT formatlama, useForm validation. Withdraw: preset + IBAN. Show: kopyala butonu. `balance-create.css` + `balance-show.css` global head'e eklendi. Testing agent %100 (iteration_13).
- **GRUP 6 — Satıcı (kısmen 3/8):**
  - **Satışlarım index TAMAM ✅** (`Seller/Sales/Index.vue`, `SaleController@index`→Inertia). Sipariş tablosu + Pagination.
  - **Satış detay TAMAM ✅** (`Seller/Sales/Show.vue`, `SaleController@show`→Inertia). OrderProgress/OrderTimeline bileşenleri kullanıldı. Kargoya ver formu (useForm→`ship` redirect+flash korundu). NOT: carrier select2 → native `<select>` (SPA'da select2 re-init kırılganlığından kaçınmak için; işlevsel birebir).
  - **İlan Yönetimi index TAMAM ✅** (`Seller/Auctions/Index.vue`, `AuctionController@index`→Inertia). İstatistik kartları + arama (router.get) + tablo + Swal onaylı silme (`router.delete`, destroy controller AYNEN) + özel pagination. NOT: "Toplam" kartı eskiden yanlışlıkla `admin.auctions.index`'e (satıcıya 403) linkliydi → `seller.auctions.index`'e düzeltildi.
  - **KALAN GRUP 6:** Satıcı Dashboard (ApexCharts + canlı kartlar), İlan create/edit/show (görsel upload sihirbazı), Satıcı Profil edit (çok bölümlü form + belge upload).
- **KALAN GRUP 3:** Satıcı Canlı Yayın / WebRTC (`BroadcastController@show`→`auctions.blade.php`, Reverb WebSocket bağımlı).
- **KALAN GRUP 7:** Admin paneli (16 sayfa).

## ✅ Genel İlerleme (özet — eski satır)

---

## 🩹 Bug Fix Turu (2026-08 · oturum devamı) — TAMAM
Aşama 1'deki 3 bilinen bug düzeltildi ve testing agent ile e2e doğrulandı (%100, 3/3):
- **1A — Header arama placeholder + scroll:** `#mhdr-input` placeholder artık görünür (theme-new.css'e `.mhdr-search-wrap .search-input::placeholder { color: var(--muted); opacity:1 }` eklendi). Yatay scroll yok. Index.vue'daki `#no-results` inline `style="display:block"` kaldırıldı → `.idx-noresult-visible` class'ı.
- **1B — Login:** `email`/`password` inputlarına `required` eklendi (Blade ile birebir). Tüm elemanlar + remember + giriş akışı doğrulandı.
- **1C — Register wizard:** `Register.vue`'ye adım-bazlı client validation eklendi (`validateStep1`/`validateStep2` + `err()`), `goStep1Next`/`goStep2Next` artık zorunlu alanlar geçerli olmadan adım atlamıyor. Sunucu kurallarıyla (RegisteredUserController) birebir: username regex/3-30, email, phone zorunlu; satıcı için tax_number, IBAN 26-34, id_document zorunlu.

**Bilgi notu (bug değil):** header live-search sadece kullanıcı döndürüyor (placeholder ilan/müzayede de vaat ediyor). İleride opsiyonel iyileştirme.

## 🩹 UI Düzeltmeleri Turu 2 (kullanıcı geri bildirimi) — TAMAM (testing agent %100)
- **Çift scroll (tüm sayfalar):** `auth.css`'teki global `body{overflow:hidden}` ve `#kt_app_root{height:100vh}` kuralları sadece `body.auth-page`'e scope'landı (AuthLayout onMounted/onUnmounted ile body class'ı ekliyor/kaldırıyor).
- **Scrollbar rengi:** belirgin mavi (`var(--primary)`) yerine soft/şeffafa yakın gri (`rgba(128,128,128,0.22)`, track transparent, 6px) — theme-new.css + Firefox `scrollbar-color`.
- **Auth mobil scroll:** mobilde `#kt_app_root` artık `height:auto; min-height:100dvh` (100vh mobil adres-çubuğu taşması giderildi) → login mobilde scroll çıkmıyor.
- **Register rol seçimi:** radio input kaldırıldığı için çalışmayan `.role-radio:checked` stili yerine `.role-card.selected` eklendi; seçim artık belirgin (mavi border/arka plan/ikon/yazı).
- **Mobil sidebar:** Inertia gezinmesinde (`router.on('start')`) KT drawer otomatik kapanıyor (`window.KTDrawer.getInstance('#kt_app_sidebar').hide()`).


## 🩹 Bug Fix Turu (Aşama 2 — kullanıcı geri bildirimi) — TAMAM (testing agent %100, iteration_15)
- **Swal footer/tema bozulması:** `body.swal2-shown #kt_app_footer { position:fixed }` → `position:static` ve `body.swal2-shown { height:100% }` kaldırıldı (theme-new.css). Artık herhangi bir SweetAlert (silme onayı, toast) açılınca footer alta sabitlenip içeriğin üstüne binmiyor, tema bozulmuyor.
- **Login sonrası tema:** `AppLayout.vue onMounted` artık `body.auth-page` sınıfını temizliyor → login/kayıttan çıkışta kalan `overflow:hidden` giderildi, dashboard normal kaydırılıyor.
- **Mobil ilan detay:** `Auctions/Show.vue boot()` başında `window.scrollTo(0,0)` → mobilde ilan detayı artık en üstten (başlık+galeri) açılıyor, açıklamaya kaymıyor.
- **NOT (bug değil):** Satıcı/Admin girişte hâlâ ESKİ Blade dashboard'a gidiyor ("doğrudan yönlendiriyor" algısı) çünkü Satıcı Dashboard (GRUP 6) ve Admin Dashboard (GRUP 7) henüz Vue'ya çevrilmedi. Karma modda çalışıyor; sıradaki iş bu dashboard'ların dönüşümü.

## 🟥 GRUP 6 — Satıcı Dashboard TAMAM ✅ (testing agent %100, iteration_16)
- `Seller/Dashboard.vue` + `Seller\DashboardController@index`→Inertia. Satıcı girişte artık ESKİ Blade yerine SPA dashboard açılıyor (kullanıcının "doğrudan eski sayfaya yönlendiriyor" sorunu çözüldü).
- Chart.js CDN'den `onMounted`'ta yükleniyor, `onBeforeUnmount`'ta destroy. (Eski `seller-dashboard.js` Blade-şablonluydu ve statik serve edildiği için grafik hiç çalışmıyordu — Vue'da düzgün çalışıyor.)
- Tüm kartlar (4 stat + 3 mini + cüzdan + grafik + en çok teklif + aktivite + son ilanlar + hızlı işlemler) taşındı. Cüzdan "Para Çek"/"Geçmiş" ve hızlı işlem linkleri gerçek route'lara bağlandı (eskiden `#`).
- `seller-live-card.css` global head'e eklendi. `recentActivities` metni controller'da `e()` ile escape'li (v-html güvenli).
- **GRUP 6 KALAN (4):** İlan create/edit/show (görsel upload sihirbazı), Satıcı Profil edit.

## 🟥 GRUP 6 — Satıcı Profil Düzenleme TAMAM ✅ (testing agent %100, iteration_18)
- `Seller/Profile/Edit.vue` + `Seller\ProfileController` edit→Inertia. 4 sekme (Kişisel/Şirket/Ödeme/Belge), her bölüm ayrı `useForm` PUT, belge yükleme ayrı POST (multipart). Sekme + üstteki "Kaydet" aktif sekmeyi gönderir.
- Flash: `success_<section>` yerine `success` + `profile_section` (HandleInertiaRequests'e `profile_section` eklendi); aktif sekme flash'tan seçiliyor.
- IBAN: input "TR" öneki olmadan gösterilir, submit'te `TR`+24 hane olarak gönderilir (eski Blade'de bu birleştirme yoktu → kaydetme aslında bozuktu, düzeltildi). `onIbanInput` DOM'u zorla senkronlar (fazla karakter takılması giderildi). Mask yalnız geçerli `^TR\d{24}$` için gösterilir.
- **Bonus fix (kritik, pre-existing):** `EnsureUserIsVerified` middleware `: Response` dönüş tipi `Inertia\Response` ile uyumsuzdu → pending satıcı TÜM /seller/* rotalarında 500 alıyordu (belge yükleyince tetikleniyordu). Dönüş tipi kaldırıldı; artık PendingApproval ekranı düzgün render ediliyor.
- **GRUP 6 KALAN (3):** İlan create/edit/show (görsel upload sihirbazı).


## 🟥 GRUP 6 — İlan Oluştur (Create) TAMAM ✅ (2026-08, e2e doğrulandı)
**Referans Blade:** `resources/views/seller/auctions/create.blade.php` · **Vue:** `resources/js/Pages/Seller/Auctions/Create.vue` · **Controller:** `Seller\AuctionController@create/@store` (DEĞİŞMEDİ — birebir korundu).

- **Ana eksik giderildi — Select2:** Blade'deki `.js-select2` kategori alanı (arama + placeholder "Kategori seçin" + allow-clear + hiyerarşik "— " girintili seçenekler) Vue'da düz `<select>` ile geçiştirilmişti. Artık yeni **`resources/js/Components/Select2.vue`** SPA-güvenli sarmalayıcı ile birebir sağlanıyor.
  - Config `public/assets/js/custom/app-init.js` ile BİREBİR: `width:100%`, `dropdownParent`=kapsayan form, `placeholder`=data-placeholder, `allowClear`=data-allow-clear.
  - jQuery + select2 global (`plugins.bundle.js`) kullanılıyor; select2 CSS zaten `theme-new.css`'te mevcut (görsel birebir).
  - SPA güvenliği: `onMounted`'ta init, çift-init koruması (`$el.data('select2')`), `onBeforeUnmount`'ta `select2('destroy')` + `off('change')`. `v-model` iki yönlü (change→emit, programatik değişim→`change.select2`). Dinamik `options` watch'lı (ileride edit/AJAX için hazır).
- **Parite bug fix (birebir):** `condition` varsayılanı Vue'da yanlışlıkla `'used'` idi; Blade'de hiç `selected` olmadığından tarayıcı ilk seçeneği (`new` = "Sıfır") seçer → Vue varsayılanı **`'new'`** yapıldı. Artık dokunmadan gönderince Blade ile aynı değer gidiyor.
- **Doğrulanan (e2e, Playwright, preview URL):**
  - Sayfa açılıyor, tüm alanlar Blade ile aynı (görseller dropzone + preview/kapak, başlık, açıklama, kategori Select2, ürün durumu, konum, fiyatlar, tarihler, hızlı tarih butonları).
  - Select2: dropdown açılıyor, arama ("a"→Antika/Sanat/Mücevherat) çalışıyor, seçim tutuluyor, clear (×) görünüyor, seçim `category_id`'ye bağlanıyor.
  - Validation: boş submit → "5 hata var" + alan-bazlı mesajlar (`description/starting_price/starts_at...`).
  - Tam geçerli submit (görsel yükleme + Select2 kategori + gelecek tarih) → `seller.auctions.index`'e yönlendi, "İlanın yayına alındı! 🎉" flash'ı, kayıt listede (kategori "Antika") göründü. Test kaydı sonrası silindi.
  - NOT (pre-existing, backend birebir): `starts_at` varsayılanı `now()` + kural `after_or_equal:now` → varsayılan tarihle submit saniye farkından reddedilebilir. Bu davranış Blade'de de aynıdır; backend değiştirilmedi (kural 7).
- **Git (kural 8):** `laravel_project` ayrı repo/submodule DEĞİL (nested `.git` yok, gitlink `160000` girdisi yok) — dosyalar ana repoda. Değişenler: `Create.vue` (M, tracked), `Components/Select2.vue` (yeni, eklenecek).
- **GRUP 6 KALAN (2):** İlan **Düzenle (edit)** — aynı Select2 bileşeni ile mevcut değer seçili gelecek şekilde uygulanacak · İlan **Göster (show)**.

## 🩹 Bug Fix Turu (kullanıcı geri bildirimi) — TAMAM ✅ (testing agent %100, iteration_1 · frontend)
Kullanıcının bildirdiği 3 hata giderildi ve testing agent ile SPA senaryosunda doğrulandı:

- **1) Hikayeler açılmıyor / "Hikaye Ekle" çalışmıyor (KÖK NEDEN + KALICI ÇÖZÜM):**
  - Kök neden: Story viewer + upload DOM'u ve `story-viewer.js`/`story-upload.js` scriptleri **Inertia root `app.blade.php` içinde `@auth` bloğundaydı**. Uygulama guest `/login` ile tam sayfa açılıp SPA ile giriş yapılınca root layout yeniden render EDİLMEDİĞİ için bu DOM/scriptler hiç yüklenmiyor, `window.openStoryViewer/openStoryUpload` tanımsız kalıyordu → hikaye açılmıyor, ekleme çalışmıyordu. (Tam sayfa yenileme hatayı maskeliyordu.)
  - Çözüm: story viewer & upload **Vue bileşenlerine taşındı** → `resources/js/Components/StoryViewer.vue` + `StoryUpload.vue`. `AppLayout.vue` sonuna eklendi (`<StoryViewer v-if="user"/>`, `<StoryUpload v-if="user.is_seller"/>`). `Teleport to="body"` ile eski Blade konumu (body altı) birebir korundu; aynı id/class'lar (theme-new.css / story-upload.css stilleri aynen geçerli). `window.openStoryViewer/closeStoryViewer/storyNext/storyPrev/deleteCurrentStory` ve `openStoryUpload/closeStoryUpload` global fonksiyonları `onMounted`'ta kaydedilir (StoryBar.vue + Profile/Show.vue değişmeden çalışır). Seen-state (localStorage) boyaması, klavye kısayolları, 5sn otomatik ilerleme, video/görsel ayrımı, silme (Swal onaylı AJAX DELETE), yükleme (AJAX POST /stories + STORY_DATA güncelleme) davranışları JS'ten birebir portlandı.
  - `app.blade.php`'deki eski `@auth` story bloğu + script include'ları KALDIRILDI (tam sayfa yüklemede çift id/script çakışmasını önlemek için).
- **2) İlan Düzenleme'de Select2 yoktu:** `Edit.vue` kategori alanı düz `<select>` → yeniden kullanılabilir `<Select2>` bileşeni. Mevcut kategori değeri seçili gelir (ör. "Antika"), arama/seç/temizle çalışır (Create ile aynı bileşen).
- **3) İlanlarım (index) 'ilan ekleme' butonu yoktu:** `Index.vue` toolbar sağ üste **"Yeni İlan"** butonu (`data-testid=seller-create-auction-btn`) + boş durum ekranına **"Yeni İlan Oluştur"** (`data-testid=seller-empty-create-btn`) eklendi; `seller.auctions.create`'e gider.

**Test:** testing agent frontend %100 (iteration_1) — SPA gezinme yolunda (guest→login→dashboard→Ana Sayfa link) hikaye görüntüleyici açılıyor, "Hikaye Ekle" modalı açılıp dosya seçince Paylaş aktifleşiyor; index "Yeni İlan" butonu create'e gidiyor; Edit & Create Select2 (ön-seçim/arama/seçim/temizle) çalışıyor.

**GRUP 6 KALAN (1):** İlan **Göster (show)** — Select2/görsel gerektirmez, sonraki turda Blade ile karşılaştırılacak.

## 🟥 GRUP 6 — İlan Göster (Show) TAMAM ✅ (testing agent %100, iteration_2) → GRUP 6 BİTTİ (8/8)
**Referans Blade:** `resources/views/seller/auctions/show.blade.php` · **Vue:** `resources/js/Pages/Seller/Auctions/Show.vue` · **Controller:** `Seller\AuctionController@show` (Inertia, DEĞİŞMEDİ).
- Show.vue zaten büyük ölçüde birebir dönüştürülmüştü (toolbar+Düzenle/Kaldır, Yayın Yönetimi kartı, görsel galerisi+thumb switch, Açıklama, Özet, Detaylar, Son Teklifler). Blade ile satır satır karşılaştırıldı; tüm alanlar/eylemler eşleşiyor.
- **Parite fix (tespit + düzeltildi):** `BroadcastController@streamSettings` başarı mesajını `session('profile_success')` ile flash'lıyor. Blade bunu gösteriyordu; Show.vue ise `flash.success` okuyordu → yayın ayarı kaydedilince başarı mesajı GÖRÜNMÜYORDU. Çözüm: `HandleInertiaRequests` flash paylaşımına `profile_success` eklendi; Show.vue alert'i `flash.profile_success || flash.success` okuyor. (Backend controller davranışı değişmedi.)
- **Test (testing agent %100):** SHOW render (tüm kartlar/Son Teklifler), "Yayın ayarların güncellendi." başarı mesajı GÖRÜNÜYOR, yayın türü toggle (Canlı↔Video: video URL alanı + Canlı butonu disabled + ipucu), silme SweetAlert onayı, galeri thumb switch — hepsi geçti.
- **NOT (öneri, bug değil):** Seed'de satıcının active/draft ilanı yok; testing agent toggle testi için id=1'i geçici 'active' yapıp geri aldı. İleride AuctionSeeder'a en az 1 active + 1 draft seller ilanı eklenebilir.

## ✅ GRUP 6 SATICI PANELİ TAMAMEN BİTTİ (8/8): Dashboard, İlanlarım(index), İlan Oluştur, İlan Düzenle, İlan Göster, Satışlarım(index), Satış Detay, Satıcı Profil.

## ⬛ GRUP 7 — Admin Kategori Yönetimi index TAMAM ✅ (testing agent %100, iteration_3) [GRUP 7 başladı: 1/16]
**Referans Blade:** `resources/views/admin/categories/index.blade.php` · **Vue:** `resources/js/Pages/Admin/Categories/Index.vue` · **Controller:** `Admin\CategoryController@index` → `Inertia::render('Admin/Categories/Index')` (query/filtre mantığı AYNEN korundu).
- Birebir: toolbar (başlık+breadcrumb+"Yeni Kategori"), 5 istatistik kartı (Toplam col-xl-12, diğerleri col-xl-3), filtre formu (arama/durum/tür/Filtrele/Temizle), tablo (görsel+ad+slug, üst kategori rozeti, ilan/alt sayısı, sıra, durum rozeti, işlemler: detay/düzenle/toggle/sil), pagination.
- **MIXED MODE:** create/edit/show hâlâ Blade → bu linkler düz `<a href>` (tam sayfa geçiş, Inertia hatası yok, testing agent 200 OK doğruladı). Filtre `router.get` (SPA). Toggle `router.post` (flash `category_success`). Sil: destroy ajax'ta JSON döndüğü için `fetch` + `router.reload({only:['categories','stats']})` + Swal onay.
- `HandleInertiaRequests` flash paylaşımına **`category_success`** eklendi (toggle/sil başarı mesajı).
- **Fix (LOW, test agent notu):** `q/status/type` local ref'leri `watch(() => props.filters)` ile senkronlandı → "Temizle" sonrası select'lerde stale değer kalmıyor.
- **Test (testing agent %100):** render, filtreler (status/type/q + Temizle), toggle+flash+rozet, silme SweetAlert onay/iptal, mixed-mode create/show/edit Blade geçişleri.

**GRUP 7 KALAN (15):** categories create/edit/show (Select2 parent + görsel upload), Dashboard, Users(index/show/edit), Auctions(index/show/edit), Orders(index/show), Support(index/show), Settings.

## ⬛ GRUP 7 — Admin Kategori create/edit/show TAMAM ✅ (testing agent %100, iteration_4) [GRUP 7: 4/16 · Kategori grubu BİTTİ]
**Kullanıcı bildirimi (2 hata) çözüldü:**
- **(1) Üst Kategori Select2'ye üst kategoriler gelmiyordu:** create/edit hâlâ Blade'di ve Blade Select2 parent'ları render etmiyordu. Çözüm: create/edit Inertia/Vue'ya çevrildi; parent alanı `<Select2>` bileşeni + controller `parentOptions()` (hiyerarşik "— " girintili, edit'te self+alt hariç). Artık üst kategoriler listeleniyor, arama/seçim/temizle çalışıyor.
- **(2) "Tüm linkler sayfayı yeniliyor":** create/edit/show Blade olduğu için index'ten geçişler tam sayfa reload'du. Çözüm: 3 sayfa da Inertia'ya çevrildi; Index.vue'daki create/detay/düzenle linkleri Inertia `<Link>` yapıldı → artık SPA (reload yok, testing agent `window.__m` işaretiyle doğruladı).
- Yeni Vue: `Admin/Categories/Create.vue`, `Edit.vue`, `Show.vue` (3 sekmeli: Bilgiler/Alt Kategoriler/İşlemler). Controller `create/edit/show` → `Inertia::render` (store/update/destroy/toggle DEĞİŞMEDİ). Görsel upload forceFormData, edit `_method=put`, toggle router.post (flash `category_success`), sil fetch+Swal onay.
- **Test (testing agent %100):** parent Select2 (4 kök + arama/seç/clear), SPA navigasyon, create kaydet+flash+listede görünme, edit ön-dolum+güncelle, show 3 sekme + toggle flash + silme SweetAlert onay/iptal. Test kaydı temizlendi (son durum = 4 kök kategori aktif).
- Cosmetic öneriler (fonksiyonel hata değil, ertelendi): Swal `heightAuto` konsol uyarısı; hata gösteriminde üst özet + inline birlikte.

**GRUP 7 KALAN (12):** Dashboard, Users(index/show/edit), Auctions(index/show/edit), Orders(index/show), Support(index/show), Settings.

### 🩹 Bug fix (kullanıcı: "görsel/ayarlar sekmesi ve düzenleme bomboş") — TAMAM ✅ (testing agent %100, iteration_5)
Kök neden: Create/Edit sekme panelleri `.pf-epanel` CSS `.active` ile gösteriliyor (`.pf-epanel{display:none}` / `.pf-epanel.active{display:block}`); ben yanlışlıkla `v-show` kullanmıştım → `v-show` true olunca inline display kaldırılıp temel `display:none` devrede kalıyor, paneller boş görünüyordu (Create'te sadece Genel'e `active` verdiğim için o görünüyordu; Edit tamamen boştu). Düzeltme: Create.vue + Edit.vue'daki 6 panelde `v-show` kaldırıldı, `:class="{ active: tab===... }"` kullanıldı (Blade ile birebir). testing agent doğruladı: her iki sayfada 3 sekme de dolu, kayıt/güncelle akışları sağlam. Stray 'test kategori' seed kaydı temizlendi (4 kök kategori).

## ⬛ GRUP 7 — Admin Dashboard TAMAM ✅ (testing agent %100, iteration_6) [GRUP 7: 5/16]
**Blade:** `admin/dashboard.blade.php` → **Vue:** `Pages/Admin/Dashboard.vue` · **Controller:** `Admin\DashboardController@index` → `Inertia::render` (tüm sorgu mantığı korundu; recentOrders/topSellers/activities Vue-dostu dizilere map edildi, sayı/tarih formatları Vue'da Intl+server translatedFormat ile). `app.blade.php`'ye `admin-dashboard.css` eklendi (adm-* stilleri).
- Birebir: hero, 4 ana stat + 4 mini stat, 14 çubuklu grafik (yükseklik hesabı birebir portlandı), sipariş durum dağılımı, hızlı işlemler, son siparişler/en iyi satıcılar/son aktiviteler.
- Hızlı işlem linkleri: Kategoriler Inertia `<Link>` (SPA); users/auctions/orders hâlâ Blade → düz `<a>` (mixed mode, tam sayfa normal).
- Admin login artık doğrudan Inertia dashboard'a düşüyor; sidebar Dashboard↔Kategoriler SPA (testing agent `window.__d` ile doğruladı). Read-only sayfa (mutasyon yok).

**GRUP 7 KALAN (11):** Users(index/show/edit), Auctions(index/show/edit), Orders(index/show), Support(index/show), Settings.

## ⬛ GRUP 7 — Admin Kullanıcılar index/show/edit TAMAM ✅ (testing agent %100, iteration_7) [GRUP 7: 8/16 · Users grubu BİTTİ]
**Blade:** admin/users/{index,show,edit}.blade.php → **Vue:** Pages/Admin/Users/{Index,Show,Edit}.vue · **Controller:** UserController index/show/edit → Inertia::render (update/verify/unverify/destroy DEĞİŞMEDİ).
- Index: 5 stat, filtre (q/role/verified + watch senkron), rol rozetleri, verify toggle (router.post+flash.success), sil (fetch+Swal+router.reload), kendi hesabında sil yok, pagination, SPA <Link>.
- Show: hero + 4 stat + 4 sekme (Bilgiler/İlanlar/Teklifler/İşlemler; :class active), verify toggle + düzenle + sil.
- Edit: 3 sekme (Genel/Güvenlik/Rol&Durum) tek useForm, _method=put, avatar preview, şifre güç barları + göster/gizle, rol select (mevcut seçili), doğrulama toggle.
- Test (testing agent %100): render, filtreler+temizle, SPA (filter/Detay/Düzenle), verify toggle flash+rozet, sil onay/iptal (+ self-guard), edit ön-dolum + kaydet→show+flash. Değişiklikler geri alındı.
- Not (birebir korundu): update() username/bio KAYDETMİYOR (mevcut backend davranışı).

**GRUP 7 KALAN (8):** Auctions(index/show/edit), Orders(index/show), Support(index/show), Settings.

### ➕ GRUP 7 — Admin "Kullanıcı Ekle" (create/store) EKLENDİ ✅ (testing agent %100, iteration_8+9)
Kullanıcı isteği: Users index'te "Kullanıcı Ekle" butonu yoktu (Blade'de + backend'de create/store rotası da yoktu). Eklendi:
- Rotalar: `admin.users.create` (GET) + `admin.users.store` (POST) — `{user}` wildcard'tan ÖNCE tanımlandı.
- `UserController@create` (Inertia) + `@store` (mevcut update() deseniyle birebir: `Hash::make`, `Rules\Password::defaults()`, `syncRoles`, `is_verified`). integration_expert deseniyle uyumlu güvenli hash.
- `Pages/Admin/Users/Create.vue` (2 sekme: Genel + Rol & Şifre) + Index.vue toolbar'a "Kullanıcı Ekle" butonu (`admin-user-create-btn`).
- **Bug fix (kullanıcı bildirdi — SQLSTATE 1364 'username' has no default):** `users.username` NOT NULL/defaultsuz; store() username set etmiyordu. Düzeltildi: username formdan (opsiyonel, `Str::slug`) ya da e-postadan `generateUsername()` ile benzersiz otomatik üretilir; validation'a `username nullable|unique`; Create formuna opsiyonel Kullanıcı Adı alanı.
- Test (testing agent %100, iter_9): username BOŞ→otomatik, MANUEL→slug, başarılı oluşturma→show+flash, listede görünme; QA kayıtları silindi.

## ⬛ GRUP 7 — Admin Müzayede (Auctions) index TAMAM ✅ (testing agent %100, iteration_10) [GRUP 7: 9/16]
**Blade:** admin/auctions/index.blade.php → **Vue:** Pages/Admin/Auctions/Index.vue · **Controller:** AuctionController@index → Inertia::render (show/edit/update/approve/reject/destroy DEĞİŞMEDİ).
- Birebir: 5 tıklanabilir stat kartı (durum filtresi), arama+durum filtre+temizle, tablo (kapak/başlık/kategori-konum, satıcı, başlangıç fiyatı, durum rozeti, tarih), pagination.
- İşlemler: İncele/Düzenle düz <a> (show/edit hâlâ Blade — mixed mode), draft'ta Onayla (router.post) + Reddet (Swal gerekçe input → router.post), Sil (fetch+Swal+router.reload; destroy ajax'ta JSON).
- Test (testing agent %100): render, stat-kart filtreleri, arama/durum/temizle, silme onay/iptal, mixed-mode İncele/Düzenle 200. Onayla/Reddet seed'de draft olmadığından test edilemedi (not).

**GRUP 7 KALAN (7):** Auctions(show/edit), Orders(index/show), Support(index/show), Settings.

## ⬛ GRUP 7 — Admin Müzayede show + edit TAMAM ✅ (testing agent %100, iteration_11) [GRUP 7: 11/16 · Auctions grubu BİTTİ]
**Blade:** admin/auctions/{show,edit}.blade.php → **Vue:** Pages/Admin/Auctions/{Show,Edit}.vue · **Controller:** show/edit → Inertia::render (update DEĞİŞMEDİ, _method=put). Index'te İncele/Düzenle artık SPA <Link>.
- Show: galeri+thumb switch, Özet (4 stat + durum rozeti), Satıcı kartı, 9 satır Detaylar, Son Teklifler; draft'ta Onayla/Reddet, Düzenle/Sil.
- Edit: 3 kart (Ürün/Fiyat/Zamanlama), tüm alanlar ön-dolu, kategori DÜZ select (Blade birebir), validation (boş başlık → hata), kaydet→show+flash.
- Test (testing agent %100): SPA nav, show render (tüm kartlar), silme onay/iptal, edit ön-dolum + kaydet+flash + revert, validation. Seed geri alındı.

**GRUP 7 KALAN (5):** Orders(index/show), Support(index/show), Settings.

## ⬛ GRUP 7 — Orders + Support + Settings TAMAM ✅ (testing agent %100, iteration_1) → GRUP 7 BİTTİ (16/16)
**Ortam yeniden kuruldu:** GitHub'dan klonlandı (`/app/laravel_project/project`), PHP 8.2 + Composer + MariaDB + Redis kuruldu, `composer install` + `migrate --seed` + `yarn build`, Laravel supervisor ile port 3000'de canlı.
- **Admin Siparişler index/show** (`Admin/Orders/{Index,Show}.vue`, `Admin\OrderController` index/show → Inertia; resolve DEĞİŞMEDİ). Index: 4 sekme (Tümü/Anlaşmazlık/Devam Eden/Tamamlanan) SPA Link + tablo + pagination. Show: OrderProgress + OrderTimeline bileşenleri, anlaşmazlık çözüm kutusu (confirm → router.post resolve), ürün kartı.
- **Admin Destek index/show** (`Admin/Support/{Index,Show}.vue`, `Admin\SupportController` index/show → Inertia; reply JSON + updateStatus PATCH DEĞİŞMEDİ). Index: 4 stat + filtre (q/status/priority) + tablo. Show: sohbet balonları, durum select (Inertia PATCH), yanıt formu (AJAX fetch → balon ekle, reload yok). `admin-support-index.css` global head'e eklendi.
- **Admin Ayarlar** (`Admin/Settings/Index.vue`, `Admin\SettingsController@index` → Inertia; update/cache/testMail DEĞİŞMEDİ). 9 sekme (genel/seo/kvkk/gizlilik/kullanim/iletisim/sosyal/odeme/bakim), bölüm bazlı useForm, logo upload (forceFormData+_method=PUT), zengin metin editörleri (contenteditable+execCommand), test e-postası (Swal+fetch), cache aksiyonları (router.post). `HandleInertiaRequests`'e `settings_success`/`settings_error` flash eklendi.

## 🩹 Bug Fix Turu (kullanıcı geri bildirimi) — TAMAM ✅ (testing agent %100, iteration_2)
- **(1) Destek mesaj balonları bozuk görünüyordu:** theme-new.css'te iki `.msg-bubble` tanımı (admin destek + özel sohbet) çakışıyordu; sohbet bloğu admin balonunu eziyordu. `public/assets/css/admin-fixes.css` (theme-new.css'ten SONRA yüklenir) `#msg-list .msg-bubble` altında admin destek stilini geri getirdi.
- **(2a) Admin İlan Düzenle kategori Select2 oldu:** düz `<select>` → `<Select2>` bileşeni (arama + ön-seçim + temizle).
- **(2b) Ürün Durumu / native select dark mode:** `admin-fixes.css`'e `select ... option { background: var(--card); color: var(--text) }` eklendi → açılan seçenekler her iki modda okunur.
- **(3) Admin İlan Göster başlık ikonu:** `toolbar-title`'a `<i class="bi bi-box-seam">` eklendi.

## ▶️ KALAN (tek grup)
- **GRUP 3 — Satıcı Canlı Yayın / WebRTC** (`BroadcastController@show` → `auctions.blade.php`, Reverb WebSocket bağımlı).

## 🩹 Bug Fix Turu 2 (kullanıcı geri bildirimi) — TAMAM ✅ (testing agent %100, iteration_3)
- **Dark mode native select okunabilirliği (KÖK ÇÖZÜM):** İlk düzeltmede `option` arkaplanı `var(--card)` idi; dark modda `--card` YARI SAYDAM (rgba) olduğundan native açılır listede beyaz görünüyordu. Çözüm: `admin-fixes.css`'te `select{color-scheme:dark}` + `html.light-mode select{color-scheme:light}` + `option{background:var(--bg);color:var(--text)}` (var(--bg) her iki modda da SOLID: #0b0d14 / #f4f6fb). Tüm admin CRUD selectlerinde (auction edit condition/status, support filtre, settings) seçenekler artık okunur.
- **Admin İlan Göster derli toplu + ikon:** başlık artık kart tarzı header (`.au-show-head`) + solu mavi gradyan ikon rozeti (`.au-show-icon` içinde bi-box-seam) + durum rozeti başlığın yanında; görsel kartı sadeleştirildi.

## 🔗 SPA Link Denetimi — TAMAM ✅ (testing agent %100, iteration_5, 7/7 çalıştırılabilir PASS)
Dönüştürülmüş Inertia sayfalarına giden ama tam sayfa yenileyen `<a :href>` linkleri `<Link>`'e çevrildi:
Admin/Dashboard (4 hızlı işlem + "Tümü"), Admin/Auctions/Show (satıcı oku), Admin/Categories/Index+Show (Yeni/Alt Kategori), Dashboard (teklif başlığı + favori kartı), Buyer/Favorites, Buyer/MyBids, Messages/Index (peer adı), Profile/Show (İlan Oluştur).
Bilerek `<a>` kalanlar: Canlı Yayın (broadcast_url — GRUP 3 hâlâ Blade), Google OAuth, Auth login/register çapraz linkleri, AppLayout arama dropdown & hash linkleri.

## 🎯 UI İyileştirme Turu (kullanıcı geri bildirimi) — TAMAM ✅ (testing agent 8/8 PASS, iteration_6)
1. **Auth linkleri SPA:** Login/Register/ForgotPassword/ResetPassword çapraz linkleri `<a>`→`<Link>` (Google OAuth bilerek `<a>`).
2. **Auth logo light mode ortalama:** `admin-fixes.css`'e `html.light-mode .logo-light{display:inline-block}` (dark modda !important vardı).
3. **İlan detay "Satıcıya Mesaj Gönder":** native form POST → Inertia `router.post` (yenileme yok).
4. **Header arama sonucu:** `<a href>` → `router.visit` (SPA).
5. **Mesajlar sayfası genişletildi:** `.msg-page max-width 1100→1400px`.
6. **Profil hikaye oynatma:** StoryViewer artık `.story-source` DOM'undan da veri okur (`window.__refreshStorySources`).
7. **Hikaye görüntüleyici (Instagram/WhatsApp):** üstte animasyonlu progress bar (`.sp-fill-active` storyFill), 5sn otomatik ilerleme (setTimeout), son hikaye sonrası bir sonraki KULLANICIYA geçiş (`window.STORY_ORDER`), görsel tam yüklenene kadar skeleton/spinner (yarım render yok) + sonraki görseli ön-yükleme.
8. **Skeleton loader:** anasayfa ilan kartı görselleri için shimmer (`.idx-card-img::before`) + görsel yüklenince fade-in (`.loaded`/`.img-ready`).

## 🩹 Bug Fix Turu 3 (kullanıcı geri bildirimi) — TAMAM ✅ (testing agent 3/3 PASS, iteration_9)
- **Mesaj gönderilemiyordu (419 CSRF):** SPA'da `<meta csrf-token>` login sonrası bayatlıyordu. `resources/js/csrf.js` eklendi → `XSRF-TOKEN` cookie'sinden `X-XSRF-TOKEN` (her yanıtta taze). Messages/Index send() ve StoryViewer delete bunu kullanıyor. Bildirim `<Link>`'ine de `route('notifications.index')` fallback eklendi (iteration_8).
- **Story kullanıcı profil linki:** Görüntüleyici başlığındaki ad/avatar tıklanınca kullanıcının profiline SPA geçiş (payload'a `profile_url` eklendi).
- **Story silme modalı arkada açılıyordu:** `.swal2-container{z-index:100000}` (story-viewer 20000 idi) → SweetAlert artık üstte; silme sırasında ilerleme duraklıyor, iptal edilince devam ediyor.

## 🩹 Bug Fix Turu 4 — CSRF/419 kesin çözüm (testing agent %100, iteration_10)
- **Sorun:** Mesaj ilk denemede gönderilemiyor, "bir kez yenileyince" çalışıyordu. Kök neden: SPA'da `<meta csrf-token>` (ve fallback'ler) login/gezinti sonrası bayat → POST 419.
- **Çözüm:** `HandleInertiaRequests` artık her yanıtta taze `csrf_token` prop'u paylaşıyor; `AppLayout` her `router.on('success')`'te `<meta>`'yı ve `axios` default header'ını güncelliyor; `Messages/Index send()` doğrudan `page.props.csrf_token`'ı `X-CSRF-TOKEN` olarak gönderiyor. `csrf.js` öncelik: explicit token > XSRF cookie > meta.
- Doğrulama: ilk-deneme SPA mesaj POST 200, yenileme sonrası 200, admin destek yanıtı 200, story silme CSRF hatasız.
