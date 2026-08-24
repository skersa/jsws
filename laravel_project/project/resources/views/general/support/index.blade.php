@extends('layouts.app')
@section('title', 'Destek Taleplerim')

@section('content')
<div class="container py-4">

    <div class="admin-toolbar mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="toolbar-title">Destek Taleplerim</div>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}" class="pf-breadcrumb-link">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active">Destek</li>
                </ol></nav>
            </div>
            <a href="{{ route('support.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Yeni Talep
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert-au success mb-3">{{ session('success') }}</div>
    @endif

    {{-- Yardım Makaleleri --}}
    <div class="admin-card mb-3">
        <div class="admin-card-head">
            <div class="admin-card-title"><i class="bi bi-lightbulb"></i> Sık Sorulan Sorular</div>
        </div>
        <div class="p-3">
            @foreach([
                ['Nasıl yeni bir destek talebi oluştururum?', '"Yeni Talep" butonuna tıklayarak konu, kategori ve açıklama alanlarını doldurup gönderebilirsiniz. Ekibimiz en kısa sürede yanıt verecektir.'],
                ['Talebimin önceliğini nasıl belirlemeliyim?', 'Ödeme ve fatura sorunları için Yüksek, teknik sorunlar için Orta, genel sorularınız için Düşük öncelik seçebilirsiniz.'],
                ['Kapalı bir talebi yeniden açabilir miyim?', 'Kapatılmış bir talebi yeniden açmak mümkün değildir. Aynı konuda yardıma ihtiyaç duyarsanız lütfen yeni bir talep oluşturun.'],
                ['Yanıt ne kadar sürede gelir?', 'Yüksek öncelikli talepler 2-4 saat içinde, diğer talepler ise 1 iş günü içinde yanıtlanmaktadır.'],
            ] as [$soru, $cevap])
            <div class="support-faq-item">
                <button class="support-faq-btn" type="button" data-faq>
                    <span>{{ $soru }}</span>
                    <i class="bi bi-chevron-down support-faq-icon"></i>
                </button>
                <div class="support-faq-body">
                    <p class="pf-hint mb-0">{{ $cevap }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Talepler Tablosu --}}
    <div class="admin-card">
        <div class="admin-card-head">
            <div class="admin-card-title"><i class="bi bi-headset"></i> Talepler</div>
            <span class="a-badge info">{{ $tickets->total() }} talep</span>
        </div>

        @if($tickets->isEmpty())
        <div class="fl-empty">
            <div class="fl-empty-icon"><i class="bi bi-inbox"></i></div>
            <div class="fl-empty-title">Henüz destek talebiniz yok</div>
            <div class="fl-empty-sub">Bir sorunla karşılaştığınızda yeni talep oluşturabilirsiniz.</div>
        </div>
        @else
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Konu</th>
                        <th>Kategori</th>
                        <th>Öncelik</th>
                        <th>Durum</th>
                        <th>Son Güncelleme</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td class="text-muted fs-xs">#{{ $ticket->id }}</td>
                        <td>
                            <div class="admin-info-val">{{ Str::limit($ticket->subject, 50) }}</div>
                            @if($ticket->lastMessage)
                            <div class="pf-hint mt-0">{{ Str::limit($ticket->lastMessage->body, 60) }}</div>
                            @endif
                        </td>
                        <td class="text-muted">{{ ucfirst($ticket->category) }}</td>
                        <td><span class="a-badge {{ $ticket->priorityBadge() }}">{{ $ticket->priorityLabel() }}</span></td>
                        <td><span class="a-badge {{ $ticket->statusBadge() }}">{{ $ticket->statusLabel() }}</span></td>
                        <td class="text-muted">{{ $ticket->updated_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('support.show', $ticket) }}" class="btn btn-outline-primary btn-sm">
                                Görüntüle
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
        <div class="fl-pagination border-top">
            {{ $tickets->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/support-index.js') }}"></script>
@endpush

