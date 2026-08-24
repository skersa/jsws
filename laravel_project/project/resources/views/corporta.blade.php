@extends('layouts.app')

@section('title', 'Hakkımızda')

@section('content')

<div class="container py-6">

    <div class="mb-6">
        <h2 class="fw-bold text-muted mb-1">Hakkımızda</h2>
        <span class="text-muted fs-6">{{ setting('site_description') }}</span>
    </div>

    <div class="row g-4 mb-6">
        <div class="col-md-6">
            <div class="auction-card h-100 p-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                        <i class="bi bi-bullseye fs-2 text-primary"></i>
                    </div>
                    <h4 class="text-white mb-0 fw-bold">Misyonumuz</h4>
                </div>
                <p class="text-muted fs-6 lh-lg mb-0">
                    Alıcı ve satıcıları gerçek zamanlı, güvenilir bir platformda buluşturarak açık artırma kültürünü Türkiye'de yaygınlaştırmak. Her işlemde şeffaflık ve adil rekabeti ön planda tutuyoruz.
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="auction-card h-100 p-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                        <i class="bi bi-eye fs-2 text-primary"></i>
                    </div>
                    <h4 class="text-white mb-0 fw-bold">Vizyonumuz</h4>
                </div>
                <p class="text-muted fs-6 lh-lg mb-0">
                    Teknoloji odaklı altyapımızla bölgenin en büyük dijital müzayede ekosistemi olmak. Kullanıcılarımıza her kategoride uçtan uca bir teklif deneyimi sunmak.
                </p>
            </div>
        </div>
    </div>

    <h5 class="section-title mb-4">Rakamlarla {{ setting('site_name') }}</h5>
    <div class="row g-3 mb-6">
        @php
            $stats = [
                ['icon' => 'bi-people',       'value' => '50.000+',  'label' => 'Kayıtlı Kullanıcı'],
                ['icon' => 'bi-hammer',        'value' => '120.000+', 'label' => 'Tamamlanan Müzayede'],
                ['icon' => 'bi-shield-check',  'value' => '%99,8',    'label' => 'Güvenli İşlem Oranı'],
                ['icon' => 'bi-clock-history', 'value' => '7/24',     'label' => 'Canlı Destek'],
            ];
        @endphp
        @foreach($stats as $s)
        <div class="col-6 col-md-3">
            <div class="auction-card text-center p-4 h-100">
                <i class="bi {{ $s['icon'] }} fs-2x text-primary mb-3 d-block"></i>
                <div class="fw-bold fs-2 text-white mb-1">{{ $s['value'] }}</div>
                <div class="text-muted fs-7">{{ $s['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <h5 class="section-title mb-4">Neden {{ setting('site_name') }}?</h5>
    <div class="row g-3 mb-6">
        @php
            $features = [
                ['icon' => 'bi-lightning-charge', 'title' => 'Anlık Teklifler',    'desc' => 'Tüm teklifler milisaniyeler içinde sisteme yansır, hiçbir fırsatı kaçırmazsınız.'],
                ['icon' => 'bi-lock',              'title' => 'Güvenli Ödeme',      'desc' => 'SSL korumalı altyapı ve doğrulanmış satıcı sistemiyle her işleminiz güvende.'],
                ['icon' => 'bi-bell',              'title' => 'Akıllı Bildirimler', 'desc' => 'Takip ettiğiniz ürünlerde teklif geldiğinde anında bildirim alın.'],
                ['icon' => 'bi-phone',             'title' => 'Mobil Uyumlu',       'desc' => 'Her cihazdan kesintisiz teklif verin, müzayedeyi dilediğiniz yerden takip edin.'],
                ['icon' => 'bi-bar-chart',         'title' => 'Fiyat Geçmişi',      'desc' => 'Benzer ürünlerin geçmiş satış fiyatlarını inceleyerek bilinçli karar verin.'],
                ['icon' => 'bi-headset',           'title' => '7/24 Destek',        'desc' => 'Sorularınız için destek ekibimiz her zaman yanınızda: ' . setting('support_email')],
            ];
        @endphp
        @foreach($features as $f)
        <div class="col-md-4 col-sm-6">
            <div class="auction-card p-4 h-100">
                <div class="bg-primary bg-opacity-10 rounded-2 p-2 d-inline-flex mb-3">
                    <i class="bi {{ $f['icon'] }} fs-4 text-primary"></i>
                </div>
                <h6 class="text-white fw-bold mb-2">{{ $f['title'] }}</h6>
                <p class="text-muted fs-7 mb-0 lh-lg">{{ $f['desc'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="auction-card p-6 text-center">
        <h3 class="text-white fw-bold mb-3">Hemen Başlayın</h3>
        <p class="text-muted mb-4">Ücretsiz hesap oluşturun, binlerce ürün arasından teklif verin.</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="{{ route('register') }}" class="btn btn-primary px-5">Ücretsiz Kayıt Ol</a>
            <a href="#" class="btn btn-outline-light px-5">Müzayedeleri Gör</a>
        </div>
    </div>
</div>

@endsection
