<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request)
    {
        $orders = Order::where('seller_id', $request->user()->id)
            ->with(['auction.cover', 'buyer'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Seller/Sales/Index', [
            'orders' => [
                'data' => collect($orders->items())->map(fn ($o) => [
                    'id'           => $o->id,
                    'title'        => Str::limit($o->auction?->title ?? 'İlan', 34),
                    'cover_url'    => $o->auction?->coverUrl() ?? asset('assets/media/placeholder.svg'),
                    'order_number' => $o->order_number,
                    'buyer_name'   => $o->buyer?->name,
                    'amount'       => number_format($o->amount, 0, ',', '.') . ' ₺',
                    'status_label' => $o->statusLabel(),
                    'status_color' => $o->statusColor(),
                    'status_icon'  => $o->statusIcon(),
                    'show_url'     => route('seller.sales.show', $o),
                ])->values(),
                'links'     => $orders->linkCollection()->toArray(),
                'has_pages' => $orders->hasPages(),
            ],
        ]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->seller_id === $request->user()->id, 403);

        $order->load(['auction.cover', 'buyer', 'events.actor', 'winningBid']);

        $carriers = ['Yurtiçi Kargo', 'Aras Kargo', 'MNG Kargo', 'PTT Kargo', 'Sürat Kargo', 'Sendeo', 'HepsiJET', 'Trendyol Express', 'UPS', 'Kolay Gelsin'];

        return Inertia::render('Seller/Sales/Show', [
            'carriers' => $carriers,
            'order' => [
                'id'                 => $order->id,
                'order_number'       => $order->order_number,
                'status'             => $order->status,
                'status_label'       => $order->statusLabel(),
                'status_color'       => $order->statusColor(),
                'status_icon'        => $order->statusIcon(),
                'escrow_status'      => $order->escrow_status,
                'amount'             => number_format($order->amount, 0, ',', '.') . ' ₺',
                'commission_amount'  => number_format($order->commission_amount, 0, ',', '.') . ' ₺',
                'net_amount'         => number_format($order->amount - $order->commission_amount, 0, ',', '.') . ' ₺',
                'progress_steps'     => $order->progressSteps(),
                'is_cancelled'       => in_array($order->status, ['cancelled', 'disputed'], true),
                'auction_title'      => $order->auction?->title,
                'cover_url'          => $order->auction?->coverUrl() ?? asset('assets/media/placeholder.svg'),
                'buyer_name'         => $order->buyer?->name,
                'has_shipping_address' => $order->hasShippingAddress(),
                'recipient_name'     => $order->recipient_name,
                'recipient_phone'    => $order->recipient_phone,
                'shipping_address'   => $order->shipping_address,
                'address_city'       => $order->address_city,
                'address_district'   => $order->address_district,
                'carrier'            => $order->carrier,
                'tracking_number'    => $order->tracking_number,
                'dispute_reason'     => $order->dispute_reason,
                'ship_url'           => route('seller.sales.ship', $order),
                'events'             => $order->events->map(fn ($ev) => [
                    'id'          => $ev->id,
                    'title'       => $ev->title,
                    'description' => $ev->description,
                    'icon'        => $ev->icon ?? 'bi-dot',
                    'color'       => Order::STATUSES[$ev->status]['color'] ?? '#3b82f6',
                    'time'        => $ev->created_at->format('d.m.Y H:i'),
                    'actor'       => $ev->actor?->name,
                ])->values(),
            ],
        ]);
    }

    public function ship(Request $request, Order $order)
    {
        abort_unless($order->seller_id === $request->user()->id, 403);

        if ($order->status !== 'paid') {
            return back()->with('error', 'Kargoya vermek için siparişin ödemesinin alınmış olması gerekir.');
        }

        if (! $order->hasShippingAddress()) {
            return back()->with('error', 'Alıcı henüz teslimat adresini girmedi.');
        }

        $data = $request->validate([
            'carrier'         => ['required', 'string', 'max:80'],
            'tracking_number' => ['required', 'string', 'max:100'],
            'tracking_url'    => ['nullable', 'url', 'max:300'],
        ]);

        $this->orders->markShipped($order, $data['carrier'], $data['tracking_number'], $data['tracking_url'] ?? null);

        return back()->with('success', 'Kargo bilgisi kaydedildi ve alıcı bilgilendirildi.');
    }
}
