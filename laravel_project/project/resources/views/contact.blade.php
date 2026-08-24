@extends('layouts.app')
@section('title', 'İletişim')
@section('content')

<div class="container py-6">

    <div class="mb-6">
        <h2 class="fw-bold text-muted mb-1">İletişim</h2>
        <span class="text-muted fs-6">Sorularınız için aşağıdaki kanallardan bize ulaşabilirsiniz.</span>
    </div>

    <div class="row g-5">

        <div class="col-lg-7">
            <div class="auction-card p-5 h-100">

                <h5 class="text-white fw-bold mb-1">Mesaj Gönderin</h5>
                <p class="text-muted fs-7 mb-5">En geç 24 saat içinde yanıt veririz.</p>

                @if(session('contact_success'))
                <div class="alert alert-success d-flex align-items-center p-4 mb-4">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <span>Mesajınız iletildi. En kısa sürede dönüş yapacağız.</span>
                </div>
                @endif

                <form action="{{ route('contact.send') }}" method="POST" novalidate>
                    @csrf

                    <div class="row g-4">
                        <div class="col-sm-6">
                            <label class="form-label text-muted fw-semibold fs-7">Ad Soyad</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control form-control-solid @error('name') is-invalid @enderror"
                                placeholder="Adınız Soyadınız"
                                value="{{ old('name') }}"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label text-muted fw-semibold fs-7">E-posta</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control form-control-solid @error('email') is-invalid @enderror"
                                placeholder="ornek@mail.com"
                                value="{{ old('email') }}"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted fw-semibold fs-7">Konu</label>
                            <select name="subject" class="form-select form-select-solid @error('subject') is-invalid @enderror">
                                <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Konu seçin</option>
                                <option value="genel"     {{ old('subject') == 'genel'     ? 'selected' : '' }}>Genel Bilgi</option>
                                <option value="teknik"    {{ old('subject') == 'teknik'    ? 'selected' : '' }}>Teknik Destek</option>
                                <option value="odeme"     {{ old('subject') == 'odeme'     ? 'selected' : '' }}>Ödeme / Fatura</option>
                                <option value="sikayet"   {{ old('subject') == 'sikayet'   ? 'selected' : '' }}>Şikayet</option>
                                <option value="isbirligi" {{ old('subject') == 'isbirligi' ? 'selected' : '' }}>İş Birliği</option>
                            </select>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted fw-semibold fs-7">Mesajınız</label>
                            <textarea
                                name="message"
                                rows="5"
                                class="form-control form-control-solid @error('message') is-invalid @enderror"
                                placeholder="Mesajınızı buraya yazın..."
                            >{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-send me-2"></i>Mesaj Gönder
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        {{-- BİLGİ KARTLARI --}}
        <div class="col-lg-5 d-flex flex-column gap-3">

            <div class="auction-card p-4 d-flex align-items-start gap-4">
                <div class="bg-primary bg-opacity-10 rounded-2 p-3 flex-shrink-0">
                    <i class="bi bi-envelope fs-3 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted fs-7 fw-semibold mb-1">E-posta</div>
                    <div class="text-white fw-bold fs-6">{{ setting('contact_email') }}</div>
                    <div class="text-muted fs-7">Genel sorular</div>
                </div>
            </div>

            <div class="auction-card p-4 d-flex align-items-start gap-4">
                <div class="bg-primary bg-opacity-10 rounded-2 p-3 flex-shrink-0">
                    <i class="bi bi-headset fs-3 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted fs-7 fw-semibold mb-1">Destek</div>
                    <div class="text-white fw-bold fs-6">{{ setting('support_email') }}</div>
                    <div class="text-muted fs-7">Teknik & sipariş desteği</div>
                </div>
            </div>

            <div class="auction-card p-4 d-flex align-items-start gap-4">
                <div class="bg-primary bg-opacity-10 rounded-2 p-3 flex-shrink-0">
                    <i class="bi bi-globe fs-3 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted fs-7 fw-semibold mb-1">Web Sitesi</div>
                    <div class="text-white fw-bold fs-6">{{ setting('site_url') }}</div>
                    <div class="text-muted fs-7">{{ setting('site_name') }}</div>
                </div>
            </div>

            {{-- SOSYAL MEDYA --}}
            <div class="auction-card p-4">
                <div class="text-muted fs-7 fw-semibold mb-3">Sosyal Medya</div>
                <div class="d-flex gap-3 flex-wrap">
                    @php
                        $socials = [
                            ['icon' => 'bi-instagram', 'label' => 'Instagram', 'href' => '#'],
                            ['icon' => 'bi-twitter-x', 'label' => 'X',         'href' => '#'],
                            ['icon' => 'bi-facebook',  'label' => 'Facebook',  'href' => '#'],
                            ['icon' => 'bi-youtube',   'label' => 'YouTube',   'href' => '#'],
                        ];
                    @endphp
                    @foreach($socials as $s)
                    <a href="{{ $s['href'] }}" target="_blank" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                        <i class="bi {{ $s['icon'] }}"></i>{{ $s['label'] }}
                    </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</div>

@endsection
