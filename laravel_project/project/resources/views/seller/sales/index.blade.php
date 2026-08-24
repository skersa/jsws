@extends('layouts.app')
@section('title', 'Satışlarım')

@section('content')
<div class="dash-wrap py-4">

    <div class="admin-toolbar dash-hero">
        <div>
            <div class="toolbar-title">Satışlarım</div>
            <div class="dash-hero-sub">Sattığın ürünlerin ödeme, kargo ve teslimat durumunu yönet.</div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success" style="border-radius:12px">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" style="border-radius:12px">{{ session('error') }}</div>@endif

    <div class="admin-card" data-testid="sales-index-card">
        @if($orders->isEmpty())
            <div class="pf-empty">
                <div class="pf-empty-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="pf-empty-title">Henüz satışın yok</div>
                <div class="pf-empty-sub">Bir ürünün satıldığında burada görünecek.</div>
            </div>
        @else
            <div class="pf-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sipariş</th>
                            <th>Alıcı</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr data-testid="sale-row-{{ $order->id }}">
                            <td>
                                <div class="dash-item">
                                    <img class="a-avatar" src="{{ $order->auction?->cover?->url() ?? asset('assets/media/placeholder.svg') }}" alt="">
                                    <div>
                                        <a class="dash-item-title" href="{{ route('seller.sales.show', $order) }}">{{ \Illuminate\Support\Str::limit($order->auction?->title ?? 'İlan', 34) }}</a>
                                        <div class="dash-muted" style="font-size:11px">{{ $order->order_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="dash-muted">{{ $order->buyer?->name }}</td>
                            <td class="dash-amount">{{ number_format($order->amount, 0, ',', '.') }} ₺</td>
                            <td><span class="ord-status-pill" style="background:{{ $order->statusColor() }}"><i class="bi {{ $order->statusIcon() }}"></i> {{ $order->statusLabel() }}</span></td>
                            <td><a href="{{ route('seller.sales.show', $order) }}" class="btn-admin-ghost" data-testid="sale-detail-btn-{{ $order->id }}">Yönet</a></td>
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
