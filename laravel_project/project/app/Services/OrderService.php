<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\User;
use App\Notifications\OrderNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Alım-satım sonrası emanet (escrow) tabanlı sipariş yaşam döngüsü.
 *
 * Akış: awaiting_payment -> paid (para emanette) -> shipped -> delivered -> completed
 * Koruma: kazanan otomatik atanır, para emanette tutulur, teslimat sonrası satıcıya aktarılır,
 *         süre dolunca otomatik serbest bırakılır, anlaşmazlıkta admin karar verir.
 */
class OrderService
{
    public function __construct(private readonly BalanceService $balance) {}

    /**
     * Kazanan teklif ile açık artırmadan sipariş oluşturur ve mümkünse parayı emanete alır.
     */
    public function createFromWinningBid(Auction $auction, Bid $bid): Order
    {
        return DB::transaction(function () use ($auction, $bid) {
            $existing = Order::where('auction_id', $auction->id)->first();
            if ($existing) {
                return $existing;
            }

            $rate       = (float) (function_exists('setting') ? setting('commission_rate', 10) : 10);
            $amount     = (float) $bid->amount;
            $commission = round($amount * $rate / 100, 2);

            $order = Order::create([
                'order_number'      => $this->generateOrderNumber(),
                'auction_id'        => $auction->id,
                'buyer_id'          => $bid->user_id,
                'seller_id'         => $auction->user_id,
                'winning_bid_id'    => $bid->id,
                'amount'            => $amount,
                'commission_amount' => $commission,
                'status'            => 'awaiting_payment',
                'escrow_status'     => 'none',
            ]);

            $auction->forceFill([
                'status'         => 'sold',
                'winning_bid_id' => $bid->id,
                'sold_at'        => now(),
                'current_price'  => $amount,
            ])->save();

            $this->event($order, 'awaiting_payment', 'Sipariş oluşturuldu',
                'Açık artırmayı '.number_format($amount, 0, ',', '.').' ₺ ile kazandınız.', $bid->user_id, 'bi-trophy');

            // Alıcının bakiyesi yeterliyse parayı hemen emanete al
            $this->tryHoldEscrow($order);

            $this->notify($order->buyer, $order, 'Açık artırmayı kazandınız! 🎉',
                $auction->title.' — '.number_format($amount, 0, ',', '.').' ₺', 'bi-trophy', '#f59e0b');
            $this->notify($order->seller, $order, 'Ürününüz satıldı',
                $auction->title.' — '.number_format($amount, 0, ',', '.').' ₺', 'bi-cash-coin', '#10b981');

            return $order;
        });
    }

    /** Alıcının bakiyesini emanete al (yeterliyse). awaiting_payment -> paid */
    public function tryHoldEscrow(Order $order): bool
    {
        if ($order->status !== 'awaiting_payment') {
            return $order->escrow_status === 'held';
        }

        $buyer = $order->buyer;
        if ((float) $buyer->balance < (float) $order->amount) {
            return false;
        }

        return DB::transaction(function () use ($order, $buyer) {
            $this->balance->debit(
                user: $buyer,
                amount: (float) $order->amount,
                description: 'Sipariş ödemesi (emanet) — '.$order->order_number,
                meta: ['order_id' => $order->id, 'type' => 'escrow_hold'],
            );

            $order->update([
                'status'        => 'paid',
                'escrow_status' => 'held',
                'paid_at'       => now(),
            ]);

            $this->event($order, 'paid', 'Ödeme alındı (emanet)',
                'Tutar güvenli şekilde emanete alındı. Kargo sonrası satıcıya aktarılacak.', $buyer->id, 'bi-shield-check');

            $this->notify($order->seller, $order, 'Ödeme alındı',
                $order->order_number.' için ödeme emanete alındı. Kargoya verebilirsiniz.', 'bi-shield-check', '#3b82f6');

            return true;
        });
    }

