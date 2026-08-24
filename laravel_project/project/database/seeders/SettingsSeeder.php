<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'site_name',             'value' => 'Artirdim.com',          'type' => 'string',  'group' => 'genel'],
            ['key' => 'site_url',              'value' => 'https://artirdim.com',  'type' => 'string',  'group' => 'genel'],
            ['key' => 'site_description',      'value' => 'Güvenli açık artırma',  'type' => 'string',  'group' => 'genel'],
            ['key' => 'default_lang',          'value' => 'tr',                    'type' => 'string',  'group' => 'genel'],
            ['key' => 'timezone',              'value' => 'Europe/Istanbul',        'type' => 'string',  'group' => 'genel'],
            ['key' => 'currency',              'value' => 'TRY',                   'type' => 'string',  'group' => 'genel'],
            ['key' => 'commission_rate',       'value' => '5',                     'type' => 'integer', 'group' => 'genel'],
            ['key' => 'registration_enabled',  'value' => '1',                     'type' => 'boolean', 'group' => 'genel'],
            ['key' => 'email_verification',    'value' => '1',                     'type' => 'boolean', 'group' => 'genel'],
            ['key' => 'auction_auto_extend',   'value' => '1',                     'type' => 'boolean', 'group' => 'genel'],
            ['key' => 'guest_bidding',         'value' => '0',                     'type' => 'boolean', 'group' => 'genel'],
            ['key' => 'maintenance_mode',      'value' => '0',                     'type' => 'boolean', 'group' => 'genel'],

            ['key' => 'meta_title',       'value' => 'Artirdim.com — Güvenli Açık Artırma', 'type' => 'string', 'group' => 'seo'],
            ['key' => 'meta_description', 'value' => 'Türkiye\'nin güvenli online müzayede platformu.', 'type' => 'string', 'group' => 'seo'],
            ['key' => 'meta_keywords',    'value' => 'müzayede, açık artırma, online alışveriş', 'type' => 'string', 'group' => 'seo'],

            ['key' => 'kvkk_required',  'value' => '1',  'type' => 'boolean', 'group' => 'kvkk'],
            ['key' => 'cookie_banner',  'value' => '1',  'type' => 'boolean', 'group' => 'kvkk'],
            ['key' => 'kvkk_text',      'value' => '<h2>KVKK Aydınlatma Metni</h2><p>Kişisel verileriniz, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında işlenmektedir.</p>', 'type' => 'text', 'group' => 'kvkk'],

            ['key' => 'privacy_text', 'value' => '<h2>Gizlilik Politikası</h2><p>Bu politika, kişisel verilerinizin nasıl toplandığını ve kullanıldığını açıklar.</p>', 'type' => 'text', 'group' => 'gizlilik'],

            ['key' => 'terms_text', 'value' => '<h2>Kullanım Koşulları</h2><p>Sitemizi kullanarak aşağıdaki koşulları kabul etmiş sayılırsınız.</p>', 'type' => 'text', 'group' => 'kullanim'],

            ['key' => 'contact_email',    'value' => 'iletisim@artirdim.com', 'type' => 'string', 'group' => 'iletisim'],
            ['key' => 'support_email',    'value' => 'destek@artirdim.com',   'type' => 'string', 'group' => 'iletisim'],
            ['key' => 'mail_from_name',   'value' => 'Artirdim.com',          'type' => 'string', 'group' => 'iletisim'],

            ['key' => 'iyzico_enabled',        'value' => '0',       'type' => 'boolean', 'group' => 'odeme'],
            ['key' => 'bank_transfer_enabled',  'value' => '1',       'type' => 'boolean', 'group' => 'odeme'],
            ['key' => 'iyzico_env',             'value' => 'sandbox', 'type' => 'string',  'group' => 'odeme'],
        ];

        foreach ($defaults as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
