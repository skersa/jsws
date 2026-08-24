<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'auction_id', 'buyer_id', 'seller_id', 'winning_bid_id',
        'amount', 'commission_amount', 'status', 'escrow_status',
        'shipping_address', 'recipient_name', 'recipient_phone',
        'address_city', 'address_district', 'address_zip',
        'tracking_number', 'carrier', 'tracking_url',
        'paid_at', 'shipped_at', 'delivered_at', 'auto_release_at', 'completed_at', 'cancelled_at',
        'dispute_reason', 'dispute_status', 'disputed_at',
    ];

    protected $casts = [
        'paid_at'         => 'datetime',
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
        'auto_release_at' => 'datetime',
        'completed_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
        'disputed_at'     => 'datetime',
        'amount'          => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function auction()   { return $this->belongsTo(Auction::class); }
    public function buyer()     { return $this->belongsTo(User::class, 'buyer_id'); }
    public function seller()    { return $this->belongsTo(User::class, 'seller_id'); }
    public function payments()  { return $this->hasMany(Payment::class); }
    public function winningBid(){ return $this->belongsTo(Bid::class, 'winning_bid_id'); }
    public function events()    { return $this->hasMany(OrderEvent::class)->orderBy('created_at')->orderBy('id'); }

    /* ---- Durum yardımcıları ---- */

    public const STATUSES = [
        'awaiting_payment' => ['label' => 'Ödeme Bekleniyor',   'color' => '#f59e0b', 'icon' => 'bi-wallet2'],
        'paid'             => ['label' => 'Ödeme Alındı',       'color' => '#3b82f6', 'icon' => 'bi-shield-check'],
        'shipped'          => ['label' => 'Kargoya Verildi',    'color' => '#8b5cf6', 'icon' => 'bi-truck'],
        'delivered'        => ['label' => 'Teslim Edildi',      'color' => '#10b981', 'icon' => 'bi-box-seam'],
        'completed'        => ['label' => 'Tamamlandı',         'color' => '#10b981', 'icon' => 'bi-check-circle-fill'],
        'disputed'         => ['label' => 'Anlaşmazlık',        'color' => '#ef4444', 'icon' => 'bi-exclamation-octagon'],
        'cancelled'        => ['label' => 'İptal / İade',       'color' => '#6b7280', 'icon' => 'bi-x-circle'],
    ];

    public function statusLabel(): string { return self::STATUSES[$this->status]['label'] ?? $this->status; }
    public function statusColor(): string { return self::STATUSES[$this->status]['color'] ?? '#6b7280'; }
    public function statusIcon(): string  { return self::STATUSES[$this->status]['icon'] ?? 'bi-dot'; }

    public function hasShippingAddress(): bool
    {
        return filled($this->shipping_address) && filled($this->recipient_name);
    }

    /** İlerleme adımları (buyer/seller ortak zaman çizelgesi) */
    public function progressSteps(): array
    {
        $order = ['awaiting_payment', 'paid', 'shipped', 'delivered', 'completed'];
        $current = in_array($this->status, $order, true) ? array_search($this->status, $order, true) : count($order) - 1;

        $labels = [
            'awaiting_payment' => 'Sipariş',
            'paid'             => 'Ödeme (Emanet)',
            'shipped'          => 'Kargo',
            'delivered'        => 'Teslimat',
            'completed'        => 'Tamamlandı',
        ];

        $steps = [];
        foreach ($order as $i => $key) {
            $steps[] = [
                'key'   => $key,
                'label' => $labels[$key],
                'done'  => $i < $current || $this->status === 'completed',
                'active'=> $i === $current,
            ];
        }
        return $steps;
    }

    public function commissionRate(): float
    {
        return (float) (function_exists('setting') ? setting('commission_rate', 10) : 10);
    }
}
