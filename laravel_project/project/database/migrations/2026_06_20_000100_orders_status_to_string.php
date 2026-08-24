<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // status ve escrow_status ENUM/uzunluk kısıtı olmadan serbest string olsun
        DB::statement("ALTER TABLE `orders` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'awaiting_payment'");
        DB::statement("ALTER TABLE `orders` MODIFY `escrow_status` VARCHAR(20) NOT NULL DEFAULT 'none'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'pending'");
    }
};
