@extends('layouts.app')
@section('title', 'İlanı Düzenle')

@section('content')
<div class="au-page-wrap">

    <div class="au-page-head">
        <div class="au-head-left">
            <a href="{{ route('seller.auctions.show', $auction) }}" class="au-back-link">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="au-page-title">İlanı Düzenle</h1>
        </div>
    </div>

    @if(session('profile_success'))
        <div class="pf-alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('profile_success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="au-card au-error-card mb-3">
            <div class="au-error-body">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $errors->count() }} hata var.
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('seller.auctions.update', $auction) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- MEVCUT GÖRSELLER --}}
        <div class="au-card">
            <div class="au-card-body">
                <div class="pf-label mb-2">Mevcut Görseller</div>
                <div class="au-img-grid">
                    @foreach($auction->images as $img)
                    <div class="au-img-item">
                        <img src="{{ $img->url() }}" class="{{ $img->is_cover ? 'au-img-cover-ring' : '' }}">
                        @if($img->is_cover)
                            <span class="au-img-cover-badge">Kapak</span>
                        @endif
                        <label class="au-img-delete-label">
                            <input type="checkbox" name="delete_images[]"
                                   value="{{ $img->id }}" class="d-none"
                                   onchange="this.closest('.au-img-item').style.opacity=this.checked?'.3':'1'">
                            <span class="au-img-delete-btn"><i class="bi bi-x"></i></span>
                        </label>
                    </div>
                    @endforeach
                </div>
                <div class="pf-hint mt-2">X işaretli görseller kaydedince silinir.</div>

                <div class="pf-label mt-3 mb-1">Yeni Görsel Ekle</div>
                <input type="file" name="new_images[]" class="pf-input"
                       accept=".png,.jpg,.jpeg,.webp" multiple>
            </div>
        </div>

        {{-- ÜRÜN BİLGİLERİ --}}
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
                    <textarea class="pf-input" name="description" rows="4"
                              maxlength="5000">{{ old('description', $auction->description) }}</textarea>
                    @error('description') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Kategori</label>
                        <select class="pf-input js-select2" name="category_id" data-placeholder="Kategori seçin" data-allow-clear="1">
                            <option value=""></option>
                            @include('partials.category-select-options', ['nodes' => \App\Models\Category::tree(), 'selected' => old('category_id', $auction->category_id)])
                        </select>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Ürün Durumu</label>
                        <select class="pf-input" name="condition">
                            <option value="new"         {{ old('condition', $auction->condition) === 'new'         ? 'selected' : '' }}>Sıfır</option>
                            <option value="used"        {{ old('condition', $auction->condition) === 'used'        ? 'selected' : '' }}>İkinci El</option>
                            <option value="refurbished" {{ old('condition', $auction->condition) === 'refurbished' ? 'selected' : '' }}>Yenilenmiş</option>
                        </select>
                    </div>
                </div>

                <div class="pf-field">
                    <label class="pf-label">Konum</label>
                    <input class="pf-input" type="text" name="location"
                           value="{{ old('location', $auction->location) }}" placeholder="İstanbul, Kadıköy">
                </div>
            </div>
        </div>

        {{-- FİYATLANDIRMA --}}
        <div class="au-card">
            <div class="au-card-body">
                <div class="au-section-label">Fiyatlandırma</div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Başlangıç fiyatı (₺)</label>
                        <input class="pf-input" type="number" name="starting_price"
                               value="{{ old('starting_price', $auction->starting_price) }}"
                               {{ $auction->bidCount() > 0 ? 'disabled' : '' }}
                               min="1" step="0.01">
                        @if($auction->bidCount() > 0)
                            <div class="pf-hint">Teklif alındığı için değiştirilemez.</div>
                        @endif
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Min. teklif artışı (₺)</label>
                        <input class="pf-input" type="number" name="min_bid_increment"
                               value="{{ old('min_bid_increment', $auction->min_bid_increment) }}"
                               min="1" step="0.01">
                    </div>
                </div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Gizli taban fiyat (₺)</label>
                        <input class="pf-input" type="number" name="reserve_price"
                               value="{{ old('reserve_price', $auction->reserve_price) }}"
                               min="0" step="0.01">
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Hemen al fiyatı (₺)</label>
                        <input class="pf-input" type="number" name="buy_now_price"
                               value="{{ old('buy_now_price', $auction->buy_now_price) }}"
                               min="0" step="0.01">
                    </div>
                </div>
            </div>
        </div>

        {{-- ZAMANLAMA --}}
        <div class="au-card">
            <div class="au-card-body">
                <div class="au-section-label">Zamanlama</div>
                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Başlangıç</label>
                        <input class="pf-input" type="datetime-local" name="starts_at"
                               value="{{ old('starts_at', $auction->starts_at->format('Y-m-d\TH:i')) }}">
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

        <div class="au-footer">
            <a href="{{ route('seller.auctions.show', $auction) }}" class="pf-btn-reset">İptal</a>
            <button type="submit" class="pf-btn-save">
                <i class="bi bi-floppy me-1"></i> Kaydet
            </button>
        </div>
    </form>
</div>
@endsection
