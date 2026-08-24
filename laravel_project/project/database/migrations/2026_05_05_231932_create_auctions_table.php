<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('starting_price', 12, 2);
            $table->decimal('reserve_price', 12, 2)->nullable();
            $table->decimal('current_price', 12, 2);
            $table->decimal('buy_now_price', 12, 2)->nullable();
            $table->decimal('min_bid_increment', 12, 2)->default(1.00);
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->enum('status', ['draft', 'active', 'ended', 'cancelled', 'sold'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->string('condition')->nullable(); // new, used, refurbished
            $table->string('location')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
