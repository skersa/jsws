@extends('layouts.app')
@section('title', 'Siparişlerim')

@section('content')
<div class="dash-wrap py-4">

    <div class="admin-toolbar dash-hero">
        <div>
            <div class="toolbar-title">Siparişlerim</div>
            <div class="dash-hero-sub">Kazandığın açık artırmaların ödeme ve kargo durumunu buradan takip et.</div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success" style="border-radius:12px">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" style="border-radius:12px">{{ session('error') }}</div>@endif

    <div class="admin-card" data-testid="orders-index-card">
        @if($orders->isEmpty())
            <div class="pf-empty">
                <div class="pf-empty-icon"><i class="bi bi-bag-check"></i></div>
                <div class="pf-empty-title">Henüz siparişin yok</div>
                <div class="pf-empty-sub">Bir açık artırma kazandığında siparişin burada görünecek.</div>
                <a href="{{ route('browse.auctions') }}" class="btn-admin-pri dash-mt">Müzayedeleri Keşfet</a>
            </div>
        @else
            <div class="pf-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sipariş</th>
                            <th>Satıcı</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr data-testid="order-row-{{ $order->id }}">
                            <td>
                                <div class="dash-item">
                                    <img class="a-avatar" src="{{ $order->auction?->cover?->url() ?? asset('assets/media/placeholder.svg') }}" alt="">
                                    <div>
                                        <a class="dash-item-title" href="{{ route('orders.show', $order) }}">
                                            {{ \Illuminate\Support\Str::limit($order->auction?->title ?? 'İlan', 34) }}
                                        </a>
                                        <div class="dash-muted" style="font-size:11px">{{ $order->order_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="dash-muted">{{ $order->seller?->name }}</td>
                            <td class="dash-amount">{{ number_format($order->amount, 0, ',', '.') }} ₺</td>
                            <td><span class="ord-status-pill" style="background:{{ $order->statusColor() }}"><i class="bi {{ $order->statusIcon() }}"></i> {{ $order->statusLabel() }}</span></td>
                            <td><a href="{{ route('orders.show', $order) }}" class="btn-admin-ghost" data-testid="order-detail-btn-{{ $order->id }}">Detay</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-mt">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
