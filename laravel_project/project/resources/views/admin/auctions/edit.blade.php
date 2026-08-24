@extends('layouts.app')
@section('title', 'İlanı Düzenle')
@section('content')

<div class="au-page-wrap">

    {{-- Toolbar --}}
    <div class="au-page-head">
        <div class="au-head-left">
            <a href="{{ route('admin.auctions.show', $auction) }}" class="au-back-link">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="au-page-title">İlanı Düzenle</h1>
            <span class="pf-role-badge">🛡 Admin</span>
        </div>
    </div>

    {{-- Hatalar --}}
    @if($errors->any())
        <div class="au-card au-error-card mb-3">
            <div class="au-error-body">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $errors->count() }} hata var, lütfen düzelt.
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.auctions.update', $auction) }}">
        @csrf @method('PUT')

        {{-- Ürün Bilgileri --}}
        <div class="au-card">
            <div class="au-card-body">
                <div class="au-section-label">Ürün Bilgileri</div>

                <div class="pf-field">
                    <label class="pf-label">Başlık <span class="pf-req">*</span></label>
                    <input class="pf-input" type="text" name="title"
                           value="{{ old('title', $auction->title) }}" maxlength="120">
                    @error('title') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label">Açıklama <span class="pf-req">*</span></label>
                    <textarea class="pf-input" name="description" rows="5"
                              maxlength="5000">{{ old('description', $auction->description) }}</textarea>
                    @error('description') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Kategori</label>
                        <select class="pf-input" name="category_id">
                            <option value="">— Seç —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $auction->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Ürün Durumu <span class="pf-req">*</span></label>
                        <select class="pf-input" name="condition">
                            @foreach(['new' => 'Sıfır', 'used' => 'İkinci El', 'refurbished' => 'Yenilenmiş'] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ old('condition', $auction->condition) === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                        @error('condition') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Konum</label>
                        <input class="pf-input" type="text" name="location"
                               value="{{ old('location', $auction->location) }}"
                               placeholder="Örn: İstanbul, Kadıköy">
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Durum <span class="pf-req">*</span></label>
                        <select class="pf-input" name="status">
                            @foreach([
                                'draft'     => 'Bekliyor',
                                'active'    => 'Aktif',
                                'rejected'  => 'Reddedildi',
                                'ended'     => 'Bitti',
                                'cancelled' => 'İptal',
                                'sold'      => 'Satıldı',
                            ] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ old('status', $auction->status) === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Fiyatlandırma --}}
        <div class="au-card">
            <div class="au-card-body">
                <div class="au-section-label">Fiyatlandırma</div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Başlangıç fiyatı (₺) <span class="pf-req">*</span></label>
                        <input class="pf-input" type="number" name="starting_price"
                               value="{{ old('starting_price', $auction->starting_price) }}"
                               min="1" step="0.01">
                        @error('starting_price') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Min. teklif artışı (₺) <span class="pf-req">*</span></label>
                        <input class="pf-input" type="number" name="min_bid_increment"
                               value="{{ old('min_bid_increment', $auction->min_bid_increment) }}"
                               min="1" step="0.01">
                        @error('min_bid_increment') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Gizli taban fiyat (₺)</label>
                        <input class="pf-input" type="number" name="reserve_price"
                               value="{{ old('reserve_price', $auction->reserve_price) }}"
                               min="0" step="0.01" placeholder="İsteğe bağlı">
                        <div class="pf-hint">Alıcılar görmez.</div>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Hemen al fiyatı (₺)</label>
                        <input class="pf-input" type="number" name="buy_now_price"
                               value="{{ old('buy_now_price', $auction->buy_now_price) }}"
                               min="0" step="0.01" placeholder="İsteğe bağlı">
                        @error('buy_now_price') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Zamanlama --}}
        <div class="au-card">
            <div class="au-card-body">
                <div class="au-section-label">Zamanlama</div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Başlangıç <span class="pf-req">*</span></label>
                        <input class="pf-input" type="datetime-local" name="starts_at"
                               value="{{ old('starts_at', $auction->starts_at->format('Y-m-d\TH:i')) }}">
                        @error('starts_at') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Bitiş <span class="pf-req">*</span></label>
                        <input class="pf-input" type="datetime-local" name="ends_at"
                               value="{{ old('ends_at', $auction->ends_at->format('Y-m-d\TH:i')) }}">
                        @error('ends_at') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="au-footer">
            <a href="{{ route('admin.auctions.show', $auction) }}" class="pf-btn-reset">İptal</a>
            <button type="submit" class="pf-btn-save">
                <i class="bi bi-check-lg me-1"></i> Kaydet
            </button>
        </div>

    </form>
</div>
@endsection
