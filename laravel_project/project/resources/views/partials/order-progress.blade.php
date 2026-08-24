{{-- Sipariş ilerleme çubuğu. Param: $order --}}
@php $steps = $order->progressSteps(); $cancelled = in_array($order->status, ['cancelled','disputed'], true); @endphp
<div class="ord-progress {{ $cancelled ? 'ord-progress-alert' : '' }}" data-testid="order-progress">
    @foreach($steps as $i => $s)
        <div class="ord-step {{ $s['done'] ? 'done' : '' }} {{ $s['active'] && !$cancelled ? 'active' : '' }}">
            <div class="ord-step-dot">
                @if($s['done'])<i class="bi bi-check-lg"></i>@else<span>{{ $i+1 }}</span>@endif
            </div>
            <div class="ord-step-label">{{ $s['label'] }}</div>
        </div>
        @if(!$loop->last)<div class="ord-step-line {{ $s['done'] ? 'done' : '' }}"></div>@endif
    @endforeach
</div>
@if($cancelled)
<div class="ord-status-alert" style="--c: {{ $order->statusColor() }}">
    <i class="bi {{ $order->statusIcon() }}"></i>
    <span>{{ $order->statusLabel() }}</span>
</div>
@endif
