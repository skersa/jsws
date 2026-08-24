<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request)
    {
        $orders = Order::where('buyer_id', $request->user()->id)
            ->with(['auction.cover', 'seller'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Buyer/Orders/Index', [
            'orders' => [
                'data' => collect($orders->items())->map(fn ($o) => [
                    'id'            => $o->id,
                    'title'         => Str::limit($o->auction?->title ?? 'İlan', 34),
                    'cover_url'     => $o->auction?->coverUrl() ?? asset('assets/media/placeholder.svg'),
                    'order_number'  => $o->order_number,
                    'seller_name'   => $o->seller?->name,
                    'amount'        => number_format($o->amount, 0, ',', '.') . ' ₺',
                    'status_label'  => $o->statusLabel(),
                    'status_color'  => $o->statusColor(),
                    'status_icon'   => $o->statusIcon(),
                    'show_url'      => route('orders.show', $o),
                ])->values(),
                'links' => $orders->linkCollection()->toArray(),
                'has_pages' => $orders->hasPages(),
            ],
        ]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        $order->load(['auction.cover', 'seller', 'events.actor', 'winningBid']);
        $existingReview = $order->seller?->reviewFrom($request->user());

        return Inertia::render('Buyer/Orders/Show', [
            'order' => [
                'id'               => $order->id,
                'order_number'     => $order->order_number,
                'status'           => $order->status,
                'status_label'     => $order->statusLabel(),
                'status_color'     => $order->statusColor(),
                'status_icon'      => $order->statusIcon(),
                'amount'           => number_format($order->amount, 0, ',', '.') . ' ₺',
                'progress_steps'   => $order->progressSteps(),
                'is_cancelled'     => in_array($order->status, ['cancelled', 'disputed'], true),
                'auction_title'    => $order->auction?->title,
                'cover_url'        => $order->auction?->coverUrl() ?? asset('assets/media/placeholder.svg'),
                'seller_name'      => $order->seller?->name,
                'seller_username'  => $order->seller?->username,
                'recipient_name'   => $order->recipient_name,
                'recipient_phone'  => $order->recipient_phone,
                'shipping_address' => $order->shipping_address,
                'address_city'     => $order->address_city,
                'address_district' => $order->address_district,
                'address_zip'      => $order->address_zip,
                'carrier'          => $order->carrier,
                'tracking_number'  => $order->tracking_number,
                'tracking_url'     => $order->tracking_url,
                'shipped_at'       => $order->shipped_at?->format('d.m.Y H:i'),
                'auto_release_at'  => $order->auto_release_at?->format('d.m.Y'),
                'dispute_reason'   => $order->dispute_reason,
                'events'           => $order->events->map(fn ($ev) => [
                    'id'          => $ev->id,
                    'title'       => $ev->title,
                    'description' => $ev->description,
                    'icon'        => $ev->icon ?? 'bi-dot',
                    'color'       => Order::STATUSES[$ev->status]['color'] ?? '#3b82f6',
                    'time'        => $ev->created_at->format('d.m.Y H:i'),
                    'actor'       => $ev->actor?->name,
                ])->values(),
            ],
            'buyerBalance' => number_format($request->user()->balance, 0, ',', '.') . ' ₺',
            'review' => [
                'seller_username' => $order->seller?->username,
                'seller_name'     => $order->seller?->name,
                'rating'          => $existingReview->rating ?? 5,
                'comment'         => $existingReview->comment ?? '',
                'exists'          => (bool) $existingReview,
            ],
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        if ($order->status !== 'awaiting_payment') {
            return back()->with('error', 'Bu sipariş için ödeme yapılamaz.');
        }

        if (! $this->orders->tryHoldEscrow($order)) {
            return redirect()
                ->route('general.balance.create')
                ->with('error', 'Bakiyeniz yetersiz. Lütfen bakiye yükleyip tekrar deneyin.');
        }

        return back()->with('success', 'Ödeme alındı ve güvenli şekilde emanete alındı.');
    }

    public function address(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        $data = $request->validate([
            'recipient_name'   => ['required', 'string', 'max:120'],
            'recipient_phone'  => ['required', 'string', 'max:30'],
            'address_line'     => ['required', 'string', 'max:500'],
            'address_city'     => ['required', 'string', 'max:80'],
            'address_district' => ['nullable', 'string', 'max:80'],
            'address_zip'      => ['nullable', 'string', 'max:20'],
        ]);

        $this->orders->setShippingAddress($order, $data);

        return back()->with('success', 'Teslimat adresi kaydedildi.');
    }

    public function confirm(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        abort_unless(in_array($order->status, ['shipped', 'delivered'], true), 422);

        $this->orders->confirmDelivered($order);

        return back()->with('success', 'Teslimatı onayladınız. Ödeme satıcıya aktarıldı.');
    }

    public function dispute(Request $request, Order $order)
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);
        abort_unless(in_array($order->status, ['paid', 'shipped', 'delivered'], true), 422);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $this->orders->openDispute($order, $data['reason']);

        return back()->with('success', 'Anlaşmazlık talebiniz alındı. Ekibimiz en kısa sürede inceleyecek.');
    }
}
