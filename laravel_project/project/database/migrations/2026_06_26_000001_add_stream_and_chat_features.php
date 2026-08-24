<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            // 'live' = canlı yayın, 'video' = tanıtım videosu
            $table->string('stream_mode', 20)->default('live')->after('status');
            $table->string('promo_video_url')->nullable()->after('stream_mode');
            $table->boolean('is_live')->default(false)->after('promo_video_url');
            $table->timestamp('live_started_at')->nullable()->after('is_live');
            $table->timestamp('live_ended_at')->nullable()->after('live_started_at');
        });

        Schema::create('auction_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message', 300);
            $table->boolean('is_seller')->default(false);
            $table->timestamps();

            $table->index(['auction_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_chat_messages');
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn(['stream_mode', 'promo_video_url', 'is_live', 'live_started_at', 'live_ended_at']);
        });
    }
};
