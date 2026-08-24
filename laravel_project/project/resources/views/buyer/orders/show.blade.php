@extends('layouts.app')
@section('title', 'Sipariş ' . $order->order_number)

@section('content')
<div class="dash-wrap py-4">

    <div class="admin-toolbar dash-hero">
        <div>
            <div class="toolbar-title">Sipariş {{ $order->order_number }}</div>
            <div class="dash-hero-sub">{{ $order->auction?->title }}</div>
        </div>
        <a href="{{ route('orders.index') }}" class="btn-admin-ghost"><i class="bi bi-arrow-left"></i> Siparişlerim</a>
    </div>

    @if(session('success'))<div class="alert alert-success" style="border-radius:12px">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" style="border-radius:12px">{{ session('error') }}</div>@endif

    <div class="admin-card" style="margin-bottom:16px">
        @include('partials.order-progress', ['order' => $order])
    </div>

    <div class="ord-grid">
        {{-- SOL: aksiyonlar --}}
        <div>
            {{-- Ödeme --}}
            @if($order->status === 'awaiting_payment')
            <div class="ord-box" data-testid="order-pay-box">
                <div class="ord-box-title"><i class="bi bi-wallet2"></i> Ödeme Gerekli</div>
                <p class="pf-text-muted-sm" style="margin-bottom:12px">Kazandığınız ürünün tutarı güvenli <strong>emanet</strong> hesabında tutulur ve ürün elinize ulaşıp onayladığınızda satıcıya aktarılır. Böylece hem siz hem satıcı korunur.</p>
                <div class="ord-total" style="margin-bottom:14px"><span>Ödenecek</span><span>{{ number_format($order->amount, 0, ',', '.') }} ₺</span></div>
                <div class="pf-text-muted-sm" style="margin-bottom:12px">Bakiyeniz: <strong>{{ number_format(auth()->user()->balance, 0, ',', '.') }} ₺</strong></div>
                <form method="POST" action="{{ route('orders.pay', $order) }}">
                    @csrf
                    <button type="submit" class="btn-admin-pri" style="width:100%" data-testid="order-pay-btn"><i class="bi bi-shield-lock me-1"></i> Öde ve Emanete Al</button>
                </form>
            </div>
            @endif

            {{-- Teslimat adresi --}}
            @if(in_array($order->status, ['awaiting_payment','paid']))
            <div class="ord-box" data-testid="order-address-box">
                <div class="ord-box-title"><i class="bi bi-geo-alt"></i> Teslimat Adresi</div>
                <form method="POST" action="{{ route('orders.address', $order) }}">
                    @csrf
                    <div class="ord-field"><label>Ad Soyad</label><input name="recipient_name" value="{{ old('recipient_name', $order->recipient_name) }}" required data-testid="addr-name"></div>
                    <div class="ord-field"><label>Telefon</label><input name="recipient_phone" value="{{ old('recipient_phone', $order->recipient_phone) }}" required data-testid="addr-phone"></div>
                    <div class="ord-field"><label>Adres</label><textarea name="address_line" rows="2" required data-testid="addr-line">{{ old('address_line', $order->shipping_address) }}</textarea></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                        <div class="ord-field"><label>İl</label><input name="address_city" value="{{ old('address_city', $order->address_city) }}" required data-testid="addr-city"></div>
                        <div class="ord-field"><label>İlçe</label><input name="address_district" value="{{ old('address_district', $order->address_district) }}"></div>
                        <div class="ord-field"><label>Posta Kodu</label><input name="address_zip" value="{{ old('address_zip', $order->address_zip) }}"></div>
                    </div>
                    <button type="submit" class="btn-admin-pri" style="width:100%" data-testid="addr-save-btn"><i class="bi bi-save me-1"></i> Adresi Kaydet</button>
                </form>
            </div>
            @endif

            {{-- Kargo takibi --}}
            @if(in_array($order->status, ['shipped','delivered','completed']) && $order->tracking_number)
            <div class="ord-box" data-testid="order-tracking-box">
                <div class="ord-box-title"><i class="bi bi-truck"></i> Kargo Takibi</div>
                <div class="ord-info-row"><span class="k">Kargo Firması</span><span class="v">{{ $order->carrier }}</span></div>
                <div class="ord-info-row"><span class="k">Takip No</span><span class="v">{{ $order->tracking_number }}</span></div>
                @if($order->shipped_at)<div class="ord-info-row"><span class="k">Gönderim</span><span class="v">{{ $order->shipped_at->format('d.m.Y H:i') }}</span></div>@endif
                @if($order->tracking_url)<a href="{{ $order->tracking_url }}" target="_blank" class="ord-track-badge" style="margin-top:10px"><i class="bi bi-box-arrow-up-right"></i> Kargoyu Takip Et</a>@endif
            </div>
            @endif

            {{-- Teslim onayı + sorun bildir --}}
            @if($order->status === 'shipped')
            <div class="ord-box" data-testid="order-confirm-box">
                <div class="ord-box-title"><i class="bi bi-box-seam"></i> Teslimat Onayı</div>
                <p class="pf-text-muted-sm" style="margin-bottom:12px">Ürünü elinize ulaştıysa onaylayın; ödeme satıcıya aktarılacaktır. @if($order->auto_release_at)Onaylamazsanız <strong>{{ $order->auto_release_at->format('d.m.Y') }}</strong> tarihinde otomatik tamamlanır.@endif</p>
                <form method="POST" action="{{ route('orders.confirm', $order) }}" onsubmit="return confirm('Ürünü teslim aldığınızı onaylıyor musunuz? Ödeme satıcıya aktarılacak.')">
                    @csrf
                    <button type="submit" class="btn-admin-pri" style="width:100%;margin-bottom:10px" data-testid="order-confirm-btn"><i class="bi bi-check-circle me-1"></i> Teslim Aldım, Onayla</button>
                </form>
                <button type="button" class="btn-admin-ghost" style="width:100%" onclick="document.getElementById('disputeBox').style.display='block'" data-testid="order-dispute-open"><i class="bi bi-exclamation-triangle me-1"></i> Bir sorun mu var?</button>
            </div>
            @endif

            {{-- Anlaşmazlık formu --}}
            @if(in_array($order->status, ['paid','shipped','delivered']))
            <div class="ord-box" id="disputeBox" style="display:{{ $errors->has('reason') ? 'block' : 'none' }}" data-testid="order-dispute-box">
                <div class="ord-box-title"><i class="bi bi-exclamation-octagon"></i> Sorun Bildir / Anlaşmazlık</div>
                <form method="POST" action="{{ route('orders.dispute', $order) }}">
                    @csrf
                    <div class="ord-field"><textarea name="reason" rows="3" placeholder="Yaşadığınız sorunu detaylıca yazın (örn: ürün hasarlı geldi)..." required data-testid="dispute-reason">{{ old('reason') }}</textarea>@error('reason')<div class="text-danger" style="font-size:12px">{{ $message }}</div>@enderror</div>
                    <button type="submit" class="btn-admin-danger" style="width:100%" data-testid="dispute-submit-btn">Anlaşmazlık Aç</button>
                </form>
            </div>
            @endif

            @if($order->status === 'disputed')
            <div class="ord-box" style="border-color:rgba(239,68,68,.4)">
                <div class="ord-box-title" style="color:#f87171"><i class="bi bi-exclamation-octagon"></i> Anlaşmazlık İnceleniyor</div>
                <p class="pf-text-muted-sm">Talebiniz ekibimize iletildi. En kısa sürede sonuçlandırılacaktır.</p>
                <div class="ord-info-row"><span class="k">Sebep</span><span class="v">{{ $order->dispute_reason }}</span></div>
            </div>
            @endif

            {{-- Sipariş tamamlandığında satıcıyı değerlendir --}}
            @if($order->status === 'completed' && $order->seller)
                @include('partials.review-form', ['seller' => $order->seller, 'existing' => $order->seller->reviewFrom(auth()->user())])
            @endif

            {{-- Zaman çizelgesi --}}
            <div class="ord-box">
                <div class="ord-box-title"><i class="bi bi-clock-history"></i> Sipariş Geçmişi</div>
                @include('partials.order-timeline', ['order' => $order])
            </div>
        </div>

        {{-- SAĞ: özet --}}
        <div>
            <div class="ord-box">
                <img src="{{ $order->auction?->cover?->url() ?? asset('assets/media/placeholder.svg') }}" alt="" style="width:100%;height:170px;object-fit:cover;border-radius:12px;margin-bottom:12px">
                <div style="font-weight:700;font-size:15px;color:var(--text)">{{ $order->auction?->title }}</div>
                <div class="pf-text-muted-sm" style="margin:4px 0 14px">Satıcı: {{ $order->seller?->name }}</div>
                <div class="ord-info-row"><span class="k">Sipariş No</span><span class="v">{{ $order->order_number }}</span></div>
                <div class="ord-info-row"><span class="k">Durum</span><span class="v" style="color:{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></div>
                <div class="ord-info-row"><span class="k">Ürün Tutarı</span><span class="v">{{ number_format($order->amount, 0, ',', '.') }} ₺</span></div>
                <div class="ord-total"><span>Toplam</span><span>{{ number_format($order->amount, 0, ',', '.') }} ₺</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