    /** Teslimat adresini kaydeder. */
    public function setShippingAddress(Order $order, array $data): void
    {
        $address = trim($data['address_line'] ?? '');

        $order->update([
            'recipient_name'   => $data['recipient_name'] ?? null,
            'recipient_phone'  => $data['recipient_phone'] ?? null,
            'shipping_address' => $address,
            'address_city'     => $data['address_city'] ?? null,
            'address_district' => $data['address_district'] ?? null,
            'address_zip'      => $data['address_zip'] ?? null,
        ]);

        $this->event($order, $order->status, 'Teslimat adresi girildi',
            $order->recipient_name.' • '.$order->address_city, $order->buyer_id, 'bi-geo-alt');

        $this->notify($order->seller, $order, 'Teslimat adresi girildi',
            $order->order_number.' için alıcı adresini girdi.', 'bi-geo-alt', '#3b82f6');
    }

    /** Satıcı kargoya verdi. paid -> shipped */
    public function markShipped(Order $order, string $carrier, string $trackingNumber, ?string $trackingUrl = null): void
    {
        DB::transaction(function () use ($order, $carrier, $trackingNumber, $trackingUrl) {
            $order->update([
                'status'          => 'shipped',
                'carrier'         => $carrier,
                'tracking_number' => $trackingNumber,
                'tracking_url'    => $trackingUrl,
                'shipped_at'      => now(),
                'auto_release_at' => now()->addDays(7),
            ]);

            $this->event($order, 'shipped', 'Kargoya verildi',
                $carrier.' • Takip No: '.$trackingNumber, $order->seller_id, 'bi-truck');

            $this->notify($order->buyer, $order, 'Siparişiniz kargoya verildi 🚚',
                $carrier.' • Takip No: '.$trackingNumber, 'bi-truck', '#8b5cf6');
        });
    }

    /** Alıcı teslim aldı (veya süre dolunca otomatik). shipped -> completed + para satıcıya. */
    public function confirmDelivered(Order $order, bool $auto = false): void
    {
        if (! in_array($order->status, ['shipped', 'delivered'], true)) {
            return;
        }

        DB::transaction(function () use ($order, $auto) {
            $payout = (float) $order->amount - (float) $order->commission_amount;

            $order->update([
                'status'        => 'completed',
                'escrow_status' => 'released',
                'delivered_at'  => $order->delivered_at ?? now(),
                'completed_at'  => now(),
            ]);

            if ((float) $order->amount > 0) {
                $this->balance->credit(
                    user: $order->seller,
                    amount: $payout,
                    paymentMethod: 'escrow_release',
                    description: 'Satış geliri — '.$order->order_number,
                    reference: 'ESC-'.$order->order_number,
                    meta: ['order_id' => $order->id, 'commission' => (float) $order->commission_amount],
                );
            }

            $this->event($order, 'completed', $auto ? 'Otomatik teslim onayı' : 'Teslimat onaylandı',
                'Ödeme satıcıya aktarıldı (komisyon: '.number_format((float) $order->commission_amount, 0, ',', '.').' ₺).',
                $auto ? null : $order->buyer_id, 'bi-check-circle-fill');

            $this->notify($order->seller, $order, 'Ödemeniz serbest bırakıldı 💰',
                number_format($payout, 0, ',', '.').' ₺ bakiyenize eklendi.', 'bi-cash-stack', '#10b981');
            $this->notify($order->buyer, $order, 'Sipariş tamamlandı',
                'Teslimatı onayladığınız için teşekkürler.', 'bi-check-circle', '#10b981');
        });
    }

    /** Süresi dolan (7 gün) kargolanmış siparişleri otomatik tamamla. */
    public function autoReleaseExpired(): int
    {
        $count = 0;
        Order::where('status', 'shipped')
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', now())
            ->get()
            ->each(function (Order $order) use (&$count) {
                $this->confirmDelivered($order, auto: true);
                $count++;
            });
        return $count;
    }

