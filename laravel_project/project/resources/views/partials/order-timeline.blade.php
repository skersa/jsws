{{-- Sipariş olay zaman çizelgesi. Param: $order (events yüklü) --}}
<div class="ord-timeline" data-testid="order-timeline">
    @forelse($order->events as $ev)
    <div class="ord-tl-item">
        <div class="ord-tl-icon" style="--c: {{ \App\Models\Order::STATUSES[$ev->status]['color'] ?? '#3b82f6' }}">
            <i class="bi {{ $ev->icon ?? 'bi-dot' }}"></i>
        </div>
        <div class="ord-tl-body">
            <div class="ord-tl-title">{{ $ev->title }}</div>
            @if($ev->description)<div class="ord-tl-desc">{{ $ev->description }}</div>@endif
            <div class="ord-tl-time">
                {{ $ev->created_at->format('d.m.Y H:i') }}
                @if($ev->actor) • {{ $ev->actor->name }} @endif
            </div>
        </div>
    </div>
    @empty
    <div class="pf-text-muted-sm">Henüz kayıt yok.</div>
    @endforelse
</div>
