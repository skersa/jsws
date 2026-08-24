@extends('layouts.app')
@section('title', 'Site Ayarları')
@section('content')

<div id="adminSettingsRoot" data-test-mail-url="{{ route('admin.settings.test-mail') }}"></div>

<div class="pf-root">

    <div class="pf-top">
        <div class="pf-cover"></div>
        <div class="pf-identity">
            <div class="pf-avatar-wrap">
                <div class="pf-avatar-outer" style="background:linear-gradient(135deg,#155eef 0%,#1e40af 100%);display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-gear-fill" style="font-size:2rem;color:#fff;"></i>
                </div>
            </div>
            <div class="pf-identity-right">
                <div class="pf-uname-row">
                    <span class="pf-uname">Site Ayarları</span>
                    <span class="pf-role-badge">👑 Admin</span>
                </div>
                <div class="pf-bio">Genel site yapılandırması, sözleşmeler ve sistem ayarları</div>
            </div>
        </div>

        <div class="pf-stats-row">
            <div class="pf-stat"><div class="pf-stat-num">{{ \App\Models\User::count() }}</div><div class="pf-stat-label">KULLANICI</div></div>
            <div class="pf-stat"><div class="pf-stat-num">{{ \App\Models\Auction::count() }}</div><div class="pf-stat-label">İLAN</div></div>
            <div class="pf-stat"><div class="pf-stat-num">{{ \App\Models\Bid::count() }}</div><div class="pf-stat-label">TEKLİF</div></div>
            <div class="pf-stat"><div class="pf-stat-num" style="color:#10b981;">●</div><div class="pf-stat-label">AKTİF</div></div>
        </div>

        <div class="pf-action-row breadcrumb-action-row">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="pf-link-primary">Admin</a></li>
                    <li class="breadcrumb-item active pf-text-muted">Ayarlar</li>
                </ol>
            </nav>
            <div class="pf-action-buttons">
                <button type="button" class="pf-btn-save" onclick="submitActiveForm()">
                    <i class="bi bi-floppy me-1"></i> Kaydet
                </button>
            </div>
        </div>
    </div>

    <div class="pf-content-area p-5">

        <div class="pf-tab-bar wraping">
            <button class="pf-ptab bar-item active" style="" onclick="switchSTab('genel',this)"><i class="bi bi-sliders me-1"></i> Genel</button>
            <button class="pf-ptab bar-item"  onclick="switchSTab('seo',this)"><i class="bi bi-search me-1"></i> SEO</button>
            <button class="pf-ptab bar-item"  onclick="switchSTab('kvkk',this)"><i class="bi bi-shield-check me-1"></i> KVKK</button>
            <button class="pf-ptab bar-item"  onclick="switchSTab('gizlilik',this)"><i class="bi bi-file-lock me-1"></i> Gizlilik</button>
            <button class="pf-ptab bar-item"  onclick="switchSTab('kullanim',this)"><i class="bi bi-file-text me-1"></i> Kullanım Koşulları</button>
            <button class="pf-ptab bar-item"  onclick="switchSTab('iletisim',this)"><i class="bi bi-envelope me-1"></i> İletişim</button>
            <button class="pf-ptab bar-item"  onclick="switchSTab('sosyal',this)"><i class="bi bi-share me-1"></i> Sosyal</button>
            <button class="pf-ptab bar-item"  onclick="switchSTab('odeme',this)"><i class="bi bi-credit-card me-1"></i> Ödeme</button>
            <button class="pf-ptab bar-item"  onclick="switchSTab('bakim',this)"><i class="bi bi-tools me-1"></i> Bakım</button>
        </div>

        @if(session('settings_success'))
        <div class="pf-alert-success mt-2">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('settings_success') }}
        </div>
        @endif
        @if(session('settings_error'))
        <div class="pf-alert-danger mt-2">
            <i class="bi bi-x-circle-fill me-2"></i>{{ session('settings_error') }}
        </div>
        @endif

        <div id="sc-genel" class="s-panel s-active">
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="s-form">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="genel">

                <div class="s-logo-row">
                    <div class="s-logo-box" id="logoPreview">
                        @if(setting('site_logo'))
                            <img src="{{ Storage::url(setting('site_logo')) }}" alt="Logo">
                        @else
                            <i class="bi bi-image" style="font-size:1.6rem;color:var(--muted);"></i>
                        @endif
                    </div>
                    <div>
                        <div class="s-hint">PNG, SVG · Transparan arka plan · Maks. 2MB</div>
                        <label for="site_logo" class="pf-btn-photo mt-2 d-inline-flex align-items-center gap-1" style="cursor:pointer;">
                            <i class="bi bi-upload"></i> Logo yükle
                        </label>
                        <input type="file" id="site_logo" name="site_logo" accept=".png,.svg,.jpg,.jpeg,.webp" class="d-none">
                    </div>
                </div>

                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">Site Adı <span class="pf-req">*</span></label>
                        <input class="pf-input" type="text" name="site_name" value="{{ old('site_name', setting('site_name', config('app.name'))) }}" placeholder="Artirdim.com">
                        @error('site_name')<div class="pf-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Site URL <span class="pf-req">*</span></label>
                        <input class="pf-input" type="url" name="site_url" value="{{ old('site_url', setting('site_url', config('app.url'))) }}" placeholder="https://artirdim.com">
                        @error('site_url')<div class="pf-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="s-field">
                    <label class="s-lbl">Site Açıklaması</label>
                    <textarea class="pf-input" name="site_description" rows="2" placeholder="Kısa site açıklaması...">{{ old('site_description', setting('site_description')) }}</textarea>
                </div>

                <hr class="s-hr">
                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">Varsayılan Dil</label>
                        <select class="pf-input" name="default_lang">
                            <option value="tr" {{ setting('default_lang','tr')==='tr'?'selected':'' }}>Türkçe</option>
                            <option value="en" {{ setting('default_lang','tr')==='en'?'selected':'' }}>English</option>
                        </select>
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Zaman Dilimi</label>
                        <select class="pf-input" name="timezone">
                            <option value="Europe/Istanbul" {{ setting('timezone','Europe/Istanbul')==='Europe/Istanbul'?'selected':'' }}>Europe/Istanbul (UTC+3)</option>
                            <option value="UTC" {{ setting('timezone')==='UTC'?'selected':'' }}>UTC</option>
                            <option value="Europe/London" {{ setting('timezone')==='Europe/London'?'selected':'' }}>Europe/London</option>
                        </select>
                    </div>
                </div>
                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">Para Birimi</label>
                        <div class="pf-input-pre"><span class="pf-pre-label">₺</span><input type="text" name="currency" value="{{ old('currency', setting('currency','TRY')) }}" placeholder="TRY"></div>
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Komisyon Oranı (%)</label>
                        <div class="pf-input-pre"><span class="pf-pre-label">%</span><input type="number" name="commission_rate" min="0" max="100" step="0.1" value="{{ old('commission_rate', setting('commission_rate', 5)) }}"></div>
                    </div>
                </div>

                <hr class="s-hr">
                <div class="pf-toggle-list">
                    @foreach([
                        ['key'=>'registration_enabled','title'=>'Kayıt açık','desc'=>'Yeni kullanıcı kaydına izin ver'],
                        ['key'=>'email_verification','title'=>'E-posta doğrulama','desc'=>'Kayıtta e-posta doğrulaması zorunlu olsun'],
                        ['key'=>'auction_auto_extend','title'=>'Otomatik süre uzatma','desc'=>'Son dakikada teklif gelince süre uzasın'],
                        ['key'=>'guest_bidding','title'=>'Misafir teklifi','desc'=>'Giriş yapmadan teklif verilebilsin'],
                        ['key'=>'maintenance_mode','title'=>'Bakım modu','desc'=>'Site bakımda gösterilsin (admin erişimi açık kalır)'],
                    ] as $t)
                    <div class="pf-trow pf-trow-border">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">{{ $t['title'] }}</div>
                            <div class="pf-trow-desc">{{ $t['desc'] }}</div>
                        </div>
                        <input type="hidden" name="{{ $t['key'] }}" value="0">
                        <label class="s-sw"><input type="checkbox" name="{{ $t['key'] }}" value="1" {{ setting($t['key'],false)?'checked':'' }}><span class="s-sl"></span></label>
                    </div>
                    @endforeach
                </div>
                <div class="s-foot"><button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
            </form>
        </div>

        <div id="sc-seo" class="s-panel">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="s-form">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="seo">

                <div class="s-field">
                    <label class="s-lbl">Meta Başlık</label>
                    <input class="pf-input" type="text" name="meta_title" value="{{ old('meta_title', setting('meta_title')) }}" placeholder="Artirdim.com — Güvenli Açık Artırma">
                    <div class="s-hint">Önerilen: 50–60 karakter</div>
                </div>
                <div class="s-field">
                    <label class="s-lbl">Meta Açıklama</label>
                    <div style="position:relative;">
                        <textarea class="pf-input" name="meta_description" id="metaDesc" rows="3" maxlength="160" oninput="charCount('metaDesc','metaDescCnt',160)" placeholder="Kısa açıklama (160 karakter)...">{{ old('meta_description', setting('meta_description')) }}</textarea>
                        <span id="metaDescCnt" class="pf-char-cnt">{{ strlen(setting('meta_description','')) }}/160</span>
                    </div>
                </div>
                <div class="s-field">
                    <label class="s-lbl">Anahtar Kelimeler</label>
                    <input class="pf-input" type="text" name="meta_keywords" value="{{ old('meta_keywords', setting('meta_keywords')) }}" placeholder="müzayede, açık artırma, online alışveriş">
                    <div class="s-hint">Virgülle ayırın</div>
                </div>

                <hr class="s-hr">
                <div class="s-field">
                    <label class="s-lbl">OG Başlık</label>
                    <input class="pf-input" type="text" name="og_title" value="{{ old('og_title', setting('og_title')) }}" placeholder="Artirdim.com">
                </div>
                <div class="s-field">
                    <label class="s-lbl">OG Açıklama</label>
                    <textarea class="pf-input" name="og_description" rows="2" placeholder="Sosyal medyada görünen açıklama...">{{ old('og_description', setting('og_description')) }}</textarea>
                </div>
                <div class="s-field">
                    <label class="s-lbl">OG Görsel URL</label>
                    <input class="pf-input" type="url" name="og_image" value="{{ old('og_image', setting('og_image')) }}" placeholder="https://artirdim.com/og-image.jpg">
                    <div class="s-hint">Önerilen boyut: 1200×630 px</div>
                </div>

                <hr class="s-hr">
                <div class="s-sec"><i class="bi bi-code-slash me-2"></i>Kod Enjeksiyonu</div>
                <div class="s-field">
                    <label class="s-lbl">Google Analytics / GTM</label>
                    <textarea class="pf-input" name="analytics_code" rows="4" style="font-family:monospace;font-size:.82rem;" placeholder="<!-- Google tag (gtag.js) -->">{{ old('analytics_code', setting('analytics_code')) }}</textarea>
                    <div class="s-hint"><i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i>Bu kod tüm sayfalara &lt;head&gt; içine eklenir.</div>
                </div>
                <div class="s-foot"><button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
            </form>
        </div>

        <div id="sc-kvkk" class="s-panel">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="s-form">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="kvkk">

                <div class="s-hint mb-2"><i class="bi bi-info-circle me-1"></i>Kayıt ve ödeme formlarında "KVKK Aydınlatma Metni" bağlantısıyla gösterilir.</div>

                <div class="s-field mt-2">
                    <label class="s-lbl">Veri Sorumlusu</label>
                    <div class="s-2col">
                        <input class="pf-input" type="text" name="kvkk_company" value="{{ old('kvkk_company', setting('kvkk_company')) }}" placeholder="Şirket / Kişi adı">
                        <input class="pf-input" type="email" name="kvkk_email" value="{{ old('kvkk_email', setting('kvkk_email')) }}" placeholder="kvkk@artirdim.com">
                    </div>
                </div>
                <div class="s-field">
                    <label class="s-lbl">KVKK Metni <span class="pf-req">*</span></label>
                    <div class="s-editor">
                        <div class="s-toolbar">
                            <button type="button" onclick="execCmd('bold')"><i class="bi bi-type-bold"></i></button>
                            <button type="button" onclick="execCmd('italic')"><i class="bi bi-type-italic"></i></button>
                            <button type="button" onclick="execCmd('underline')"><i class="bi bi-type-underline"></i></button>
                            <span class="s-sep"></span>
                            <button type="button" onclick="execCmd('insertUnorderedList')"><i class="bi bi-list-ul"></i></button>
                            <button type="button" onclick="execCmd('insertOrderedList')"><i class="bi bi-list-ol"></i></button>
                            <span class="s-sep"></span>
                            <button type="button" onclick="execCmd('formatBlock','h2')">H2</button>
                            <button type="button" onclick="execCmd('formatBlock','h3')">H3</button>
                            <button type="button" onclick="execCmd('formatBlock','p')">P</button>
                            <span class="s-sep"></span>
                            <button type="button" onclick="execCmd('removeFormat')"><i class="bi bi-eraser"></i></button>
                        </div>
                        <div class="s-editable" id="kvkk-content" contenteditable="true">{!! old('kvkk_text', setting('kvkk_text', '<h2>KVKK Aydınlatma Metni</h2><p>Kişisel verileriniz, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında işlenmektedir.</p>')) !!}</div>
                    </div>
                    <input type="hidden" name="kvkk_text" id="kvkk-hidden">
                </div>

                <hr class="s-hr">
                <div class="pf-toggle-list">
                    <div class="pf-trow pf-trow-border">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Kayıtta KVKK onayı zorunlu</div>
                            <div class="pf-trow-desc">Kullanıcılar kayıt olmadan önce metni onaylamalı</div>
                        </div>
                        <input type="hidden" name="kvkk_required" value="0">
                        <label class="s-sw"><input type="checkbox" name="kvkk_required" value="1" {{ setting('kvkk_required',true)?'checked':'' }}><span class="s-sl"></span></label>
                    </div>
                    <div class="pf-trow pf-trow-border">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Çerez banner'ı göster</div>
                            <div class="pf-trow-desc">Ziyaretçilere çerez onay bildirimi göster</div>
                        </div>
                        <input type="hidden" name="cookie_banner" value="0">
                        <label class="s-sw"><input type="checkbox" name="cookie_banner" value="1" {{ setting('cookie_banner',true)?'checked':'' }}><span class="s-sl"></span></label>
                    </div>
                </div>
                <div class="s-foot">
                    <button type="submit" class="pf-btn-save" onclick="syncEditor('kvkk-content','kvkk-hidden')"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>

        <div id="sc-gizlilik" class="s-panel">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="s-form">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="gizlilik">

                <div class="s-hint mb-2"><i class="bi bi-info-circle me-1"></i>Footer'daki "Gizlilik Politikası" bağlantısında ve /gizlilik-politikasi sayfasında gösterilir.</div>
                <div class="s-field mt-2">
                    <label class="s-lbl">Gizlilik Politikası Metni <span class="pf-req">*</span></label>
                    <div class="s-editor">
                        <div class="s-toolbar">
                            <button type="button" onclick="execCmd('bold')"><i class="bi bi-type-bold"></i></button>
                            <button type="button" onclick="execCmd('italic')"><i class="bi bi-type-italic"></i></button>
                            <button type="button" onclick="execCmd('underline')"><i class="bi bi-type-underline"></i></button>
                            <span class="s-sep"></span>
                            <button type="button" onclick="execCmd('insertUnorderedList')"><i class="bi bi-list-ul"></i></button>
                            <button type="button" onclick="execCmd('insertOrderedList')"><i class="bi bi-list-ol"></i></button>
                            <span class="s-sep"></span>
                            <button type="button" onclick="execCmd('formatBlock','h2')">H2</button>
                            <button type="button" onclick="execCmd('formatBlock','h3')">H3</button>
                            <button type="button" onclick="execCmd('formatBlock','p')">P</button>
                            <span class="s-sep"></span>
                            <button type="button" onclick="execCmd('removeFormat')"><i class="bi bi-eraser"></i></button>
                        </div>
                        <div class="s-editable" id="gizlilik-content" contenteditable="true">{!! old('privacy_text', setting('privacy_text', '<h2>Gizlilik Politikası</h2><p>Bu politika, kişisel verilerinizin nasıl toplandığını ve kullanıldığını açıklar.</p>')) !!}</div>
                    </div>
                    <input type="hidden" name="privacy_text" id="gizlilik-hidden">
                </div>
                <div class="s-foot">
                    <button type="submit" class="pf-btn-save" onclick="syncEditor('gizlilik-content','gizlilik-hidden')"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>

        <div id="sc-kullanim" class="s-panel">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="s-form">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="kullanim">

                <div class="s-hint mb-2"><i class="bi bi-info-circle me-1"></i>Kayıt formunda ve /kullanim-kosullari sayfasında gösterilir.</div>
                <div class="s-field mt-2">
                    <label class="s-lbl">Kullanım Koşulları Metni <span class="pf-req">*</span></label>
                    <div class="s-editor">
                        <div class="s-toolbar">
                            <button type="button" onclick="execCmd('bold')"><i class="bi bi-type-bold"></i></button>
                            <button type="button" onclick="execCmd('italic')"><i class="bi bi-type-italic"></i></button>
                            <button type="button" onclick="execCmd('underline')"><i class="bi bi-type-underline"></i></button>
                            <span class="s-sep"></span>
                            <button type="button" onclick="execCmd('insertUnorderedList')"><i class="bi bi-list-ul"></i></button>
                            <button type="button" onclick="execCmd('insertOrderedList')"><i class="bi bi-list-ol"></i></button>
                            <span class="s-sep"></span>
                            <button type="button" onclick="execCmd('formatBlock','h2')">H2</button>
                            <button type="button" onclick="execCmd('formatBlock','h3')">H3</button>
                            <button type="button" onclick="execCmd('formatBlock','p')">P</button>
                            <span class="s-sep"></span>
                            <button type="button" onclick="execCmd('removeFormat')"><i class="bi bi-eraser"></i></button>
                        </div>
                        <div class="s-editable" id="kullanim-content" contenteditable="true">{!! old('terms_text', setting('terms_text', '<h2>Kullanım Koşulları</h2><p>Sitemizi kullanarak aşağıdaki koşulları kabul etmiş sayılırsınız.</p>')) !!}</div>
                    </div>
                    <input type="hidden" name="terms_text" id="kullanim-hidden">
                </div>
                <div class="s-foot">
                    <button type="submit" class="pf-btn-save" onclick="syncEditor('kullanim-content','kullanim-hidden')"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>

        <div id="sc-iletisim" class="s-panel">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="s-form">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="iletisim">

                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">İletişim E-postası</label>
                        <input class="pf-input" type="email" name="contact_email" value="{{ old('contact_email', setting('contact_email')) }}" placeholder="iletisim@artirdim.com">
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Destek E-postası</label>
                        <input class="pf-input" type="email" name="support_email" value="{{ old('support_email', setting('support_email')) }}" placeholder="destek@artirdim.com">
                    </div>
                </div>
                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">Telefon</label>
                        <div class="pf-input-pre"><span class="pf-pre-label">+90</span><input type="tel" name="contact_phone" value="{{ old('contact_phone', setting('contact_phone')) }}" placeholder="5xx xxx xx xx"></div>
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">WhatsApp</label>
                        <div class="pf-input-pre"><span class="pf-pre-label"><i class="bi bi-whatsapp"></i></span><input type="tel" name="whatsapp" value="{{ old('whatsapp', setting('whatsapp')) }}" placeholder="5xx xxx xx xx"></div>
                    </div>
                </div>
                <div class="s-field">
                    <label class="s-lbl">Adres</label>
                    <textarea class="pf-input" name="contact_address" rows="2" placeholder="Şirket adresi...">{{ old('contact_address', setting('contact_address')) }}</textarea>
                </div>

                <hr class="s-hr">
                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">SMTP Host</label>
                        <input class="pf-input" type="text" name="smtp_host" value="{{ old('smtp_host', setting('smtp_host', env('MAIL_HOST'))) }}" placeholder="smtp.gmail.com">
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">SMTP Port</label>
                        <input class="pf-input" type="number" name="smtp_port" value="{{ old('smtp_port', setting('smtp_port', 587)) }}" placeholder="587">
                    </div>
                </div>
                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">Kullanıcı Adı</label>
                        <input class="pf-input" type="text" name="smtp_username" value="{{ old('smtp_username', setting('smtp_username')) }}" placeholder="kullanici@gmail.com">
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Şifre</label>
                        <input class="pf-input" type="password" name="smtp_password" placeholder="••••••••">
                        <div class="s-hint">Boş bırakırsanız mevcut şifre korunur</div>
                    </div>
                </div>
                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">Gönderen Ad</label>
                        <input class="pf-input" type="text" name="mail_from_name" value="{{ old('mail_from_name', setting('mail_from_name', config('app.name'))) }}" placeholder="Artirdim.com">
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Gönderen E-posta</label>
                        <input class="pf-input" type="email" name="mail_from_address" value="{{ old('mail_from_address', setting('mail_from_address')) }}" placeholder="noreply@artirdim.com">
                    </div>
                </div>
                <div class="s-foot">
                    <button type="button" class="pf-btn-reset" onclick="testMail()"><i class="bi bi-send me-1"></i> Test E-postası</button>
                    <button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>

        <div id="sc-sosyal" class="s-panel">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="s-form">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="sosyal">

                @foreach([
                    ['key'=>'social_instagram','icon'=>'bi-instagram','prefix'=>'instagram.com/','label'=>'Instagram'],
                    ['key'=>'social_twitter','icon'=>'bi-twitter-x','prefix'=>'x.com/','label'=>'X (Twitter)'],
                    ['key'=>'social_facebook','icon'=>'bi-facebook','prefix'=>'facebook.com/','label'=>'Facebook'],
                    ['key'=>'social_youtube','icon'=>'bi-youtube','prefix'=>'youtube.com/@','label'=>'YouTube'],
                    ['key'=>'social_linkedin','icon'=>'bi-linkedin','prefix'=>'linkedin.com/company/','label'=>'LinkedIn'],
                    ['key'=>'social_tiktok','icon'=>'bi-tiktok','prefix'=>'tiktok.com/@','label'=>'TikTok'],
                ] as $s)
                <div class="pf-social-row" style="margin-bottom:.65rem;">
                    <div class="pf-social-icon"><i class="bi {{ $s['icon'] }}"></i></div>
                    <div class="pf-input-pre" style="flex:1;">
                        <span class="pf-pre-label" style="font-size:.75rem;">{{ $s['prefix'] }}</span>
                        <input type="text" name="{{ $s['key'] }}" value="{{ old($s['key'], setting($s['key'])) }}" placeholder="{{ $s['label'] }} kullanıcı adı">
                    </div>
                </div>
                @endforeach
                <div class="s-foot"><button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
            </form>
        </div>

        <div id="sc-odeme" class="s-panel">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="s-form">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="odeme">

                <div class="pf-toggle-list">
                    <div class="pf-trow pf-trow-border">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">iyzico</div>
                            <div class="pf-trow-desc">iyzico ödeme altyapısını aktif et</div>
                        </div>
                        <input type="hidden" name="iyzico_enabled" value="0">
                        <label class="s-sw"><input type="checkbox" name="iyzico_enabled" value="1" {{ setting('iyzico_enabled')?'checked':'' }}><span class="s-sl"></span></label>
                    </div>
                    <div class="pf-trow pf-trow-border">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Havale / EFT</div>
                            <div class="pf-trow-desc">Banka transferi seçeneğini göster</div>
                        </div>
                        <input type="hidden" name="bank_transfer_enabled" value="0">
                        <label class="s-sw"><input type="checkbox" name="bank_transfer_enabled" value="1" {{ setting('bank_transfer_enabled')?'checked':'' }}><span class="s-sl"></span></label>
                    </div>
                </div>

                <hr class="s-hr">
                <div class="s-field">
                    <label class="s-lbl">Ortam</label>
                    <select class="pf-input" name="iyzico_env">
                        <option value="sandbox" {{ setting('iyzico_env','sandbox')==='sandbox'?'selected':'' }}>Sandbox (Test)</option>
                        <option value="production" {{ setting('iyzico_env')==='production'?'selected':'' }}>Production (Canlı)</option>
                    </select>
                </div>
                <div class="s-2col">
                    <div class="s-field">
                        <label class="s-lbl">API Key</label>
                        <input class="pf-input" type="text" name="iyzico_api_key" value="{{ old('iyzico_api_key', setting('iyzico_api_key')) }}" placeholder="sandbox-...">
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Secret Key</label>
                        <input class="pf-input" type="password" name="iyzico_secret_key" placeholder="••••••••">
                        <div class="s-hint">Boş bırakırsanız mevcut anahtar korunur</div>
                    </div>
                </div>

                <hr class="s-hr">
                <div class="s-field">
                    <label class="s-lbl">IBAN Bilgileri</label>
                    <textarea class="pf-input" name="bank_accounts" rows="4" placeholder="Ziraat Bankası&#10;IBAN: TR00 0000 0000 0000 0000 0000 00&#10;Ad Soyad: Artirdim A.Ş.">{{ old('bank_accounts', setting('bank_accounts')) }}</textarea>
                </div>
                <div class="s-foot"><button type="submit" class="pf-btn-save"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
            </form>
        </div>

        <div id="sc-bakim" class="s-panel">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="s-form">
                @csrf @method('PUT')
                <input type="hidden" name="section" value="bakim">
                <div class="s-info-grid">
                    @foreach([
                        ['label'=>'PHP Sürümü','value'=>phpversion()],
                        ['label'=>'Laravel Sürümü','value'=>app()->version()],
                        ['label'=>'Ortam','value'=>app()->environment()],
                        ['label'=>'Önbellek','value'=>config('cache.default')],
                        ['label'=>'Kuyruk','value'=>config('queue.default')],
                        ['label'=>'Depolama','value'=>config('filesystems.default')],
                    ] as $info)
                    <div class="s-info-item">
                        <div class="s-info-lbl">{{ $info['label'] }}</div>
                        <div class="s-info-val">{{ $info['value'] }}</div>
                    </div>
                    @endforeach
                </div>

                <hr class="s-hr">
                <div class="s-sec"><i class="bi bi-lightning me-2"></i>Önbellek Yönetimi</div>
                <div class="s-action-grid">
                    @foreach([
                        ['route'=>'admin.settings.cache.clear','icon'=>'bi-trash3','title'=>'Önbelleği Temizle','desc'=>'Tüm önbellek verilerini sil','color'=>'#ef4444'],
                        ['route'=>'admin.settings.cache.config','icon'=>'bi-gear','title'=>'Config Cache','desc'=>'Yapılandırmayı önbellekle','color'=>'var(--primary)'],
                        ['route'=>'admin.settings.cache.route','icon'=>'bi-signpost-split','title'=>'Route Cache','desc'=>'Rotaları önbellekle','color'=>'var(--primary)'],
                        ['route'=>'admin.settings.cache.view','icon'=>'bi-eye','title'=>'View Cache','desc'=>'Blade şablonlarını derle','color'=>'var(--primary)'],
                        ['route'=>'admin.settings.storage.link','icon'=>'bi-link-45deg','title'=>'Storage Link','desc'=>'Public storage bağlantısı oluştur','color'=>'var(--primary)'],
                        ['route'=>'admin.settings.optimize','icon'=>'bi-speedometer2','title'=>'Optimize','desc'=>'Tüm önbellekleri oluştur','color'=>'#10b981'],
                    ] as $action)
                    <form method="POST" action="{{ route($action['route']) }}" style="display:contents;">
                        @csrf
                        <button type="submit" class="s-action-btn" style="--ac:{{ $action['color'] }};">
                            <i class="bi {{ $action['icon'] }}" style="font-size:1.2rem;display:block;margin-bottom:.3rem;color:var(--ac);"></i>
                            <div style="font-size:.8rem;font-weight:700;color:var(--ac);margin-bottom:.1rem;">{{ $action['title'] }}</div>
                            <div style="font-size:.7rem;color:var(--muted);">{{ $action['desc'] }}</div>
                        </button>
                    </form>
                    @endforeach
                </div>

                <hr class="s-hr">
                <div class="s-sec" style="color:#f59e0b;"><i class="bi bi-exclamation-triangle me-2"></i>Tehlikeli Alan</div>
                <div style="border:1.5px solid rgba(239,68,68,.3);border-radius:10px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1rem;gap:1rem;flex-wrap:wrap;">
                        <div>
                            <div style="font-weight:700;font-size:.88rem;color:#ef4444;margin-bottom:.15rem;">Tüm Önbelleği Sıfırla</div>
                            <div class="s-hint">Config, route, view önbelleği ve uygulama önbelleği temizlenir</div>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.cache.clear') }}">
                            @csrf
                            <button type="button" class="pf-btn-action-delete danger-cache-btn"><i class="bi bi-trash"></i> Sıfırla</button>
                        </form>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection


@push('scripts')
<script src="{{ asset('assets/js/custom/admin-settings.js') }}"></script>
@endpush
