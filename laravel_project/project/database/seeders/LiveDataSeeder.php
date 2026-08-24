<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\AuctionChatMessage;
use App\Models\Bid;
use App\Models\SellerProfile;
use App\Models\SellerReview;
use App\Models\Story;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LiveDataSeeder extends Seeder
{
    /**
     * Uygulamayı canlı bir platform gibi hissettirecek gerçekçi veri:
     * - Doğrulanmış satıcıların gerçek profil fotoğrafları
     * - Gerçek Unsplash görselleriyle 24-saatlik aktif hikayeler
     * - Auction sohbet mesajları (canlı yorum akışı)
     * - Yeni teklifler (son 10 dakika içinde)
     * - Watchlist bağlantıları
     * - Satıcı yorumları
     */
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('stories');
        Storage::disk('public')->makeDirectory('avatars');

        $this->command->info('▸ İlan (auction) görselleri güncelleniyor...');
        $this->refreshAuctionImages();

        $this->command->info('▸ Satıcı avatarları güncelleniyor...');
        $this->refreshSellerAvatars();

        $this->command->info('▸ Alıcı avatarları güncelleniyor...');
        $this->refreshBuyerAvatars();

        $this->command->info('▸ 24 saatlik hikayeler oluşturuluyor...');
        $this->seedStories();

        $this->command->info('▸ Yeni teklifler (son dakika) ekleniyor...');
        $this->seedRecentBids();

        $this->command->info('▸ Canlı sohbet mesajları ekleniyor...');
        $this->seedChatMessages();

        $this->command->info('▸ Satıcı değerlendirmeleri ekleniyor...');
        $this->seedSellerReviews();

        $this->command->info('▸ Watchlist bağlantıları ekleniyor...');
        $this->seedWatchlists();

        $this->command->info('✓ Canlı veri seed tamamlandı!');
    }

    /* ─────────────────────────────────────────
     * Yardımcı: URL'den dosya indir, storage'a kaydet
     * ───────────────────────────────────────── */
    private function downloadImage(string $url, string $dir, string $ext = 'jpg'): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->get($url);

            if (! $response->successful()) return null;

            $filename = $dir . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /* ─────────────────────────────────────────
     * Auction (ilan) görselleri — gerçek fotoğraflar
     * ───────────────────────────────────────── */
    private function refreshAuctionImages(): void
    {
        // Kategoriye göre gerçekçi ürün fotoğrafları (Unsplash)
        $imageMap = [
            'antika' => [
                'https://images.unsplash.com/photo-1594035910387-fea47794261f?w=1200&h=900&fit=crop&auto=format', // antika saat
                'https://images.unsplash.com/photo-1610177498876-e14c3d9d99fb?w=1200&h=900&fit=crop&auto=format', // porselen vazo
                'https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=1200&h=900&fit=crop&auto=format', // seramik
                'https://images.unsplash.com/photo-1567696911980-2eed69a46042?w=1200&h=900&fit=crop&auto=format', // vintage
                'https://images.unsplash.com/photo-1580136579312-94651dfd596d?w=1200&h=900&fit=crop&auto=format', // antika kitap
            ],
            'sanat' => [
                'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=1200&h=900&fit=crop&auto=format', // sanat tablo
                'https://images.unsplash.com/photo-1549887534-1541e9326642?w=1200&h=900&fit=crop&auto=format', // heykel
                'https://images.unsplash.com/photo-1579783483458-83d02161294e?w=1200&h=900&fit=crop&auto=format', // sanat objesi
            ],
            'elektronik' => [
                'https://images.unsplash.com/photo-1495707902641-75cac588d2e9?w=1200&h=900&fit=crop&auto=format', // vintage kamera
                'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=1200&h=900&fit=crop&auto=format', // yashica
                'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=1200&h=900&fit=crop&auto=format', // leica
                'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=1200&h=900&fit=crop&auto=format', // camera lens
            ],
            'mucevherat' => [
                'https://images.unsplash.com/photo-1587466412392-70b28ee23a24?w=1200&h=900&fit=crop&auto=format', // saat
                'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?w=1200&h=900&fit=crop&auto=format', // rolex
                'https://images.unsplash.com/photo-1611652022419-a9419f74343d?w=1200&h=900&fit=crop&auto=format', // takı
                'https://images.unsplash.com/photo-1601924572081-a9d68b3ebd76?w=1200&h=900&fit=crop&auto=format', // yüzük
            ],
            'default' => [
                'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200&h=900&fit=crop&auto=format',
                'https://images.unsplash.com/photo-1560264280-88b68371db39?w=1200&h=900&fit=crop&auto=format',
            ],
        ];

        $auctions = Auction::with('category')->get();

        // Önce mevcut auction_images kayıtlarını sil (temiz başlangıç)
        DB::table('auction_images')->delete();

        foreach ($auctions as $auction) {
            $catSlug = $auction->category?->slug ?? 'default';
            $pool = $imageMap[$catSlug] ?? $imageMap['default'];

            // Her ilana 2-4 görsel
            $count = min(rand(2, 4), count($pool));
            $selected = collect($pool)->shuffle()->take($count);

            foreach ($selected as $idx => $url) {
                $path = $this->downloadImage($url, 'auction-images');
                if (! $path) continue;

                DB::table('auction_images')->insert([
                    'auction_id' => $auction->id,
                    'path'       => $path,
                    'is_cover'   => $idx === 0 ? 1 : 0,
                    'sort_order' => $idx,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /* ─────────────────────────────────────────
     * Satıcı avatarları — gerçekçi portreler
     * ───────────────────────────────────────── */
    private function refreshSellerAvatars(): void
    {
        $sellerAvatars = [
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1552058544-f2b08422138a?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=400&h=400&fit=crop&crop=faces&auto=format',
        ];

        $sellers = User::role('seller')
            ->join('seller_profiles', 'seller_profiles.user_id', '=', 'users.id')
            ->where('seller_profiles.verification_status', 'approved')
            ->select('users.*')
            ->take(8)
            ->get();

        foreach ($sellers as $i => $u) {
            $path = $this->downloadImage($sellerAvatars[$i % count($sellerAvatars)], 'avatars');
            if ($path) {
                $u->update(['avatar' => $path]);
            }
        }
    }

    /* ─────────────────────────────────────────
     * Alıcı avatarları
     * ───────────────────────────────────────── */
    private function refreshBuyerAvatars(): void
    {
        $buyerAvatars = [
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1607746882042-944635dfe10e?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1584999734482-0361aecad844?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1543610892-0b1f7e6d8ac1?w=400&h=400&fit=crop&crop=faces&auto=format',
            'https://images.unsplash.com/photo-1546961342-1531b4be1116?w=400&h=400&fit=crop&crop=faces&auto=format',
        ];

        $buyers = User::role('buyer')->take(10)->get();

        foreach ($buyers as $i => $u) {
            $path = $this->downloadImage($buyerAvatars[$i % count($buyerAvatars)], 'avatars');
            if ($path) $u->update(['avatar' => $path]);
        }
    }

    /* ─────────────────────────────────────────
     * Hikayeler (24 saatlik)
     * ───────────────────────────────────────── */
    private function seedStories(): void
    {
        $storyContent = [
            [
                'img'     => 'https://images.unsplash.com/photo-1584464491033-06628f3a6b7b?w=1080&h=1920&fit=crop&auto=format',
                'caption' => '🔥 Bu akşam 21:00 canlı yayında! Nadir bulunan bir parça hazırlıyorum ✨',
            ],
            [
                'img'     => 'https://images.unsplash.com/photo-1548036328-c9fa32d4b0a3?w=1080&h=1920&fit=crop&auto=format',
                'caption' => 'Yeni gelen antika koleksiyondan bir kaç kare 📸 Yarın listelemeye başlıyorum',
            ],
            [
                'img'     => 'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?w=1080&h=1920&fit=crop&auto=format',
                'caption' => 'Türk kahvesi eşliğinde yeni depoyu düzenliyoruz ☕',
            ],
            [
                'img'     => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=1080&h=1920&fit=crop&auto=format',
                'caption' => 'Bugün +18 satış tamamlandı! Teşekkürler 🙏',
            ],
            [
                'img'     => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=1080&h=1920&fit=crop&auto=format',
                'caption' => 'Nadir bulunan Osmanlı dönemi cep saatı — yarın açık artırmaya çıkıyor ⏰',
            ],
            [
                'img'     => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1080&h=1920&fit=crop&auto=format',
                'caption' => 'Yeni mağazamızda sizi bekleriz 🛒',
            ],
            [
                'img'     => 'https://images.unsplash.com/photo-1520637836862-4d197d17c96a?w=1080&h=1920&fit=crop&auto=format',
                'caption' => 'Kaligrafi tabloları yolda… 📦',
            ],
            [
                'img'     => 'https://images.unsplash.com/photo-1560264280-88b68371db39?w=1080&h=1920&fit=crop&auto=format',
                'caption' => 'Nadir bulunan cam koleksiyon parçası 💎',
            ],
        ];

        $sellers = User::role('seller')
            ->join('seller_profiles', 'seller_profiles.user_id', '=', 'users.id')
            ->where('seller_profiles.verification_status', 'approved')
            ->select('users.*')
            ->take(6)
            ->get();

        foreach ($sellers as $sIdx => $seller) {
            // Her satıcıya 1-3 hikaye
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $c = $storyContent[($sIdx + $i) % count($storyContent)];
                $path = $this->downloadImage($c['img'], 'stories');
                if (! $path) continue;

                Story::create([
                    'user_id'    => $seller->id,
                    'media_path' => $path,
                    'media_type' => 'image',
                    'caption'    => $c['caption'],
                    'view_count' => rand(20, 500),
                    'expires_at' => now()->addHours(rand(3, 23)),
                    'created_at' => now()->subMinutes(rand(15, 720)),
                ]);
            }
        }
    }

    /* ─────────────────────────────────────────
     * Son dakika teklifleri (aktivite hissi)
     * ───────────────────────────────────────── */
    private function seedRecentBids(): void
    {
        $auctions = Auction::where('status', 'active')->take(10)->get();
        $buyers = User::role('buyer')->take(8)->get();

        foreach ($auctions as $auction) {
            $bidCount = rand(2, 6);
            $price = (float) $auction->current_price;

            for ($i = 0; $i < $bidCount; $i++) {
                $inc = rand(2, 8) * (float) $auction->min_bid_increment;
                $price += $inc;

                $buyer = $buyers->random();

                $bid = Bid::create([
                    'auction_id' => $auction->id,
                    'user_id'    => $buyer->id,
                    'amount'     => $price,
                    'created_at' => now()->subMinutes(rand(0, 15))->subSeconds(rand(0, 59)),
                    'updated_at' => now(),
                ]);
            }

            $auction->update(['current_price' => $price]);
        }
    }

    /* ─────────────────────────────────────────
     * Canlı chat mesajları
     * ───────────────────────────────────────── */
    private function seedChatMessages(): void
    {
        $messages = [
            'Fiyat çok uygun 🔥',
            'Ürün gerçekten muhteşem, tebrikler!',
            'Kargo süresi ne kadar?',
            'Ben de teklif verdim 🙌',
            'Bu parça çok nadir bulunur',
            'Canlı yayını nasıl açacaksın?',
            'Videosu var mı acaba?',
            'İstanbul teslimat opsiyonu var mı?',
            'Detaylı fotoğraf istiyorum',
            'Ürün orjinal mi?',
            'Aynısını uzun süredir arıyordum 😍',
            'Tam da istediğim gibi',
            'Peki reserve fiyat ne kadar?',
            '+1',
            'Süper bir koleksiyon',
            'Alıyorum bunu 💪',
            'Bir dahaki yayında da olayım',
            'İtaya gönderiyor musunuz?',
            'Hemen al fiyatı var mı?',
            'Yayına başladın mı hocam?',
        ];

        $auctions = Auction::where('status', 'active')->take(8)->get();
        $users = User::role('buyer')->take(10)->get()->concat(User::role('seller')->take(3)->get());

        foreach ($auctions as $auction) {
            $count = rand(4, 12);
            for ($i = 0; $i < $count; $i++) {
                $sender = $users->random();
                AuctionChatMessage::create([
                    'auction_id' => $auction->id,
                    'user_id'    => $sender->id,
                    'message'    => $messages[array_rand($messages)],
                    'is_seller'  => $sender->id === $auction->user_id ? 1 : 0,
                    'created_at' => now()->subMinutes(rand(0, 60))->subSeconds(rand(0, 59)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /* ─────────────────────────────────────────
     * Satıcı yorumları
     * ───────────────────────────────────────── */
    private function seedSellerReviews(): void
    {
        $comments = [
            'Ürün fotoğraflardaki gibi, çok memnun kaldım. Teşekkürler!',
            'Hızlı kargo, güvenilir satıcı. Kesinlikle tavsiye ederim.',
            'Paketleme mükemmeldi, ürün tam istediğim gibi geldi.',
            'İletişim çok iyi, sorularıma anında dönüş yaptı.',
            'Nadir bulunan bir parçaydı, çok teşekkürler.',
            'Fiyat performans harika. Yeni ürünleri de takip edeceğim.',
            'Ürün açıklamada belirtilen özelliklere uyuyor, memnun kaldım.',
            'Bir dahaki alışverişte de buradan alacağım. Süpersin!',
            'Canlı yayında verdiği bilgiler çok yardımcı oldu.',
        ];

        $sellers = User::role('seller')->take(6)->get();
        $buyers = User::role('buyer')->take(10)->get();

        foreach ($sellers as $seller) {
            // Her alıcı bir satıcıya sadece 1 review yapabilir (unique constraint)
            $available = $buyers->shuffle();
            $reviewCount = min(rand(3, 8), $available->count());
            for ($i = 0; $i < $reviewCount; $i++) {
                SellerReview::firstOrCreate(
                    [
                        'seller_id'   => $seller->id,
                        'reviewer_id' => $available[$i]->id,
                    ],
                    [
                        'rating'     => rand(4, 5),
                        'comment'    => $comments[array_rand($comments)],
                        'created_at' => now()->subDays(rand(1, 45))->subHours(rand(0, 23)),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /* ─────────────────────────────────────────
     * Watchlist (favoriler)
     * ───────────────────────────────────────── */
    private function seedWatchlists(): void
    {
        $auctions = Auction::where('status', 'active')->get();
        $buyers = User::role('buyer')->take(10)->get();

        foreach ($buyers as $buyer) {
            $picks = $auctions->random(min(rand(2, 5), $auctions->count()));
            foreach ($picks as $auction) {
                // Watchlist tablosu 'watchlist' (tekil), doğrudan query builder ile
                $exists = DB::table('watchlist')
                    ->where('user_id', $buyer->id)
                    ->where('auction_id', $auction->id)
                    ->exists();
                if ($exists) continue;

                DB::table('watchlist')->insert([
                    'user_id'    => $buyer->id,
                    'auction_id' => $auction->id,
                    'created_at' => now()->subHours(rand(1, 72)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
