@extends('layouts.app')
@section('title', 'İlan Oluştur')
@section('content')
<div class="au-page-wrap">
    <div class="au-page-head">
        <div class="au-head-left">
            <a href="{{ route('seller.auctions.index') }}" class="au-back-link">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="au-page-title">Yeni İlan Oluştur</h1>
            <span class="pf-role-badge">🏪 Seller</span>
        </div>
    </div>

    @if($errors->any())
        <div class="au-card au-error-card mb-3">
            <div class="au-error-body">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $errors->count() }} hata var, lütfen düzelt.
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('seller.auctions.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="au-card">
            <div class="au-card-body">
                <div class="pf-label mb-2">
                    Görseller <span class="pf-req">*</span>
                    <span class="pf-hint ms-2">İlk görsel kapak olur · Maks. 10 fotoğraf</span>
                </div>
                <div class="au-dropzone"
                     id="dropzone"
                     onclick="document.getElementById('images').click()"
                     ondragover="event.preventDefault();this.classList.add('au-dropzone-hover')"
                     ondragleave="this.classList.remove('au-dropzone-hover')"
                     ondrop="handleDrop(event)">
                    <i class="bi bi-cloud-upload"></i>
                    <div class="au-dropzone-title">Tıkla veya sürükle bırak</div>
                    <div class="au-dropzone-hint">PNG, JPG, WEBP · Her biri maks. 4MB</div>
                </div>
                <input type="file" id="images" name="images[]"
                       accept=".png,.jpg,.jpeg,.webp" multiple class="d-none"
                       onchange="previewImages(this.files)">
                <div class="au-preview-grid" id="previewGrid"></div>
                @error('images')   <div class="pf-error">{{ $message }}</div> @enderror
                @error('images.*') <div class="pf-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="au-card">
            <div class="au-card-body">
                <div class="au-section-label">Ürün Bilgileri</div>

                <div class="pf-field">
                    <label class="pf-label">Başlık <span class="pf-req">*</span></label>
                    <input class="pf-input" type="text" name="title"
                           value="{{ old('title') }}"
                           placeholder="Örn: Vintage Rolex Oyster 1967" maxlength="120">
                    @error('title') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label">Açıklama <span class="pf-req">*</span></label>
                    <textarea class="pf-input" name="description" rows="4" maxlength="5000"
                              placeholder="Ürün hakkında detaylı bilgi, kusur varsa belirt...">{{ old('description') }}</textarea>
                    @error('description') <div class="pf-error">{{ $message }}</div> @enderror
                </div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Kategori</label>
                        <select class="pf-input js-select2" name="category_id" data-placeholder="Kategori seçin" data-allow-clear="1">
                            <option value=""></option>
                            @include('partials.category-select-options', ['nodes' => \App\Models\Category::tree(), 'selected' => old('category_id')])
                        </select>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Ürün Durumu <span class="pf-req">*</span></label>
                        <select class="pf-input" name="condition">
                            <option value="new"         {{ old('condition') === 'new'         ? 'selected' : '' }}>Sıfır</option>
                            <option value="used"        {{ old('condition') === 'used'        ? 'selected' : '' }}>İkinci El</option>
                            <option value="refurbished" {{ old('condition') === 'refurbished' ? 'selected' : '' }}>Yenilenmiş</option>
                        </select>
                        @error('condition') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-field">
                    <label class="pf-label">Konum</label>
                    <input class="pf-input" type="text" name="location"
                           value="{{ old('location') }}" placeholder="Örn: İstanbul, Kadıköy">
                </div>
            </div>
        </div>

        <div class="au-card">
            <div class="au-card-body">
                <div class="au-section-label">Fiyatlandırma</div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Başlangıç fiyatı (₺) <span class="pf-req">*</span></label>
                        <input class="pf-input" type="number" name="starting_price"
                               value="{{ old('starting_price') }}" min="1" step="0.01" placeholder="500">
                        @error('starting_price') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Min. teklif artışı (₺) <span class="pf-req">*</span></label>
                        <input class="pf-input" type="number" name="min_bid_increment"
                               value="{{ old('min_bid_increment', 1) }}" min="1" step="0.01" placeholder="50">
                        @error('min_bid_increment') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Gizli taban fiyat (₺)</label>
                        <input class="pf-input" type="number" name="reserve_price"
                               value="{{ old('reserve_price') }}" min="0" step="0.01" placeholder="İsteğe bağlı">
                        <div class="pf-hint">Alıcılar görmez, bu fiyatın altında satılmaz.</div>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Hemen al fiyatı (₺)</label>
                        <input class="pf-input" type="number" name="buy_now_price"
                               value="{{ old('buy_now_price') }}" min="0" step="0.01" placeholder="İsteğe bağlı">
                        @error('buy_now_price') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="au-card">
            <div class="au-card-body">
                <div class="au-section-label">Zamanlama</div>

                <div class="pf-two-col">
                    <div class="pf-field">
                        <label class="pf-label">Başlangıç <span class="pf-req">*</span></label>
                        <input class="pf-input" type="datetime-local" name="starts_at"
                               value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}">
                        @error('starts_at') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Bitiş <span class="pf-req">*</span></label>
                        <input class="pf-input" type="datetime-local" name="ends_at"
                               value="{{ old('ends_at', now()->addDays(7)->format('Y-m-d\TH:i')) }}">
                        @error('ends_at') <div class="pf-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="au-quick-dates">
                    @foreach([['1 gün','1'],['3 gün','3'],['7 gün','7'],['14 gün','14'],['30 gün','30']] as [$lbl,$d])
                        <button type="button" class="pf-btn-reset" onclick="setEndDate({{ $d }})">{{ $lbl }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="au-footer">
            <a href="{{ url()->previous() }}" class="pf-btn-reset">İptal</a>
            <button type="submit" class="pf-btn-save">
                <i class="bi bi-rocket me-1"></i> İlanı Yayınla
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script src="{{ asset('assets/js/custom/seller-auctions-create.js') }}"></script>
@endpush
@endsection
