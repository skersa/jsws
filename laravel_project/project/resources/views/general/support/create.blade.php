@extends('layouts.app')
@section('title', 'Yeni Destek Talebi')

@section('content')
<div class="container py-4 pf-narrow">

    <div class="admin-toolbar mb-3">
        <div>
            <div class="toolbar-title">Yeni Destek Talebi</div>
            <nav><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('index') }}" class="pf-breadcrumb-link">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('support.index') }}" class="pf-breadcrumb-link">Destek</a></li>
                <li class="breadcrumb-item active">Yeni Talep</li>
            </ol></nav>
        </div>
    </div>

    {{-- Yardım İpuçları Akordiyonu --}}
    <div class="admin-card mb-3">
        <div class="admin-card-head">
            <div class="admin-card-title"><i class="bi bi-info-circle"></i> Talep Oluşturmadan Önce</div>
        </div>
        <div class="p-3">
            @foreach([
                ['Doğru kategoriyi seçin', 'Talebinizin hızlı işleme alınması için en uygun kategoriyi seçtiğinizden emin olun. Yanlış kategori yanıt süresini uzatabilir.'],
                ['Açıklamanızı detaylı yazın', 'Ne zaman, nerede ve nasıl bir sorunla karşılaştığınızı belirtin. Hata mesajı varsa kopyalayarak yapıştırın.'],
                ['Öncelik seviyesi hakkında', 'Öncelik seviyesini gerçekçi tutun. Tüm talepler Yüksek öncelik seçilirse yanıt süreleri uzayabilir.'],
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

    <div class="admin-card">
        <div class="admin-card-head">
            <div class="admin-card-title"><i class="bi bi-plus-circle"></i> Talep Oluştur</div>
        </div>
        <div class="p-4">
            <form action="{{ route('support.store') }}" method="POST">
                @csrf

                <div class="pf-field">
                    <label class="pf-label">Konu <span class="pf-req">*</span></label>
                    <input type="text" name="subject"
                           class="pf-input @error('subject') is-invalid @enderror"
                           value="{{ old('subject') }}"
                           placeholder="Talebinizi kısaca özetleyin"
                           maxlength="150">
                    @error('subject')
                    <div class="pf-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="pf-label">Kategori <span class="pf-req">*</span></label>
                        <select name="category" class="pf-input @error('category') is-invalid @enderror">
                            <option value="">Seçin...</option>
                            <option value="general"   {{ old('category')=='general'   ?'selected':'' }}>Genel</option>
                            <option value="billing"   {{ old('category')=='billing'   ?'selected':'' }}>Ödeme / Fatura</option>
                            <option value="auction"   {{ old('category')=='auction'   ?'selected':'' }}>Müzayede</option>
                            <option value="technical" {{ old('category')=='technical' ?'selected':'' }}>Teknik Sorun</option>
                            <option value="other"     {{ old('category')=='other'     ?'selected':'' }}>Diğer</option>
                        </select>
                        @error('category')
                        <div class="pf-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="pf-label">Öncelik <span class="pf-req">*</span></label>
                        <select name="priority" class="pf-input @error('priority') is-invalid @enderror">
                            <option value="low"    {{ old('priority','medium')=='low'    ?'selected':'' }}>Düşük</option>
                            <option value="medium" {{ old('priority','medium')=='medium' ?'selected':'' }}>Orta</option>
                            <option value="high"   {{ old('priority','medium')=='high'   ?'selected':'' }}>Yüksek</option>
                        </select>
                        @error('priority')
                        <div class="pf-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="pf-field">
                    <label class="pf-label">Açıklama <span class="pf-req">*</span></label>
                    <div class="support-textarea-wrap">
                        <textarea name="body"
                                  class="pf-input @error('body') is-invalid @enderror"
                                  rows="7"
                                  placeholder="Sorununuzu detaylı şekilde açıklayın..."
                                  maxlength="3000">{{ old('body') }}</textarea>
                        <span class="pf-char-cnt"><span id="char-count">0</span>/3000</span>
                    </div>
                    @error('body')
                    <div class="pf-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-send"></i> Talebi Gönder
                    </button>
                    <a href="{{ route('support.index') }}" class="btn btn-outline-primary">İptal</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/support-create.js') }}"></script>
@endpush
