@extends('layouts.app')
@section('title', 'Siparişler & Anlaşmazlıklar')

@section('content')
<div class="dash-wrap py-4">

    <div class="admin-toolbar dash-hero">
        <div>
            <div class="toolbar-title">Siparişler & Anlaşmazlıklar</div>
            <div class="dash-hero-sub">Tüm siparişleri izleyin, anlaşmazlıkları çözün.</div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success" style="border-radius:12px">{{ session('success') }}</div>@endif

    <div class="admin-card orders-tabs" data-testid="admin-orders-tabs">
        @php $tabs = ['all'=>'Tümü ('.$counts['all'].')','disputed'=>'Anlaşmazlık ('.$counts['disputed'].')','active'=>'Devam Eden ('.$counts['active'].')','completed'=>'Tamamlanan ('.$counts['completed'].')']; @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.orders.index', $key === 'all' ? [] : ['status' => $key]) }}"
               class="{{ ($status ?? 'all') === $key || (!$status && $key==='all') ? 'btn-admin-pri' : 'btn-admin-ghost' }}"
               data-testid="admin-orders-tab-{{ $key }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="admin-card" data-testid="admin-orders-card">
        @if($orders->isEmpty())
            <div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-inbox"></i></div><div class="pf-empty-title">Kayıt yok</div></div>
        @else
            <div class="pf-table-responsive">
                <table class="admin-table">
                    <thead><tr><th>Sipariş No</th><th>Ürün</th><th>Alıcı</th><th>Satıcı</th><th>Tutar</th><th>Durum</th><th></th></tr></thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr data-testid="admin-order-row-{{ $order->id }}">
                            <td class="dash-muted" style="font-size:12px">{{ $order->order_number }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($order->auction?->title ?? 'İlan', 28) }}</td>
                            <td class="dash-muted">{{ $order->buyer?->name }}</td>
                            <td class="dash-muted">{{ $order->seller?->name }}</td>
                            <td class="dash-amount">{{ number_format($order->amount, 0, ',', '.') }} ₺</td>
                            <td><span class="ord-status-pill" style="background:{{ $order->statusColor() }}"><i class="bi {{ $order->statusIcon() }}"></i> {{ $order->statusLabel() }}</span></td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="btn-admin-ghost">İncele</a></td>
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
