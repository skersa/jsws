<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SettingsController extends Controller
{
    // ── Ayarlar sayfasını göster ──────────────────────────────────────────────
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'stats' => [
                'users'    => \App\Models\User::count(),
                'auctions' => \App\Models\Auction::count(),
                'bids'     => \App\Models\Bid::count(),
            ],
            'testMailUrl' => route('admin.settings.test-mail'),
            'logoUrl'     => setting('site_logo') ? Storage::url(setting('site_logo')) : null,
            'system' => [
                'php'     => phpversion(),
                'laravel' => app()->version(),
                'env'     => app()->environment(),
                'cache'   => config('cache.default'),
                'queue'   => config('queue.default'),
                'storage' => config('filesystems.default'),
            ],
            'settings' => [
                // Genel
                'site_name'            => setting('site_name', config('app.name')),
                'site_url'             => setting('site_url', config('app.url')),
                'site_description'     => setting('site_description', ''),
                'default_lang'         => setting('default_lang', 'tr'),
                'timezone'             => setting('timezone', 'Europe/Istanbul'),
                'currency'             => setting('currency', 'TRY'),
                'commission_rate'      => setting('commission_rate', 5),
                'registration_enabled' => (bool) setting('registration_enabled', false),
                'email_verification'   => (bool) setting('email_verification', false),
                'auction_auto_extend'  => (bool) setting('auction_auto_extend', false),
                'guest_bidding'        => (bool) setting('guest_bidding', false),
                'maintenance_mode'     => (bool) setting('maintenance_mode', false),
                // SEO
                'meta_title'           => setting('meta_title', ''),
                'meta_description'     => setting('meta_description', ''),
                'meta_keywords'        => setting('meta_keywords', ''),
                'og_title'             => setting('og_title', ''),
                'og_description'       => setting('og_description', ''),
                'og_image'             => setting('og_image', ''),
                'analytics_code'       => setting('analytics_code', ''),
                // KVKK
                'kvkk_company'         => setting('kvkk_company', ''),
                'kvkk_email'           => setting('kvkk_email', ''),
                'kvkk_text'            => setting('kvkk_text', '<h2>KVKK Aydınlatma Metni</h2><p>Kişisel verileriniz, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında işlenmektedir.</p>'),
                'kvkk_required'        => (bool) setting('kvkk_required', true),
                'cookie_banner'        => (bool) setting('cookie_banner', true),
                // Gizlilik & Kullanım
                'privacy_text'         => setting('privacy_text', '<h2>Gizlilik Politikası</h2><p>Bu politika, kişisel verilerinizin nasıl toplandığını ve kullanıldığını açıklar.</p>'),
                'terms_text'           => setting('terms_text', '<h2>Kullanım Koşulları</h2><p>Sitemizi kullanarak aşağıdaki koşulları kabul etmiş sayılırsınız.</p>'),
                // İletişim
                'contact_email'        => setting('contact_email', ''),
                'support_email'        => setting('support_email', ''),
                'contact_phone'        => setting('contact_phone', ''),
                'whatsapp'             => setting('whatsapp', ''),
                'contact_address'      => setting('contact_address', ''),
                'smtp_host'            => setting('smtp_host', env('MAIL_HOST')),
                'smtp_port'            => setting('smtp_port', 587),
                'smtp_username'        => setting('smtp_username', ''),
                'mail_from_name'       => setting('mail_from_name', config('app.name')),
                'mail_from_address'    => setting('mail_from_address', ''),
                // Sosyal
                'social_instagram'     => setting('social_instagram', ''),
                'social_twitter'       => setting('social_twitter', ''),
                'social_facebook'      => setting('social_facebook', ''),
                'social_youtube'       => setting('social_youtube', ''),
                'social_linkedin'      => setting('social_linkedin', ''),
                'social_tiktok'        => setting('social_tiktok', ''),
                // Ödeme
                'iyzico_enabled'        => (bool) setting('iyzico_enabled', false),
                'bank_transfer_enabled' => (bool) setting('bank_transfer_enabled', false),
                'iyzico_env'            => setting('iyzico_env', 'sandbox'),
                'iyzico_api_key'        => setting('iyzico_api_key', ''),
                'bank_accounts'         => setting('bank_accounts', ''),
            ],
        ]);
    }

    // ── Ayarları güncelle ─────────────────────────────────────────────────────
    public function update(Request $request)
    {
        $section = $request->input('section', 'genel');

        match ($section) {
            'genel'    => $this->saveGenel($request),
            'seo'      => $this->saveSeo($request),
            'kvkk'     => $this->saveKvkk($request),
            'gizlilik' => $this->saveGizlilik($request),
            'kullanim' => $this->saveKullanim($request),
            'iletisim' => $this->saveIletisim($request),
            'sosyal'   => $this->saveSosyal($request),
            'odeme'    => $this->saveOdeme($request),
            default    => null,
        };

        Setting::clearCache();

        return back()->with('settings_success', 'Ayarlar başarıyla kaydedildi.');
    }

    // ── Genel ─────────────────────────────────────────────────────────────────
    private function saveGenel(Request $request): void
    {
        $request->validate([
            'site_name' => 'required|string|max:100',
            'site_url'  => 'required|url|max:255',
        ]);

        // Logo yükleme
        if ($request->hasFile('site_logo')) {
            $request->validate(['site_logo' => 'image|max:2048']);
            $old = setting('site_logo');
            if ($old) Storage::disk('public')->delete($old);
            $path = $request->file('site_logo')->store('settings', 'public');
            Setting::set('site_logo', $path, 'string', 'genel');
        }

        $stringFields = ['site_name', 'site_url', 'site_description', 'default_lang', 'timezone', 'currency'];
        foreach ($stringFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field), 'string', 'genel');
            }
        }

        Setting::set('commission_rate', $request->input('commission_rate', 5), 'integer', 'genel');

        $boolFields = ['registration_enabled', 'email_verification', 'auction_auto_extend', 'guest_bidding', 'maintenance_mode'];
        foreach ($boolFields as $field) {
            Setting::set($field, (bool) $request->input($field, false), 'boolean', 'genel');
        }
    }

    // ── SEO ───────────────────────────────────────────────────────────────────
    private function saveSeo(Request $request): void
    {
        $stringFields = ['meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description', 'og_image', 'analytics_code'];
        foreach ($stringFields as $field) {
            Setting::set($field, $request->input($field, ''), 'string', 'seo');
        }
    }

    // ── KVKK ──────────────────────────────────────────────────────────────────
    private function saveKvkk(Request $request): void
    {
        Setting::set('kvkk_company', $request->input('kvkk_company', ''), 'string', 'kvkk');
        Setting::set('kvkk_email',   $request->input('kvkk_email', ''),   'string', 'kvkk');
        Setting::set('kvkk_text',    $request->input('kvkk_text', ''),    'text',   'kvkk');
        Setting::set('kvkk_required', (bool) $request->input('kvkk_required', false), 'boolean', 'kvkk');
        Setting::set('cookie_banner', (bool) $request->input('cookie_banner', false), 'boolean', 'kvkk');
    }

    // ── Gizlilik ──────────────────────────────────────────────────────────────
    private function saveGizlilik(Request $request): void
    {
        Setting::set('privacy_text', $request->input('privacy_text', ''), 'text', 'gizlilik');
    }

    // ── Kullanım Koşulları ────────────────────────────────────────────────────
    private function saveKullanim(Request $request): void
    {
        Setting::set('terms_text', $request->input('terms_text', ''), 'text', 'kullanim');
    }

    // ── İletişim ──────────────────────────────────────────────────────────────
    private function saveIletisim(Request $request): void
    {
        $stringFields = [
            'contact_email', 'support_email', 'contact_phone',
            'whatsapp', 'contact_address',
            'smtp_host', 'smtp_port', 'smtp_username',
            'mail_from_name', 'mail_from_address',
        ];

        foreach ($stringFields as $field) {
            Setting::set($field, $request->input($field, ''), 'string', 'iletisim');
        }

        // Şifre boş bırakılmışsa mevcut şifreyi koru
        if ($request->filled('smtp_password')) {
            Setting::set('smtp_password', encrypt($request->input('smtp_password')), 'string', 'iletisim');
        }
    }

    // ── Sosyal ────────────────────────────────────────────────────────────────
    private function saveSosyal(Request $request): void
    {
        $fields = ['social_instagram', 'social_twitter', 'social_facebook', 'social_youtube', 'social_linkedin', 'social_tiktok'];
        foreach ($fields as $field) {
            Setting::set($field, $request->input($field, ''), 'string', 'sosyal');
        }
    }

    // ── Ödeme ─────────────────────────────────────────────────────────────────
    private function saveOdeme(Request $request): void
    {
        Setting::set('iyzico_enabled',       (bool) $request->input('iyzico_enabled', false),       'boolean', 'odeme');
        Setting::set('bank_transfer_enabled', (bool) $request->input('bank_transfer_enabled', false), 'boolean', 'odeme');
        Setting::set('iyzico_env',            $request->input('iyzico_env', 'sandbox'),               'string',  'odeme');
        Setting::set('iyzico_api_key',        $request->input('iyzico_api_key', ''),                  'string',  'odeme');
        Setting::set('bank_accounts',         $request->input('bank_accounts', ''),                   'text',    'odeme');

        if ($request->filled('iyzico_secret_key')) {
            Setting::set('iyzico_secret_key', encrypt($request->input('iyzico_secret_key')), 'string', 'odeme');
        }
    }

    // ── Cache işlemleri ───────────────────────────────────────────────────────
    public function cacheClear()
    {
        Artisan::call('cache:clear');
        Setting::clearCache();
        return back()->with('settings_success', 'Uygulama önbelleği temizlendi.');
    }

    public function cacheConfig()
    {
        Artisan::call('config:cache');
        return back()->with('settings_success', 'Config önbelleği oluşturuldu.');
    }

    public function cacheRoute()
    {
        Artisan::call('route:cache');
        return back()->with('settings_success', 'Route önbelleği oluşturuldu.');
    }

    public function cacheView()
    {
        Artisan::call('view:cache');
        return back()->with('settings_success', 'View önbelleği oluşturuldu.');
    }

    public function storageLink()
    {
        Artisan::call('storage:link');
        return back()->with('settings_success', 'Storage bağlantısı oluşturuldu.');
    }

    public function optimize()
    {
        Artisan::call('optimize');
        return back()->with('settings_success', 'Uygulama optimize edildi.');
    }

    // ── Test e-postası ────────────────────────────────────────────────────────
    public function testMail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Mail::raw('Bu bir test e-postasıdır. SMTP ayarlarınız çalışıyor.', function ($message) use ($request) {
                $message->to($request->input('email'))
                        ->subject(setting('site_name', config('app.name')) . ' — Test E-postası');
            });

            return response()->json(['success' => true, 'message' => 'Test e-postası başarıyla gönderildi.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
        }
    }
}
