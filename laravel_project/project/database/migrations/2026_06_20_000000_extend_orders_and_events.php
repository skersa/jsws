<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'winning_bid_id')) {
                $table->unsignedBigInteger('winning_bid_id')->nullable()->after('status');
            }
            if (! Schema::hasColumn('auctions', 'sold_at')) {
                $table->timestamp('sold_at')->nullable()->after('winning_bid_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $add = function (string $col, callable $def) use ($table) {
                if (! Schema::hasColumn('orders', $col)) {
                    $def();
                }
            };

            $add('order_number',     fn () => $table->string('order_number')->nullable()->unique()->after('id'));
            $add('escrow_status',    fn () => $table->string('escrow_status')->default('none')->after('status'));   // none|held|released|refunded
            $add('recipient_name',   fn () => $table->string('recipient_name')->nullable()->after('shipping_address'));
            $add('recipient_phone',  fn () => $table->string('recipient_phone')->nullable()->after('recipient_name'));
            $add('address_city',     fn () => $table->string('address_city')->nullable()->after('recipient_phone'));
            $add('address_district', fn () => $table->string('address_district')->nullable()->after('address_city'));
            $add('address_zip',      fn () => $table->string('address_zip')->nullable()->after('address_district'));
            $add('carrier',          fn () => $table->string('carrier')->nullable()->after('tracking_number'));
            $add('tracking_url',     fn () => $table->string('tracking_url')->nullable()->after('carrier'));
            $add('delivered_at',     fn () => $table->timestamp('delivered_at')->nullable()->after('shipped_at'));
            $add('auto_release_at',  fn () => $table->timestamp('auto_release_at')->nullable()->after('delivered_at'));
            $add('cancelled_at',     fn () => $table->timestamp('cancelled_at')->nullable()->after('completed_at'));
            $add('dispute_reason',   fn () => $table->text('dispute_reason')->nullable()->after('cancelled_at'));
            $add('dispute_status',   fn () => $table->string('dispute_status')->nullable()->after('dispute_reason')); // open|resolved_buyer|resolved_seller
            $add('disputed_at',      fn () => $table->timestamp('disputed_at')->nullable()->after('dispute_status'));
        });

        if (! Schema::hasTable('order_events')) {
            Schema::create('order_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('status');
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('icon')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');

        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'order_number','escrow_status','recipient_name','recipient_phone',
                'address_city','address_district','address_zip','carrier','tracking_url',
                'delivered_at','auto_release_at','cancelled_at','dispute_reason','dispute_status','disputed_at',
            ] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('auctions', function (Blueprint $table) {
            foreach (['winning_bid_id','sold_at'] as $col) {
                if (Schema::hasColumn('auctions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
