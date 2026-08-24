@extends('layouts.app')
@section('title', 'Yeni Kategori')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

@section('content')
<div class="pf-root">
    <div class="pf-top pf-top-padding">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="pf-title-text">Yeni Kategori</div>
                <nav aria-label="breadcrumb" class="mt-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="pf-link-primary">Admin</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.categories.index') }}" class="pf-link-primary">Kategoriler</a>
                        </li>
                        <li class="breadcrumb-item active" class="pf-text-muted">Yeni</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="pf-btn-reset pf-btn-back-custom">
                <i class="bi bi-arrow-left"></i> Geri
            </a>
        </div>
    </div>

    <div class="pf-edit-drawer open">

        <div class="pf-edit-tabs">
            <button class="pf-etab active" onclick="switchETab('genel', this)">
                <i class="bi bi-grid me-1"></i> Genel
            </button>
            <button class="pf-etab" onclick="switchETab('gorsel', this)">
                <i class="bi bi-image me-1"></i> Görsel & Açıklama
            </button>
            <button class="pf-etab" onclick="switchETab('ayarlar', this)">
                <i class="bi bi-sliders me-1"></i> Ayarlar
            </button>
        </div>

        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" id="categoryForm">
            @csrf

            @if ($errors->any())
                <div class="pf-alert-success pf-alert-error-custom">
                    <i class="bi bi-exclamation-circle-fill pf-text-danger"></i>
                    <span class="pf-text-danger">
                        @foreach ($errors->all() as $err)
                            {{ $err }}@if (!$loop->last) · @endif
                        @endforeach
                    </span>
                </div>
            @endif

            <div id="ep-genel" class="pf-epanel active">

                <div class="pf-field">
                    <label class="pf-label">Kategori Adı <span class="pf-req">*</span></label>
                    <input
                        class="pf-input"
                        type="text"
                        name="name"
                        id="catName"
                        value="{{ old('name') }}"
                        placeholder="Örn: Elektronik" required>
                    @error('name')
                        <div class="pf-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label">Slug</label>
                    <div class="pf-input-pre">
                        <span class="pf-pre-label">/</span>
                        <input
                            type="text"
                            name="slug"
                            id="catSlug"
                            value="{{ old('slug') }}"
                            placeholder="otomatik-uretilir"
                            maxlength="191">
                    </div>
                    <div class="pf-hint">Boş bırakırsan ad'dan otomatik üretilir.</div>
                    @error('slug')
                        <div class="pf-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label">Üst Kategori</label>
                    <select
                        name="parent_id"
                        id="parentSelect"
                        class="pf-input js-select2"
                        data-placeholder="— Ana Kategori —"
                        data-allow-clear="1"
                        data-has-error="{{ $errors->has('parent_id') ? '1' : '0' }}">
                        <option value="">— Ana Kategori —</option>
                        @include('partials.category-select-options', ['nodes' => \App\Models\Category::tree(), 'selected' => old('parent_id')])
                    </select>
                    @error('parent_id')
                        <div class="pf-error">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div id="ep-gorsel" class="pf-epanel">

                <div class="pf-avatar-upload-row">
                    <label for="image" class="pf-upload-avatar pf-category-upload-label" title="Görsel seç">
                        <img
                            src="https://ui-avatars.com/api/?name=K&background=155eef&color=fff&size=128&bold=true"
                            alt="Önizleme"
                            id="imgPreview"
                            class="pf-category-preview-img">
                        <input type="file" id="image" name="image" accept=".png,.jpg,.jpeg,.webp" class="d-none">
                    </label>
                    <div>
                        <div class="pf-upload-title">Kategori görseli</div>
                        <div class="pf-upload-desc">PNG, JPG, WEBP · Maks. 2MB</div>
                        <label for="image" class="pf-btn-photo mt-2 d-inline-flex align-items-center gap-1 pf-cursor-pointer">
                            <i class="bi bi-upload"></i> Görsel yükle
                        </label>
                    </div>
                </div>
                @error('image')
                    <div class="pf-error mt-1">{{ $message }}</div>
                @enderror

                <div class="pf-field mt-3">
                    <label class="pf-label">Açıklama</label>
                    <div class="pf-relative">
                        <textarea
                            class="pf-input"
                            name="description"
                            rows="4"
                            maxlength="1000"
                            placeholder="Kategori hakkında kısa açıklama...">{{ old('description') }}</textarea>
                        <span id="desc_counter" class="pf-char-cnt">{{ strlen(old('description', '')) }}/1000</span>
                    </div>
                    @error('description')
                        <div class="pf-error">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div id="ep-ayarlar" class="pf-epanel">

                <div class="pf-field">
                    <label class="pf-label">Sıralama</label>
                    <input
                        class="pf-input"
                        type="number"
                        name="sort_order"
                        value="{{ old('sort_order', 0) }}"
                        min="0"
                        max="9999"
                        placeholder="0">
                    <div class="pf-hint">Küçük değer öne gelir. Varsayılan: 0</div>
                    @error('sort_order')
                        <div class="pf-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="pf-toggle-list">
                    <label class="pf-trow pf-trow-border-none">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Kategoriyi Yayınla</div>
                            <div class="pf-trow-desc">Aktif kategoriler sitede görünür</div>
                        </div>
                        <input type="hidden" name="is_active" value="0">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="pf-tog-input"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                    </label>
                </div>

            </div>

            <div class="pf-footer">
                <span class="pf-save-info">
                    <i class="bi bi-info-circle"></i> Tüm alanları doldurmak zorunda değilsin.
                </span>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="pf-btn-reset">İptal</a>
                    <button type="submit" class="pf-btn-save" id="saveBtn">
                        <i class="bi bi-floppy me-1"></i> Kaydet
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/custom/theme/category.js') }}"></script>
@endpush
