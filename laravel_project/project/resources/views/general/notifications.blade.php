@extends('layouts.app')
@section('title', 'Bildirimler')
@section('content')

<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="text-muted">Bildirimler</h5>
        @if($notifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="pf-btn-reset" style="font-size:12px;">
                    <i class="bi bi-check2-all me-1"></i> Tümünü okundu say
                </button>
            </form>
        @endif
    </div>
    @if($notifications->isEmpty())
        <div class="text-center text-muted py-10">
            <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-50"></i>
            Henüz bildiriminiz yok.
        </div>
    @else
        <div class="d-flex flex-column gap-2">
            @foreach($notifications as $notif)
                @php
                    $data = $notif->data;
                    $type = $data['type'] ?? 'follow';
                    $unread = is_null($notif->read_at);

                    $meta = match($type) {
                        'follow'           => ['bi-person-plus-fill',  '#155eef', 'rgba(127,119,221,.12)'],
                        'new_bid'          => ['bi-currency-lira',     '#10b981', 'rgba(16,185,129,.12)'],
                        'auction_approved' => ['bi-check-circle-fill', '#22c55e', 'rgba(34,197,94,.12)'],
                        'auction_rejected' => ['bi-x-circle-fill',     '#ef4444', 'rgba(239,68,68,.12)'],
                        'auction_ended'    => ['bi-flag-fill',         '#6b7280', 'rgba(107,114,128,.12)'],
                        'buy_now'          => ['bi-lightning-fill',    '#f59e0b', 'rgba(245,158,11,.12)'],
                        default            => ['bi-bell-fill',         '#155eef', 'rgba(127,119,221,.12)'],
                    };
                    [$icon, $color, $iconBg] = $meta;

                    $avatarName   = $data['follower_name']   ?? $data['bidder_name']   ?? $data['buyer_name']   ?? null;
                    $avatarImg    = $data['follower_avatar']  ?? $data['bidder_avatar']  ?? $data['buyer_avatar']  ?? null;
                    $avatarUser   = $data['follower_username']?? $data['bidder_username']?? $data['buyer_username']?? null;

                    $link = match($type) {
                        'follow'                        => $avatarUser ? route('profile.public', $avatarUser) : '#',
                        'new_bid','auction_approved',
                        'auction_rejected','auction_ended',
                        'buy_now'                       => isset($data['auction_slug']) ? route('seller.auctions.show', $data['auction_slug']) : '#',
                        default                         => '#',
                    };
                @endphp

                <a href="{{ $link }}"
                   class="notif-card {{ $unread ? 'unread' : '' }}">

                    <div class="notif-avatar-wrap">
                        @if($avatarImg)
                            <img src="{{ $avatarImg }}" class="notif-avatar-img" alt="">
                        @elseif($avatarName)
                            <div class="notif-avatar-letter" style="background:#155eef;">
                                {{ strtoupper(mb_substr($avatarName, 0, 1)) }}
                            </div>
                        @else
                            <div class="notif-avatar-letter" style="background:{{ $iconBg }};color:{{ $color }};">
                                <i class="bi {{ $icon }}"></i>
                            </div>
                        @endif

                        <div class="notif-type-badge" style="background:{{ $color }};">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                    </div>

                    <div class="notif-body">
                        <span class="notif-text {{ $unread ? 'fw-semibold' : '' }}">
                            {{ $data['message'] }}
                        </span>
                        @if(!empty($data['reason']))
                            <span class="notif-reason">{{ $data['reason'] }}</span>
                        @endif
                        <span class="notif-time">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>

                    @if($unread)
                        <div class="notif-unread-dot"></div>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif

</div>
@endsection
