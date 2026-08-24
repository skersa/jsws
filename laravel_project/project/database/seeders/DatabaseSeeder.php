<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([SettingsSeeder::class]);
        $adminRole  = Role::firstOrCreate(['name' => 'admin',  'guard_name' => 'web']);
        $sellerRole = Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
        $buyerRole  = Role::firstOrCreate(['name' => 'buyer',  'guard_name' => 'web']);

        $admin = User::factory()->create([
            'name'        => 'Admin',
            'username' => 'admin',
            'email'       => 'admin@test.com',
            'password'    => bcrypt('password'),
            'is_verified' => 1
        ]);

        $admin->assignRole($adminRole);

        $seller = User::factory()->create([
            'name'        => 'test satıcı',
            'username' => 'seller',
            'email'       => 'seller@test.com',
            'password'    => bcrypt('password'),
            'is_verified' => 1
        ]);

        $seller->assignRole($sellerRole);

        SellerProfile::create([
            'user_id'             => $seller->id,
            'company_name'        => 'Test Satıcı A.Ş.',
            'tax_number'          => fake()->numerify('##########'),
            'iban'                => fake()->iban('TR'),
            'id_document_path'    => null,
            'verification_status' => 'approved',
            'verified_at'         => now(),
        ]);

         $buyer = User::factory()->create([
            'name'        => 'test alıcı',
            'username' => 'buyer',
            'email'       => 'buyer@test.com',
            'password'    => bcrypt('password'),
            'is_verified' => 1
        ]);

        $buyer->assignRole($buyerRole);

        $sellers = User::factory(50)->create();

        foreach ($sellers as $seller) {
            $seller->assignRole($sellerRole);

            SellerProfile::create([
                'user_id'             => $seller->id,
                'company_name'        => fake()->company(),
                'tax_number'          => fake()->numerify('##########'),
                'iban'                => fake()->iban('TR'),
                'id_document_path'    => null,
                'verification_status' => 'approved',
                'verified_at'         => now(),
            ]);
        }

        $buyers = User::factory(50)->create();

        foreach ($buyers as $buyer) {
            $buyer->assignRole($buyerRole);
        }

        Category::insert([
            ['name' => 'Elektronik', 'slug' => 'elektronik', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Antika',     'slug' => 'antika',     'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sanat',      'slug' => 'sanat',      'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mücevherat', 'slug' => 'mucevherat', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Auctions + zengin canlı veri (hikaye, chat, teklifler, review, watchlist, gerçek görseller)
        $this->call([
            AuctionSeeder::class,
            LiveDataSeeder::class,
        ]);
    }
}
