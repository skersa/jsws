@extends('layouts.app')
@section('title', 'Satış ' . $order->order_number)

@php $carriers = ['Yurtiçi Kargo','Aras Kargo','MNG Kargo','PTT Kargo','Sürat Kargo','Sendeo','HepsiJET','Trendyol Express','UPS','Kolay Gelsin']; @endphp

@section('content')
<div class="dash-wrap py-4">

    <div class="admin-toolbar dash-hero">
        <div>
            <div class="toolbar-title">Satış {{ $order->order_number }}</div>
            <div class="dash-hero-sub">{{ $order->auction?->title }}</div>
        </div>
        <a href="{{ route('seller.sales.index') }}" class="btn-admin-ghost"><i class="bi bi-arrow-left"></i> Satışlarım</a>
    </div>

    @if(session('success'))<div class="alert alert-success" style="border-radius:12px">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" style="border-radius:12px">{{ session('error') }}</div>@endif

    <div class="admin-card" style="margin-bottom:16px">
        @include('partials.order-progress', ['order' => $order])
    </div>

    <div class="ord-grid">
        <div>
            {{-- Ödeme durumu --}}
            <div class="ord-box">
                <div class="ord-box-title"><i class="bi bi-shield-check"></i> Ödeme Durumu</div>
                @if($order->escrow_status === 'held')
                    <div class="ord-track-badge" style="background:rgba(59,130,246,.14);border-color:rgba(59,130,246,.35);color:#93c5fd"><i class="bi bi-shield-lock"></i> Tutar emanette güvende — kargo + teslimat sonrası hesabınıza geçer</div>
                @elseif($order->escrow_status === 'released')
                    <div class="ord-track-badge" style="background:rgba(16,185,129,.14);border-color:rgba(16,185,129,.35);color:#6ee7b7"><i class="bi bi-cash-stack"></i> Ödeme hesabınıza aktarıldı</div>
                @else
                    <div class="ord-track-badge" style="background:rgba(245,158,11,.14);border-color:rgba(245,158,11,.35);color:#fcd34d"><i class="bi bi-hourglass-split"></i> Alıcının ödemesi bekleniyor</div>
                @endif
            </div>

            {{-- Alıcı teslimat adresi --}}
            <div class="ord-box" data-testid="seller-address-box">
                <div class="ord-box-title"><i class="bi bi-geo-alt"></i> Teslimat Adresi</div>
                @if($order->hasShippingAddress())
                    <div class="ord-info-row"><span class="k">Alıcı</span><span class="v">{{ $order->recipient_name }}</span></div>
                    <div class="ord-info-row"><span class="k">Telefon</span><span class="v">{{ $order->recipient_phone }}</span></div>
                    <div class="ord-info-row"><span class="k">Adres</span><span class="v" style="max-width:60%">{{ $order->shipping_address }}</span></div>
                    <div class="ord-info-row"><span class="k">İl / İlçe</span><span class="v">{{ $order->address_city }}{{ $order->address_district ? ' / '.$order->address_district : '' }}</span></div>
                @else
                    <p class="pf-text-muted-sm">Alıcı henüz teslimat adresini girmedi. Adres girildiğinde kargolayabilirsiniz.</p>
                @endif
            </div>

            {{-- Kargoya ver --}}
            @if($order->status === 'paid' && $order->hasShippingAddress())
            <div class="ord-box" data-testid="seller-ship-box">
                <div class="ord-box-title"><i class="bi bi-truck"></i> Kargoya Ver</div>
                <form method="POST" action="{{ route('seller.sales.ship', $order) }}">
                    @csrf
                    <div class="ord-field">
                        <label>Kargo Firması</label>
                        <select name="carrier" class="js-select2" data-placeholder="Kargo firması seçin" required data-testid="ship-carrier">
                            <option value=""></option>
                            @foreach($carriers as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                        </select>
                    </div>
                    <div class="ord-field"><label>Takip Numarası</label><input name="tracking_number" placeholder="Örn: 1234567890123" required data-testid="ship-tracking"></div>
                    <div class="ord-field"><label>Takip Linki (isteğe bağlı)</label><input name="tracking_url" type="url" placeholder="https://..."></div>
                    <button type="submit" class="btn-admin-pri" style="width:100%" data-testid="ship-submit-btn"><i class="bi bi-send me-1"></i> Kargoya Verdim</button>
                </form>
            </div>
            @endif

            @if(in_array($order->status, ['shipped','delivered','completed']) && $order->tracking_number)
            <div class="ord-box">
                <div class="ord-box-title"><i class="bi bi-truck"></i> Kargo Bilgisi</div>
                <div class="ord-info-row"><span class="k">Firma</span><span class="v">{{ $order->carrier }}</span></div>
                <div class="ord-info-row"><span class="k">Takip No</span><span class="v">{{ $order->tracking_number }}</span></div>
            </div>
            @endif

            @if($order->status === 'disputed')
            <div class="ord-box" style="border-color:rgba(239,68,68,.4)">
                <div class="ord-box-title" style="color:#f87171"><i class="bi bi-exclamation-octagon"></i> Anlaşmazlık</div>
                <div class="ord-info-row"><span class="k">Alıcı sebebi</span><span class="v">{{ $order->dispute_reason }}</span></div>
                <p class="pf-text-muted-sm" style="margin-top:8px">Yönetici inceliyor. Sonuca göre ödeme size aktarılacak veya alıcıya iade edilecek.</p>
            </div>
            @endif

            <div class="ord-box">
                <div class="ord-box-title"><i class="bi bi-clock-history"></i> Sipariş Geçmişi</div>
                @include('partials.order-timeline', ['order' => $order])
            </div>
        </div>

        <div>
            <div class="ord-box">
                <img src="{{ $order->auction?->cover?->url() ?? asset('assets/media/placeholder.svg') }}" alt="" style="width:100%;height:170px;object-fit:cover;border-radius:12px;margin-bottom:12px">
                <div style="font-weight:700;font-size:15px;color:var(--text)">{{ $order->auction?->title }}</div>
                <div class="pf-text-muted-sm" style="margin:4px 0 14px">Alıcı: {{ $order->buyer?->name }}</div>
                <div class="ord-info-row"><span class="k">Sipariş No</span><span class="v">{{ $order->order_number }}</span></div>
                <div class="ord-info-row"><span class="k">Satış Tutarı</span><span class="v">{{ number_format($order->amount, 0, ',', '.') }} ₺</span></div>
                <div class="ord-info-row"><span class="k">Komisyon</span><span class="v" style="color:#f87171">- {{ number_format($order->commission_amount, 0, ',', '.') }} ₺</span></div>
                <div class="ord-total"><span>Net Kazanç</span><span style="color:#10b981">{{ number_format($order->amount - $order->commission_amount, 0, ',', '.') }} ₺</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