    /** İptal / iade — para alıcıya geri döner. */
    public function cancelAndRefund(Order $order, string $reason, ?int $actorId = null): void
    {
        if (in_array($order->status, ['completed', 'cancelled'], true)) {
            return;
        }

        DB::transaction(function () use ($order, $reason, $actorId) {
            if ($order->escrow_status === 'held') {
                $this->balance->credit(
                    user: $order->buyer,
                    amount: (float) $order->amount,
                    paymentMethod: 'refund',
                    description: 'Sipariş iadesi — '.$order->order_number,
                    reference: 'RFN-'.$order->order_number,
                    meta: ['order_id' => $order->id],
                );
            }

            $order->update([
                'status'         => 'cancelled',
                'escrow_status'  => $order->escrow_status === 'held' ? 'refunded' : $order->escrow_status,
                'cancelled_at'   => now(),
                'dispute_status' => $order->dispute_status ? 'resolved_buyer' : $order->dispute_status,
            ]);

            $this->event($order, 'cancelled', 'Sipariş iptal edildi / iade',
                $reason, $actorId, 'bi-arrow-counterclockwise');

            $this->notify($order->buyer, $order, 'Siparişiniz iade edildi',
                number_format((float) $order->amount, 0, ',', '.').' ₺ bakiyenize geri yüklendi.', 'bi-arrow-counterclockwise', '#6b7280');
            $this->notify($order->seller, $order, 'Sipariş iptal edildi', $reason, 'bi-x-circle', '#6b7280');
        });
    }

    /** Alıcı anlaşmazlık açar. */
    public function openDispute(Order $order, string $reason): void
    {
        $order->update([
            'status'         => 'disputed',
            'dispute_reason' => $reason,
            'dispute_status' => 'open',
            'disputed_at'    => now(),
        ]);

        $this->event($order, 'disputed', 'Anlaşmazlık açıldı', $reason, $order->buyer_id, 'bi-exclamation-octagon');

        $this->notify($order->seller, $order, 'Sipariş için anlaşmazlık açıldı',
            'Yönetici inceleyecek: '.Str::limit($reason, 60), 'bi-exclamation-octagon', '#ef4444');

        // Adminlere bildir
        User::role('admin')->get()->each(fn ($admin) => $this->notify(
            $admin, $order, 'Yeni anlaşmazlık — '.$order->order_number, Str::limit($reason, 60), 'bi-exclamation-octagon', '#ef4444'
        ));
    }

    /** Admin anlaşmazlığı çözer: 'buyer' => iade, 'seller' => satıcıya öde. */
    public function resolveDispute(Order $order, string $decision, ?int $adminId = null): void
    {
        if ($decision === 'buyer') {
            $this->cancelAndRefund($order, 'Anlaşmazlık alıcı lehine sonuçlandı (yönetici kararı).', $adminId);
            $order->update(['dispute_status' => 'resolved_buyer']);
        } else {
            $order->update(['status' => 'shipped']); // release akışına geri al
            $this->confirmDelivered($order, auto: true);
            $order->update(['dispute_status' => 'resolved_seller']);
            $this->event($order, 'completed', 'Anlaşmazlık satıcı lehine sonuçlandı', 'Yönetici kararı.', $adminId, 'bi-gavel');
        }
    }

    /* ---------------- yardımcılar ---------------- */

    private function tryHoldEscrowSafe(Order $order): void
    {
        try { $this->tryHoldEscrow($order); } catch (\Throwable $e) { report($e); }
    }

    private function event(Order $order, string $status, string $title, ?string $desc, ?int $actorId, string $icon): OrderEvent
    {
        return OrderEvent::create([
            'order_id'    => $order->id,
            'status'      => $status,
            'title'       => $title,
            'description' => $desc,
            'actor_id'    => $actorId,
            'icon'        => $icon,
        ]);
    }

    private function notify(User $user, Order $order, string $title, string $message, string $icon, string $color): void
    {
        try {
            $user->notify(new OrderNotification($order, $title, $message, $icon, $color));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ART-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
