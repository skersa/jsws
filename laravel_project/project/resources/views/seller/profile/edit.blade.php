@extends('layouts.app')
@section('title', 'Satıcı Profilim')
@section('content')

<div class="pf-root">

    {{-- ── Üst kart ── --}}
    <div class="pf-top">
        <div class="pf-cover"></div>
        <div class="pf-identity">
            <div class="pf-avatar-wrap">
                <div class="pf-avatar-outer" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-shop" style="font-size:2rem;color:#fff;"></i>
                </div>
            </div>
            <div class="pf-identity-right">
                <div class="pf-uname-row">
                    <span class="pf-uname">{{ auth()->user()->name }}</span>
                    @php
                        $statusMap = [
                            'approved' => ['label'=>'✓ Onaylı Satıcı',  'color'=>'rgba(16,185,129,.15)', 'border'=>'rgba(16,185,129,.3)',  'text'=>'#10b981'],
                            'pending'  => ['label'=>'⏳ İncelemede',     'color'=>'rgba(245,158,11,.15)', 'border'=>'rgba(245,158,11,.3)',  'text'=>'#f59e0b'],
                            'rejected' => ['label'=>'✗ Reddedildi',      'color'=>'rgba(239,68,68,.15)',  'border'=>'rgba(239,68,68,.3)',   'text'=>'#ef4444'],
                        ];
                        $st = $statusMap[$profile->verification_status] ?? $statusMap['pending'];
                    @endphp
                    <span class="pf-role-badge" style="background:{{ $st['color'] }};border-color:{{ $st['border'] }};color:{{ $st['text'] }};">{{ $st['label'] }}</span>
                </div>
                <div class="pf-bio">Satıcı profilinizi buradan yönetebilirsiniz.</div>
            </div>
        </div>

        <div class="pf-stats-row">
            <div class="pf-stat">
                <div class="pf-stat-num">{{ auth()->user()->auctions()->count() }}</div>
                <div class="pf-stat-label">İLAN</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ auth()->user()->auctions()->where('status','active')->count() }}</div>
                <div class="pf-stat-label">AKTİF</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num">{{ $profile->verification_status === 'approved' ? '✓' : '—' }}</div>
                <div class="pf-stat-label">DOĞRULAMA</div>
            </div>
            <div class="pf-stat">
                <div class="pf-stat-num" style="font-size:var(--fs-sm);color:{{ $profile->iban ? '#10b981' : 'var(--muted)' }};">
                    {{ $profile->iban ? 'Tanımlı' : 'Eksik' }}
                </div>
                <div class="pf-stat-label">IBAN</div>
            </div>
        </div>

        <div class="pf-action-row breadcrumb-action-row">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}" class="pf-link-primary">Ana Sayfa</a></li>
                    <li class="breadcrumb-item active pf-text-muted">Satıcı Profilim</li>
                </ol>
            </nav>
            <div class="pf-action-buttons">
                <button type="button" class="pf-btn-save" onclick="submitActiveTab()">
                    <i class="bi bi-floppy me-1"></i> Kaydet
                </button>
            </div>
        </div>
    </div>

    {{-- Ret mesajı ── --}}
    @if($profile->verification_status === 'rejected' && $profile->rejection_reason)
    <div style="display:flex;align-items:flex-start;gap:10px;padding:.8rem 1rem;border-radius:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444;font-size:var(--fs-sm);">
        <i class="bi bi-x-circle-fill" style="flex-shrink:0;margin-top:2px;"></i>
        <div><strong>Başvurunuz reddedildi:</strong> {{ $profile->rejection_reason }}</div>
    </div>
    @endif

    {{-- ── İçerik alanı ── --}}
    <div class="pf-content-area" style="padding:0 0 1rem;">

        <div class="pf-tab-bar wraping">
            <button class="pf-ptab bar-item active" onclick="switchTab('kisisel',this)"><i class="bi bi-person me-1"></i> Kişisel</button>
            <button class="pf-ptab bar-item" onclick="switchTab('sirket',this)"><i class="bi bi-building me-1"></i> Şirket</button>
            <button class="pf-ptab bar-item" onclick="switchTab('odeme',this)"><i class="bi bi-credit-card me-1"></i> Ödeme</button>
            <button class="pf-ptab bar-item" onclick="switchTab('belge',this)"><i class="bi bi-file-earmark-person me-1"></i> Belge</button>
        </div>

        {{-- Flash mesajları --}}
        @foreach(['kisisel','sirket','odeme','belge'] as $sec)
            @if(session('success_'.$sec))
            <div style="display:flex;align-items:center;gap:8px;padding:.65rem 1rem;border-radius:8px;margin:.75rem .75rem 0;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#10b981;font-size:var(--fs-sm);">
                <i class="bi bi-check-circle-fill"></i> {{ session('success_'.$sec) }}
            </div>
            @endif
        @endforeach

        {{-- ── TAB: Kişisel ── --}}
        <div id="tab-kisisel" class="s-panel s-active">
            <form method="POST" action="{{ route('seller.profile.update', 'kisisel') }}" class="s-form" style="padding:1.25rem 1rem .5rem;">
                @csrf @method('PUT')

                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">Ad Soyad <span class="pf-req">*</span></label>
                        <input class="pf-input" type="text" name="name"
                               value="{{ old('name', auth()->user()->name) }}"
                               placeholder="Ad Soyad">
                        @error('name')<div class="pf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">E-posta</label>
                        <input class="pf-input" type="email"
                               value="{{ auth()->user()->email }}"
                               disabled>
                        <div class="s-hint">E-posta değiştirilemez.</div>
                    </div>
                </div>

                <div class="s-field">
                    <label class="s-lbl">Telefon</label>
                    <div class="pf-input-pre">
                        <span class="pf-pre-label">+90</span>
                        <input type="tel" name="phone"
                               value="{{ old('phone', auth()->user()->phone ?? '') }}"
                               placeholder="5xx xxx xx xx">
                    </div>
                    @error('phone')<div class="pf-error">{{ $message }}</div>@enderror
                </div>

                <div class="s-foot">
                    <button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>

        {{-- ── TAB: Şirket ── --}}
        <div id="tab-sirket" class="s-panel">
            <form method="POST" action="{{ route('seller.profile.update', 'sirket') }}" class="s-form" style="padding:1.25rem 1rem .5rem;">
                @csrf @method('PUT')

                <div class="s-hint mb-3" style="padding:.6rem .8rem;border-radius:8px;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);color:var(--muted);">
                    <i class="bi bi-info-circle me-1"></i>
                    Şirket ve vergi bilgileri doğrulama sürecinde kullanılır. Kurumsal satıcılar için zorunludur.
                </div>

                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">Şirket / Marka Adı</label>
                        <input class="pf-input" type="text" name="company_name"
                               value="{{ old('company_name', $profile->company_name) }}"
                               placeholder="Artirdim A.Ş.">
                        @error('company_name')<div class="pf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Vergi / TC Kimlik No</label>
                        <input class="pf-input" type="text" name="tax_number"
                               value="{{ old('tax_number', $profile->tax_number) }}"
                               placeholder="10 veya 11 hane"
                               maxlength="11">
                        @error('tax_number')<div class="pf-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="s-field">
                    <label class="s-lbl">Doğrulama Durumu</label>
                    <div style="display:flex;align-items:center;gap:10px;padding:.7rem .9rem;border-radius:9px;background:var(--bg-soft);border:1px solid var(--border);">
                        <span style="width:9px;height:9px;border-radius:50%;flex-shrink:0;background:{{ $st['text'] }};"></span>
                        <span style="font-size:var(--fs-sm);font-weight:600;color:{{ $st['text'] }};">{{ $st['label'] }}</span>
                        @if($profile->verified_at)
                            <span style="margin-left:auto;font-size:var(--fs-xs);color:var(--muted);">{{ $profile->verified_at->format('d.m.Y') }}</span>
                        @endif
                    </div>
                </div>

                <div class="s-foot">
                    <button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>

        {{-- ── TAB: Ödeme ── --}}
        <div id="tab-odeme" class="s-panel">
            <form method="POST" action="{{ route('seller.profile.update', 'odeme') }}" class="s-form" style="padding:1.25rem 1rem .5rem;">
                @csrf @method('PUT')

                <div class="s-hint mb-3" style="padding:.6rem .8rem;border-radius:8px;background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.2);color:var(--muted);">
                    <i class="bi bi-shield-lock me-1"></i>
                    IBAN bilginiz şifreli olarak saklanır ve yalnızca ödeme transferlerinde kullanılır.
                </div>

                <div class="s-field">
                    <label class="s-lbl">IBAN <span class="pf-req">*</span></label>
                    <div class="pf-input-pre">
                        <span class="pf-pre-label">TR</span>
                        <input type="text" name="iban" id="ibanInput"
                               value="{{ old('iban', $profile->iban ? substr($profile->iban, 2) : '') }}"
                               placeholder="00 0000 0000 0000 0000 0000 00"
                               maxlength="27"
                               oninput="formatIban(this)">
                    </div>
                    @error('iban')<div class="pf-error">{{ $message }}</div>@enderror
                    <div class="s-hint">TR ile başlayan 26 haneli IBAN. Örn: TR33 0006 1005 1978 6457 8413 26</div>
                </div>

                @if($profile->iban)
                <div style="display:flex;align-items:center;gap:8px;padding:.65rem .9rem;border-radius:8px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);">
                    <i class="bi bi-check-circle" style="color:#10b981;"></i>
                    <span style="font-size:var(--fs-sm);color:var(--muted);">Kayıtlı:</span>
                    <span style="font-size:var(--fs-sm);font-weight:700;color:var(--text);letter-spacing:.05em;">
                        {{ substr($profile->iban, 0, 4) }} **** **** **** {{ substr($profile->iban, -4) }}
                    </span>
                </div>
                @endif

                <div class="s-foot">
                    <button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>

        {{-- ── TAB: Belge ── --}}
        <div id="tab-belge" class="s-panel">
            <div class="s-form" style="padding:1.25rem 1rem .5rem;">

                <div class="s-hint mb-3" style="padding:.6rem .8rem;border-radius:8px;background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.2);color:var(--muted);">
                    <i class="bi bi-exclamation-triangle me-1" style="color:#f59e0b;"></i>
                    Kimlik belgesi yüklemeniz satıcı hesabınızın onaylanması için zorunludur. Belge 48 saat içinde incelenir.
                </div>

                @if($profile->id_document_path)
                <div style="display:flex;align-items:center;gap:10px;padding:.75rem 1rem;border-radius:9px;background:var(--bg-soft);border:1px solid var(--border);margin-bottom:1rem;">
                    <i class="bi bi-file-earmark-check" style="font-size:1.4rem;color:#10b981;flex-shrink:0;"></i>
                    <div>
                        <div style="font-weight:600;font-size:var(--fs-sm);">Belge yüklendi</div>
                        <div style="font-size:var(--fs-xs);color:var(--muted);">
                            {{ $profile->updated_at->format('d.m.Y H:i') }} tarihinde güncellendi
                        </div>
                    </div>
                    <span class="a-badge {{ $profile->verification_status === 'approved' ? 'success' : ($profile->verification_status === 'rejected' ? 'danger' : 'warning') }}" style="margin-left:auto;">
                        {{ ['pending'=>'İncelemede','approved'=>'Onaylı','rejected'=>'Reddedildi'][$profile->verification_status] }}
                    </span>
                </div>
                @endif

                <form method="POST" action="{{ route('seller.profile.document.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="s-field">
                        <label class="s-lbl">{{ $profile->id_document_path ? 'Belgeyi Güncelle' : 'Kimlik Belgesi Yükle' }}</label>
                        <label for="id_document" style="display:flex;align-items:center;gap:14px;padding:.9rem 1rem;border-radius:10px;border:1.5px dashed var(--border);background:var(--bg-soft);cursor:pointer;transition:border-color .2s;" id="docUploadZone">
                            <i class="bi bi-cloud-upload" style="font-size:1.6rem;color:var(--muted);flex-shrink:0;" id="docIcon"></i>
                            <div>
                                <div style="font-weight:600;font-size:var(--fs-sm);" id="docLabel">Dosya seç veya sürükle</div>
                                <div style="font-size:var(--fs-xs);color:var(--muted);">JPG, PNG, PDF · Maks. 5MB · Nüfus cüzdanı veya pasaport</div>
                            </div>
                        </label>
                        <input type="file" id="id_document" name="id_document"
                               accept=".jpg,.jpeg,.png,.pdf" class="d-none"
                               onchange="previewDoc(this)">
                        @error('id_document')<div class="pf-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="s-foot">
                        <button type="submit" class="pf-btn-save"><i class="bi bi-cloud-upload me-1"></i> Yükle</button>
                    </div>
                </form>

                {{-- Bilgi notları --}}
                <div style="margin-top:.5rem;">
                    @foreach([
                        ['bi-check2','Belge tüm köşeleri görünür şekilde net çekilmiş olmalı'],
                        ['bi-check2','Ad, soyad ve TC/vergi numarası okunabilir olmalı'],
                        ['bi-check2','Belgeler yalnızca kimlik doğrulama için kullanılır'],
                    ] as [$icon, $text])
                    <div style="display:flex;align-items:center;gap:8px;padding:.4rem 0;font-size:var(--fs-xs);color:var(--muted);">
                        <i class="bi {{ $icon }}" style="color:#10b981;flex-shrink:0;"></i> {{ $text }}
                    </div>
                    @endforeach
                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/custom/seller-profile-edit.js') }}"></script>
@endpush
