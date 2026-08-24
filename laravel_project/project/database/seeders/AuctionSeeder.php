<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Bid;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuctionSeeder extends Seeder
{
    public function run(): void
    {
        $sellerRole = Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
        $buyerRole  = Role::firstOrCreate(['name' => 'buyer', 'guard_name' => 'web']);

        $sellers = User::role('seller')->take(6)->get();
        if ($sellers->isEmpty()) {
            $sellers = collect([User::where('email', 'seller@test.com')->first()])->filter();
        }
        $buyers = User::role('buyer')->take(10)->get();

        $cats = Category::pluck('id', 'slug');
        $catFor = fn($slug) => $cats[$slug] ?? $cats->first();

        $items = [
            ['Antika Masa Saati ve Vazo Seti', 'antika', 12000, 'a1.jpg', 'active', 'İstanbul'],
            ['El Yapımı Porselen At Figürlü Vazo', 'antika', 4500, 'a2.jpg', 'active', 'İzmir'],
            ['Bordo Altın Detaylı Porselen Vazo', 'sanat', 8800, 'a3.jpg', 'active', 'Ankara'],
            ['Mavi-Beyaz Çini Vazo Üçlüsü', 'antika', 3200, 'a4.jpg', 'active', 'Bursa'],
            ['Vintage Yashica Fotoğraf Makinesi', 'elektronik', 2600, 'a5.jpg', 'active', 'İstanbul'],
            ['Klasik Çelik Kol Saati (Koleksiyon)', 'mucevherat', 15400, 'a6.jpg', 'active', 'Antalya'],
            ['Retro Analog Kamera & Saat Koleksiyonu', 'elektronik', 5100, 'a7.jpg', 'ended', 'İstanbul'],
            ['Leica Kamera ve Vintage Aksesuarlar', 'elektronik', 21000, 'a8.jpg', 'active', 'Kocaeli'],
        ];

        foreach ($items as $i => [$title, $catSlug, $price, $img, $status, $loc]) {
            $seller = $sellers[$i % max($sellers->count(), 1)] ?? $sellers->first();
            if (!$seller) {
                continue;
            }

            $isLive = $i < 2; // ilk iki ilan canlı yayında
            $endsAt = $status === 'ended' ? now()->subDays(2) : now()->addDays(rand(1, 6))->addHours(rand(1, 12));

            $auction = Auction::create([
                'user_id'          => $seller->id,
                'category_id'      => $catFor($catSlug),
                'title'            => $title,
                'slug'             => Str::slug($title) . '-' . Str::random(4),
                'description'      => "Bu " . $title . " gerçek bir koleksiyon parçasıdır. Orijinal, bakımlı ve sertifikalıdır. Detaylı fotoğraflar ve durum raporu için satıcıyla iletişime geçebilirsiniz.",
                'starting_price'   => $price,
                'current_price'    => $price,
                'min_bid_increment'=> max(50, round($price * 0.02)),
                'buy_now_price'    => round($price * 1.6),
                'starts_at'        => now()->subDays(1),
                'ends_at'          => $endsAt,
                'status'           => $status,
                'is_featured'      => $i < 3,
                'condition'        => 'used',
                'location'         => $loc,
                'view_count'       => rand(40, 900),
            ]);

            AuctionImage::create([
                'auction_id' => $auction->id,
                'path'       => 'auctions/' . $img,
                'is_cover'   => true,
                'sort_order' => 0,
            ]);

            // teklifler
            $bidCount = rand(3, 9);
            $amount = $price;
            for ($b = 0; $b < $bidCount; $b++) {
                $amount += rand(1, 4) * $auction->min_bid_increment;
                $bidder = $buyers->isNotEmpty() ? $buyers->random() : $seller;
                Bid::create([
                    'auction_id' => $auction->id,
                    'user_id'    => $bidder->id,
                    'amount'     => $amount,
                    'created_at' => now()->subMinutes(($bidCount - $b) * rand(5, 40)),
                ]);
            }
            $auction->update(['current_price' => $amount]);
        }
    }
}
