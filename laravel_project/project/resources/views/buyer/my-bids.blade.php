@extends('layouts.app')
@section('title', 'Tekliflerim')

@section('content')
<div class="dash-wrap py-4">

    <div class="admin-toolbar dash-hero">
        <div>
            <div class="toolbar-title">Tekliflerim</div>
            <div class="dash-hero-sub">Verdiğin tüm teklifleri buradan takip edebilirsin.</div>
        </div>
    </div>

    <div class="admin-card" data-testid="my-bids-card">
        @if($bids->isEmpty())
            <div class="pf-empty">
                <div class="pf-empty-icon"><i class="bi bi-hammer"></i></div>
                <div class="pf-empty-title">Henüz teklif vermedin</div>
                <div class="pf-empty-sub">Müzayedelere göz at ve ilk teklifini ver.</div>
                <a href="{{ route('browse.auctions') }}" class="btn-admin-pri dash-mt">Müzayedeleri Keşfet</a>
            </div>
        @else
            <div class="pf-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>İlan</th>
                            <th>Teklifim</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bids as $bid)
                        <tr>
                            <td>
                                <div class="dash-item">
                                    <img class="a-avatar" src="{{ $bid->auction?->cover?->url() ?? asset('assets/media/placeholder.svg') }}" alt="">
                                    <a class="dash-item-title" href="{{ $bid->auction ? route('auctions.show', $bid->auction) : '#' }}">
                                        {{ \Illuminate\Support\Str::limit($bid->auction?->title ?? 'İlan silinmiş', 40) }}
                                    </a>
                                </div>
                            </td>
                            <td class="dash-amount">{{ number_format($bid->amount, 0, ',', '.') }} ₺</td>
                            <td>
                                @if($bid->auction?->status === 'active')
                                    <span class="a-badge success">Aktif</span>
                                @elseif($bid->auction?->status === 'sold')
                                    <span class="a-badge info">Satıldı</span>
                                @else
                                    <span class="a-badge warning">Bitti</span>
                                @endif
                            </td>
                            <td class="dash-muted">{{ $bid->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $bids->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
