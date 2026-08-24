@extends('layouts.app')
@section('title', 'Sipariş ' . $order->order_number)

@section('content')
<div class="dash-wrap py-4">

    <div class="admin-toolbar dash-hero">
        <div>
            <div class="toolbar-title">Sipariş {{ $order->order_number }}</div>
            <div class="dash-hero-sub">{{ $order->auction?->title }}</div>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn-admin-ghost"><i class="bi bi-arrow-left"></i> Geri</a>
    </div>

    @if(session('success'))<div class="alert alert-success" style="border-radius:12px">{{ session('success') }}</div>@endif

    <div class="admin-card" style="margin-bottom:16px">@include('partials.order-progress', ['order' => $order])</div>

    <div class="ord-grid">
        <div>
            @if($order->status === 'disputed')
            <div class="ord-box" style="border-color:rgba(239,68,68,.4)" data-testid="admin-dispute-box">
                <div class="ord-box-title" style="color:#f87171"><i class="bi bi-exclamation-octagon"></i> Anlaşmazlık Çözümü</div>
                <div class="ord-info-row"><span class="k">Alıcının şikayeti</span></div>
                <p class="pf-text-muted-sm" style="margin:6px 0 14px">{{ $order->dispute_reason }}</p>
                <form method="POST" action="{{ route('admin.orders.resolve', $order) }}" style="display:flex;gap:10px">
                    @csrf
                    <button name="decision" value="buyer" class="btn-admin-danger" style="flex:1" onclick="return confirm('Alıcıya iade edilsin mi?')" data-testid="resolve-buyer-btn">Alıcı Lehine (İade)</button>
                    <button name="decision" value="seller" class="btn-admin-pri" style="flex:1" onclick="return confirm('Ödeme satıcıya aktarılsın mı?')" data-testid="resolve-seller-btn">Satıcı Lehine (Öde)</button>
                </form>
            </div>
            @endif

            <div class="ord-box">
                <div class="ord-box-title"><i class="bi bi-info-circle"></i> Sipariş Bilgileri</div>
                <div class="ord-info-row"><span class="k">Alıcı</span><span class="v">{{ $order->buyer?->name }}</span></div>
                <div class="ord-info-row"><span class="k">Satıcı</span><span class="v">{{ $order->seller?->name }}</span></div>
                <div class="ord-info-row"><span class="k">Emanet Durumu</span><span class="v">{{ $order->escrow_status }}</span></div>
                <div class="ord-info-row"><span class="k">Kargo</span><span class="v">{{ $order->carrier ? $order->carrier.' • '.$order->tracking_number : '—' }}</span></div>
                @if($order->hasShippingAddress())
                <div class="ord-info-row"><span class="k">Adres</span><span class="v" style="max-width:60%">{{ $order->recipient_name }}, {{ $order->shipping_address }}, {{ $order->address_city }}</span></div>
                @endif
            </div>

            <div class="ord-box">
                <div class="ord-box-title"><i class="bi bi-clock-history"></i> Zaman Çizelgesi</div>
                @include('partials.order-timeline', ['order' => $order])
            </div>
        </div>

        <div>
            <div class="ord-box">
                <img src="{{ $order->auction?->cover?->url() ?? asset('assets/media/placeholder.svg') }}" alt="" style="width:100%;height:170px;object-fit:cover;border-radius:12px;margin-bottom:12px">
                <div style="font-weight:700;color:var(--text)">{{ $order->auction?->title }}</div>
                <div class="ord-info-row" style="margin-top:10px"><span class="k">Tutar</span><span class="v">{{ number_format($order->amount, 0, ',', '.') }} ₺</span></div>
                <div class="ord-info-row"><span class="k">Komisyon</span><span class="v">{{ number_format($order->commission_amount, 0, ',', '.') }} ₺</span></div>
                <div class="ord-info-row"><span class="k">Durum</span><span class="v" style="color:{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
