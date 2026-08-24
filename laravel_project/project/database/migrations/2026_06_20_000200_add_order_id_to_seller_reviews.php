<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('seller_reviews', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('reviewer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seller_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('seller_reviews', 'order_id')) {
                $table->dropColumn('order_id');
            }
        });
    }
};
