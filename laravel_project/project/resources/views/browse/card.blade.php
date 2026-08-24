<a href="{{ route('auctions.show', $auction) }}" class="idx-card">
    <div class="idx-card-img">
        <img src="{{ $auction->cover?->url() ?? asset('assets/media/placeholder.svg') }}"
             alt="{{ $auction->title }}"
             loading="eager">

        @if($auction->isActive())
            <div class="idx-live-badge"><span class="dot"></span> CANLI</div>
        @else
            <div class="idx-ended-badge">BİTTİ</div>
        @endif

        <div class="idx-price-overlay">{{ $auction->displayPrice() }}</div>
    </div>

    <div class="idx-card-body">
        <div class="idx-card-title">{{ $auction->title }}</div>

        <div class="idx-card-meta">
            @if($auction->category)
                <span><i class="bi bi-tag"></i>{{ $auction->category->name }}</span>
            @endif
            <span><i class="bi bi-chat-square"></i>{{ $auction->bidCount() }} teklif</span>
            @if($auction->location)
                <span><i class="bi bi-geo-alt"></i>{{ Str::limit($auction->location, 18) }}</span>
            @endif
        </div>

        <div class="idx-card-bottom">
            <div>
                <div class="idx-bid-lbl">Güncel Teklif</div>
                <div class="idx-bid-val">{{ $auction->displayPrice() }}</div>
            </div>
            <div>
                <div class="idx-timer-lbl">Kalan</div>
                <div class="idx-timer-val" data-ends="{{ $auction->ends_at->timestamp }}">
                    {{ $auction->timeLeft() }}
                </div>
            </div>
        </div>
    </div>
</a>
