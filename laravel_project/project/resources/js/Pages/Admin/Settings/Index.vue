<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({ settings: Object, stats: Object, system: Object, testMailUrl: String, logoUrl: String });
const s = props.settings;
const page = usePage();
const flash = computed(() => page.props.flash || {});
const updateUrl = () => route('admin.settings.update');

const activeTab = ref('genel');
function switchSTab(key) { activeTab.value = key; }

/* ── Forms (bölüm bazlı, controller birebir) ── */
const fGenel = useForm({
    section: 'genel', site_logo: null,
    site_name: s.site_name, site_url: s.site_url, site_description: s.site_description,
    default_lang: s.default_lang, timezone: s.timezone, currency: s.currency, commission_rate: s.commission_rate,
    registration_enabled: s.registration_enabled, email_verification: s.email_verification,
    auction_auto_extend: s.auction_auto_extend, guest_bidding: s.guest_bidding, maintenance_mode: s.maintenance_mode,
});
const fSeo = useForm({
    section: 'seo', meta_title: s.meta_title, meta_description: s.meta_description, meta_keywords: s.meta_keywords,
    og_title: s.og_title, og_description: s.og_description, og_image: s.og_image, analytics_code: s.analytics_code,
});
const fKvkk = useForm({ section: 'kvkk', kvkk_company: s.kvkk_company, kvkk_email: s.kvkk_email, kvkk_text: s.kvkk_text, kvkk_required: s.kvkk_required, cookie_banner: s.cookie_banner });
const fGizlilik = useForm({ section: 'gizlilik', privacy_text: s.privacy_text });
const fKullanim = useForm({ section: 'kullanim', terms_text: s.terms_text });
const fIletisim = useForm({
    section: 'iletisim', contact_email: s.contact_email, support_email: s.support_email, contact_phone: s.contact_phone,
    whatsapp: s.whatsapp, contact_address: s.contact_address, smtp_host: s.smtp_host, smtp_port: s.smtp_port,
    smtp_username: s.smtp_username, smtp_password: '', mail_from_name: s.mail_from_name, mail_from_address: s.mail_from_address,
});
const fSosyal = useForm({
    section: 'sosyal', social_instagram: s.social_instagram, social_twitter: s.social_twitter, social_facebook: s.social_facebook,
    social_youtube: s.social_youtube, social_linkedin: s.social_linkedin, social_tiktok: s.social_tiktok,
});
const fOdeme = useForm({
    section: 'odeme', iyzico_enabled: s.iyzico_enabled, bank_transfer_enabled: s.bank_transfer_enabled,
    iyzico_env: s.iyzico_env, iyzico_api_key: s.iyzico_api_key, iyzico_secret_key: '', bank_accounts: s.bank_accounts,
});

const opts = { preserveScroll: true, preserveState: true };
function saveGenel() { fGenel.transform((d) => ({ ...d, _method: 'PUT' })).post(updateUrl(), { ...opts, forceFormData: true }); }
function saveSeo() { fSeo.put(updateUrl(), opts); }
function saveIletisim() { fIletisim.put(updateUrl(), opts); }
function saveSosyal() { fSosyal.put(updateUrl(), opts); }
function saveOdeme() { fOdeme.put(updateUrl(), opts); }

/* ── Zengin metin editörleri ── */
const kvkkEl = ref(null), gizlilikEl = ref(null), kullanimEl = ref(null);
onMounted(() => {
    if (kvkkEl.value) kvkkEl.value.innerHTML = s.kvkk_text;
    if (gizlilikEl.value) gizlilikEl.value.innerHTML = s.privacy_text;
    if (kullanimEl.value) kullanimEl.value.innerHTML = s.terms_text;
});
function execCmd(cmd, val) { document.execCommand(cmd, false, val || null); }
function saveKvkk() { fKvkk.kvkk_text = kvkkEl.value ? kvkkEl.value.innerHTML : ''; fKvkk.put(updateUrl(), opts); }
function saveGizlilik() { fGizlilik.privacy_text = gizlilikEl.value ? gizlilikEl.value.innerHTML : ''; fGizlilik.put(updateUrl(), opts); }
function saveKullanim() { fKullanim.terms_text = kullanimEl.value ? kullanimEl.value.innerHTML : ''; fKullanim.put(updateUrl(), opts); }

/* ── Üstteki "Kaydet" — aktif sekme formu ── */
function submitActiveForm() {
    ({ genel: saveGenel, seo: saveSeo, kvkk: saveKvkk, gizlilik: saveGizlilik, kullanim: saveKullanim,
       iletisim: saveIletisim, sosyal: saveSosyal, odeme: saveOdeme, bakim: () => {} }[activeTab.value] || (() => {}))();
}

/* ── Logo önizleme ── */
const logoPreview = ref(props.logoUrl);
function onLogoChange(e) {
    const file = e.target.files && e.target.files[0];
    if (!file) return;
    fGenel.site_logo = file;
    const r = new FileReader();
    r.onload = (ev) => { logoPreview.value = ev.target.result; };
    r.readAsDataURL(file);
}

/* ── Karakter sayacı ── */
const metaDescLen = computed(() => (fSeo.meta_description || '').length);

/* ── Cache / optimize aksiyonları ── */
function runAction(routeName) { router.post(route(routeName), {}, { preserveScroll: true }); }
function dangerReset() {
    const go = () => runAction('admin.settings.cache.clear');
    if (window.Swal) {
        window.Swal.fire({ title: 'Emin misin?', text: 'Tüm önbellek verileri silinecek.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet, temizle', cancelButtonText: 'Vazgeç', reverseButtons: true, heightAuto: false }).then((r) => { if (r.isConfirmed) go(); });
    } else if (confirm('Tüm önbellek verileri silinecek.')) go();
}

/* ── Test e-postası ── */
function testMail() {
    const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    const send = (email) => fetch(props.testMailUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin', body: JSON.stringify({ email }) })
        .then((r) => r.json())
        .then((data) => { if (window.Swal) window.Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'Gönderildi!' : 'Hata!', text: data.message, heightAuto: false }); else alert(data.message); });
    if (window.Swal) {
        window.Swal.fire({ title: 'Test E-postası', input: 'email', inputPlaceholder: 'Gönderilecek e-posta adresi', icon: 'question', showCancelButton: true, confirmButtonText: 'Gönder', cancelButtonText: 'Vazgeç', heightAuto: false }).then((r) => { if (r.isConfirmed) send(r.value); });
    } else { const e = prompt('Gönderilecek e-posta adresi'); if (e) send(e); }
}

const genelToggles = [
    { key: 'registration_enabled', title: 'Kayıt açık', desc: 'Yeni kullanıcı kaydına izin ver' },
    { key: 'email_verification', title: 'E-posta doğrulama', desc: 'Kayıtta e-posta doğrulaması zorunlu olsun' },
    { key: 'auction_auto_extend', title: 'Otomatik süre uzatma', desc: 'Son dakikada teklif gelince süre uzasın' },
    { key: 'guest_bidding', title: 'Misafir teklifi', desc: 'Giriş yapmadan teklif verilebilsin' },
    { key: 'maintenance_mode', title: 'Bakım modu', desc: 'Site bakımda gösterilsin (admin erişimi açık kalır)' },
];
const socials = [
    { key: 'social_instagram', icon: 'bi-instagram', prefix: 'instagram.com/', label: 'Instagram' },
    { key: 'social_twitter', icon: 'bi-twitter-x', prefix: 'x.com/', label: 'X (Twitter)' },
    { key: 'social_facebook', icon: 'bi-facebook', prefix: 'facebook.com/', label: 'Facebook' },
    { key: 'social_youtube', icon: 'bi-youtube', prefix: 'youtube.com/@', label: 'YouTube' },
    { key: 'social_linkedin', icon: 'bi-linkedin', prefix: 'linkedin.com/company/', label: 'LinkedIn' },
    { key: 'social_tiktok', icon: 'bi-tiktok', prefix: 'tiktok.com/@', label: 'TikTok' },
];
const cacheActions = [
    { route: 'admin.settings.cache.clear', icon: 'bi-trash3', title: 'Önbelleği Temizle', desc: 'Tüm önbellek verilerini sil', color: '#ef4444' },
    { route: 'admin.settings.cache.config', icon: 'bi-gear', title: 'Config Cache', desc: 'Yapılandırmayı önbellekle', color: 'var(--primary)' },
    { route: 'admin.settings.cache.route', icon: 'bi-signpost-split', title: 'Route Cache', desc: 'Rotaları önbellekle', color: 'var(--primary)' },
    { route: 'admin.settings.cache.view', icon: 'bi-eye', title: 'View Cache', desc: 'Blade şablonlarını derle', color: 'var(--primary)' },
    { route: 'admin.settings.storage.link', icon: 'bi-link-45deg', title: 'Storage Link', desc: 'Public storage bağlantısı oluştur', color: 'var(--primary)' },
    { route: 'admin.settings.optimize', icon: 'bi-speedometer2', title: 'Optimize', desc: 'Tüm önbellekleri oluştur', color: '#10b981' },
];
const systemInfo = computed(() => [
    ['PHP Sürümü', props.system.php], ['Laravel Sürümü', props.system.laravel], ['Ortam', props.system.env],
    ['Önbellek', props.system.cache], ['Kuyruk', props.system.queue], ['Depolama', props.system.storage],
]);
const tabs = [
    ['genel', 'bi-sliders', 'Genel'], ['seo', 'bi-search', 'SEO'], ['kvkk', 'bi-shield-check', 'KVKK'],
    ['gizlilik', 'bi-file-lock', 'Gizlilik'], ['kullanim', 'bi-file-text', 'Kullanım Koşulları'],
    ['iletisim', 'bi-envelope', 'İletişim'], ['sosyal', 'bi-share', 'Sosyal'], ['odeme', 'bi-credit-card', 'Ödeme'],
    ['bakim', 'bi-tools', 'Bakım'],
];
</script>

<template>
    <Head title="Site Ayarları" />
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
                    <div class="pf-uname-row"><span class="pf-uname">Site Ayarları</span><span class="pf-role-badge">👑 Admin</span></div>
                    <div class="pf-bio">Genel site yapılandırması, sözleşmeler ve sistem ayarları</div>
                </div>
            </div>

            <div class="pf-stats-row">
                <div class="pf-stat"><div class="pf-stat-num">{{ stats.users }}</div><div class="pf-stat-label">KULLANICI</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ stats.auctions }}</div><div class="pf-stat-label">İLAN</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ stats.bids }}</div><div class="pf-stat-label">TEKLİF</div></div>
                <div class="pf-stat"><div class="pf-stat-num" style="color:#10b981;">●</div><div class="pf-stat-label">AKTİF</div></div>
            </div>

            <div class="pf-action-row breadcrumb-action-row">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')" class="pf-link-primary">Admin</Link></li>
                        <li class="breadcrumb-item active pf-text-muted">Ayarlar</li>
                    </ol>
                </nav>
                <div class="pf-action-buttons">
                    <button type="button" class="pf-btn-save" @click="submitActiveForm" data-testid="settings-save-top"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                </div>
            </div>
        </div>

        <div class="pf-content-area p-5">
            <div class="pf-tab-bar wraping">
                <button v-for="t in tabs" :key="t[0]" class="pf-ptab bar-item" :class="{ active: activeTab === t[0] }" @click="switchSTab(t[0])" :data-testid="`settings-tab-${t[0]}`">
                    <i class="bi me-1" :class="t[1]"></i> {{ t[2] }}
                </button>
            </div>

            <div v-if="flash.settings_success" class="pf-alert-success mt-2"><i class="bi bi-check-circle-fill me-2"></i>{{ flash.settings_success }}</div>
            <div v-if="flash.settings_error" class="pf-alert-danger mt-2"><i class="bi bi-x-circle-fill me-2"></i>{{ flash.settings_error }}</div>

            <!-- GENEL -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'genel' }">
                <form @submit.prevent="saveGenel" class="s-form">
                    <div class="s-logo-row">
                        <div class="s-logo-box" id="logoPreview">
                            <img v-if="logoPreview" :src="logoPreview" alt="Logo">
                            <i v-else class="bi bi-image" style="font-size:1.6rem;color:var(--muted);"></i>
                        </div>
                        <div>
                            <div class="s-hint">PNG, SVG · Transparan arka plan · Maks. 2MB</div>
                            <label for="site_logo" class="pf-btn-photo mt-2 d-inline-flex align-items-center gap-1" style="cursor:pointer;"><i class="bi bi-upload"></i> Logo yükle</label>
                            <input type="file" id="site_logo" accept=".png,.svg,.jpg,.jpeg,.webp" class="d-none" @change="onLogoChange">
                        </div>
                    </div>
                    <div class="s-2col">
                        <div class="s-field">
                            <label class="s-lbl">Site Adı <span class="pf-req">*</span></label>
                            <input class="pf-input" type="text" v-model="fGenel.site_name" placeholder="Artirdim.com">
                            <div v-if="fGenel.errors.site_name" class="pf-error">{{ fGenel.errors.site_name }}</div>
                        </div>
                        <div class="s-field">
                            <label class="s-lbl">Site URL <span class="pf-req">*</span></label>
                            <input class="pf-input" type="url" v-model="fGenel.site_url" placeholder="https://artirdim.com">
                            <div v-if="fGenel.errors.site_url" class="pf-error">{{ fGenel.errors.site_url }}</div>
                        </div>
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Site Açıklaması</label>
                        <textarea class="pf-input" v-model="fGenel.site_description" rows="2" placeholder="Kısa site açıklaması..."></textarea>
                    </div>
                    <hr class="s-hr">
                    <div class="s-2col">
                        <div class="s-field">
                            <label class="s-lbl">Varsayılan Dil</label>
                            <select class="pf-input" v-model="fGenel.default_lang"><option value="tr">Türkçe</option><option value="en">English</option></select>
                        </div>
                        <div class="s-field">
                            <label class="s-lbl">Zaman Dilimi</label>
                            <select class="pf-input" v-model="fGenel.timezone">
                                <option value="Europe/Istanbul">Europe/Istanbul (UTC+3)</option>
                                <option value="UTC">UTC</option>
                                <option value="Europe/London">Europe/London</option>
                            </select>
                        </div>
                    </div>
                    <div class="s-2col">
                        <div class="s-field"><label class="s-lbl">Para Birimi</label><div class="pf-input-pre"><span class="pf-pre-label">₺</span><input type="text" v-model="fGenel.currency" placeholder="TRY"></div></div>
                        <div class="s-field"><label class="s-lbl">Komisyon Oranı (%)</label><div class="pf-input-pre"><span class="pf-pre-label">%</span><input type="number" v-model="fGenel.commission_rate" min="0" max="100" step="0.1"></div></div>
                    </div>
                    <hr class="s-hr">
                    <div class="pf-toggle-list">
                        <div v-for="t in genelToggles" :key="t.key" class="pf-trow pf-trow-border">
                            <div class="pf-trow-info"><div class="pf-trow-title">{{ t.title }}</div><div class="pf-trow-desc">{{ t.desc }}</div></div>
                            <label class="s-sw"><input type="checkbox" v-model="fGenel[t.key]"><span class="s-sl"></span></label>
                        </div>
                    </div>
                    <div class="s-foot"><button type="submit" class="pf-btn-save" :disabled="fGenel.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
                </form>
            </div>

            <!-- SEO -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'seo' }">
                <form @submit.prevent="saveSeo" class="s-form">
                    <div class="s-field"><label class="s-lbl">Meta Başlık</label><input class="pf-input" type="text" v-model="fSeo.meta_title" placeholder="Artirdim.com — Güvenli Açık Artırma"><div class="s-hint">Önerilen: 50–60 karakter</div></div>
                    <div class="s-field">
                        <label class="s-lbl">Meta Açıklama</label>
                        <div style="position:relative;">
                            <textarea class="pf-input" v-model="fSeo.meta_description" rows="3" maxlength="160" placeholder="Kısa açıklama (160 karakter)..."></textarea>
                            <span class="pf-char-cnt">{{ metaDescLen }}/160</span>
                        </div>
                    </div>
                    <div class="s-field"><label class="s-lbl">Anahtar Kelimeler</label><input class="pf-input" type="text" v-model="fSeo.meta_keywords" placeholder="müzayede, açık artırma, online alışveriş"><div class="s-hint">Virgülle ayırın</div></div>
                    <hr class="s-hr">
                    <div class="s-field"><label class="s-lbl">OG Başlık</label><input class="pf-input" type="text" v-model="fSeo.og_title" placeholder="Artirdim.com"></div>
                    <div class="s-field"><label class="s-lbl">OG Açıklama</label><textarea class="pf-input" v-model="fSeo.og_description" rows="2" placeholder="Sosyal medyada görünen açıklama..."></textarea></div>
                    <div class="s-field"><label class="s-lbl">OG Görsel URL</label><input class="pf-input" type="url" v-model="fSeo.og_image" placeholder="https://artirdim.com/og-image.jpg"><div class="s-hint">Önerilen boyut: 1200×630 px</div></div>
                    <hr class="s-hr">
                    <div class="s-sec"><i class="bi bi-code-slash me-2"></i>Kod Enjeksiyonu</div>
                    <div class="s-field"><label class="s-lbl">Google Analytics / GTM</label><textarea class="pf-input" v-model="fSeo.analytics_code" rows="4" style="font-family:monospace;font-size:.82rem;" placeholder="<!-- Google tag (gtag.js) -->"></textarea><div class="s-hint"><i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i>Bu kod tüm sayfalara &lt;head&gt; içine eklenir.</div></div>
                    <div class="s-foot"><button type="submit" class="pf-btn-save" :disabled="fSeo.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
                </form>
            </div>

            <!-- KVKK -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'kvkk' }">
                <form @submit.prevent="saveKvkk" class="s-form">
                    <div class="s-hint mb-2"><i class="bi bi-info-circle me-1"></i>Kayıt ve ödeme formlarında "KVKK Aydınlatma Metni" bağlantısıyla gösterilir.</div>
                    <div class="s-field mt-2"><label class="s-lbl">Veri Sorumlusu</label><div class="s-2col"><input class="pf-input" type="text" v-model="fKvkk.kvkk_company" placeholder="Şirket / Kişi adı"><input class="pf-input" type="email" v-model="fKvkk.kvkk_email" placeholder="kvkk@artirdim.com"></div></div>
                    <div class="s-field">
                        <label class="s-lbl">KVKK Metni <span class="pf-req">*</span></label>
                        <div class="s-editor">
                            <div class="s-toolbar">
                                <button type="button" @click="execCmd('bold')"><i class="bi bi-type-bold"></i></button>
                                <button type="button" @click="execCmd('italic')"><i class="bi bi-type-italic"></i></button>
                                <button type="button" @click="execCmd('underline')"><i class="bi bi-type-underline"></i></button>
                                <span class="s-sep"></span>
                                <button type="button" @click="execCmd('insertUnorderedList')"><i class="bi bi-list-ul"></i></button>
                                <button type="button" @click="execCmd('insertOrderedList')"><i class="bi bi-list-ol"></i></button>
                                <span class="s-sep"></span>
                                <button type="button" @click="execCmd('formatBlock','h2')">H2</button>
                                <button type="button" @click="execCmd('formatBlock','h3')">H3</button>
                                <button type="button" @click="execCmd('formatBlock','p')">P</button>
                                <span class="s-sep"></span>
                                <button type="button" @click="execCmd('removeFormat')"><i class="bi bi-eraser"></i></button>
                            </div>
                            <div class="s-editable" ref="kvkkEl" contenteditable="true"></div>
                        </div>
                    </div>
                    <hr class="s-hr">
                    <div class="pf-toggle-list">
                        <div class="pf-trow pf-trow-border"><div class="pf-trow-info"><div class="pf-trow-title">Kayıtta KVKK onayı zorunlu</div><div class="pf-trow-desc">Kullanıcılar kayıt olmadan önce metni onaylamalı</div></div><label class="s-sw"><input type="checkbox" v-model="fKvkk.kvkk_required"><span class="s-sl"></span></label></div>
                        <div class="pf-trow pf-trow-border"><div class="pf-trow-info"><div class="pf-trow-title">Çerez banner'ı göster</div><div class="pf-trow-desc">Ziyaretçilere çerez onay bildirimi göster</div></div><label class="s-sw"><input type="checkbox" v-model="fKvkk.cookie_banner"><span class="s-sl"></span></label></div>
                    </div>
                    <div class="s-foot"><button type="submit" class="pf-btn-save" :disabled="fKvkk.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
                </form>
            </div>

            <!-- GİZLİLİK -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'gizlilik' }">
                <form @submit.prevent="saveGizlilik" class="s-form">
                    <div class="s-hint mb-2"><i class="bi bi-info-circle me-1"></i>Footer'daki "Gizlilik Politikası" bağlantısında ve /gizlilik-politikasi sayfasında gösterilir.</div>
                    <div class="s-field mt-2">
                        <label class="s-lbl">Gizlilik Politikası Metni <span class="pf-req">*</span></label>
                        <div class="s-editor">
                            <div class="s-toolbar">
                                <button type="button" @click="execCmd('bold')"><i class="bi bi-type-bold"></i></button>
                                <button type="button" @click="execCmd('italic')"><i class="bi bi-type-italic"></i></button>
                                <button type="button" @click="execCmd('underline')"><i class="bi bi-type-underline"></i></button>
                                <span class="s-sep"></span>
                                <button type="button" @click="execCmd('insertUnorderedList')"><i class="bi bi-list-ul"></i></button>
                                <button type="button" @click="execCmd('insertOrderedList')"><i class="bi bi-list-ol"></i></button>
                                <span class="s-sep"></span>
                                <button type="button" @click="execCmd('formatBlock','h2')">H2</button>
                                <button type="button" @click="execCmd('formatBlock','h3')">H3</button>
                                <button type="button" @click="execCmd('formatBlock','p')">P</button>
                                <span class="s-sep"></span>
                                <button type="button" @click="execCmd('removeFormat')"><i class="bi bi-eraser"></i></button>
                            </div>
                            <div class="s-editable" ref="gizlilikEl" contenteditable="true"></div>
                        </div>
                    </div>
                    <div class="s-foot"><button type="submit" class="pf-btn-save" :disabled="fGizlilik.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
                </form>
            </div>

            <!-- KULLANIM -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'kullanim' }">
                <form @submit.prevent="saveKullanim" class="s-form">
                    <div class="s-hint mb-2"><i class="bi bi-info-circle me-1"></i>Kayıt formunda ve /kullanim-kosullari sayfasında gösterilir.</div>
                    <div class="s-field mt-2">
                        <label class="s-lbl">Kullanım Koşulları Metni <span class="pf-req">*</span></label>
                        <div class="s-editor">
                            <div class="s-toolbar">
                                <button type="button" @click="execCmd('bold')"><i class="bi bi-type-bold"></i></button>
                                <button type="button" @click="execCmd('italic')"><i class="bi bi-type-italic"></i></button>
                                <button type="button" @click="execCmd('underline')"><i class="bi bi-type-underline"></i></button>
                                <span class="s-sep"></span>
                                <button type="button" @click="execCmd('insertUnorderedList')"><i class="bi bi-list-ul"></i></button>
                                <button type="button" @click="execCmd('insertOrderedList')"><i class="bi bi-list-ol"></i></button>
                                <span class="s-sep"></span>
                                <button type="button" @click="execCmd('formatBlock','h2')">H2</button>
                                <button type="button" @click="execCmd('formatBlock','h3')">H3</button>
                                <button type="button" @click="execCmd('formatBlock','p')">P</button>
                                <span class="s-sep"></span>
                                <button type="button" @click="execCmd('removeFormat')"><i class="bi bi-eraser"></i></button>
                            </div>
                            <div class="s-editable" ref="kullanimEl" contenteditable="true"></div>
                        </div>
                    </div>
                    <div class="s-foot"><button type="submit" class="pf-btn-save" :disabled="fKullanim.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
                </form>
            </div>

            <!-- İLETİŞİM -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'iletisim' }">
                <form @submit.prevent="saveIletisim" class="s-form">
                    <div class="s-2col">
                        <div class="s-field"><label class="s-lbl">İletişim E-postası</label><input class="pf-input" type="email" v-model="fIletisim.contact_email" placeholder="iletisim@artirdim.com"></div>
                        <div class="s-field"><label class="s-lbl">Destek E-postası</label><input class="pf-input" type="email" v-model="fIletisim.support_email" placeholder="destek@artirdim.com"></div>
                    </div>
                    <div class="s-2col">
                        <div class="s-field"><label class="s-lbl">Telefon</label><div class="pf-input-pre"><span class="pf-pre-label">+90</span><input type="tel" v-model="fIletisim.contact_phone" placeholder="5xx xxx xx xx"></div></div>
                        <div class="s-field"><label class="s-lbl">WhatsApp</label><div class="pf-input-pre"><span class="pf-pre-label"><i class="bi bi-whatsapp"></i></span><input type="tel" v-model="fIletisim.whatsapp" placeholder="5xx xxx xx xx"></div></div>
                    </div>
                    <div class="s-field"><label class="s-lbl">Adres</label><textarea class="pf-input" v-model="fIletisim.contact_address" rows="2" placeholder="Şirket adresi..."></textarea></div>
                    <hr class="s-hr">
                    <div class="s-2col">
                        <div class="s-field"><label class="s-lbl">SMTP Host</label><input class="pf-input" type="text" v-model="fIletisim.smtp_host" placeholder="smtp.gmail.com"></div>
                        <div class="s-field"><label class="s-lbl">SMTP Port</label><input class="pf-input" type="number" v-model="fIletisim.smtp_port" placeholder="587"></div>
                    </div>
                    <div class="s-2col">
                        <div class="s-field"><label class="s-lbl">Kullanıcı Adı</label><input class="pf-input" type="text" v-model="fIletisim.smtp_username" placeholder="kullanici@gmail.com"></div>
                        <div class="s-field"><label class="s-lbl">Şifre</label><input class="pf-input" type="password" v-model="fIletisim.smtp_password" placeholder="••••••••"><div class="s-hint">Boş bırakırsanız mevcut şifre korunur</div></div>
                    </div>
                    <div class="s-2col">
                        <div class="s-field"><label class="s-lbl">Gönderen Ad</label><input class="pf-input" type="text" v-model="fIletisim.mail_from_name" placeholder="Artirdim.com"></div>
                        <div class="s-field"><label class="s-lbl">Gönderen E-posta</label><input class="pf-input" type="email" v-model="fIletisim.mail_from_address" placeholder="noreply@artirdim.com"></div>
                    </div>
                    <div class="s-foot">
                        <button type="button" class="pf-btn-reset" @click="testMail"><i class="bi bi-send me-1"></i> Test E-postası</button>
                        <button type="submit" class="pf-btn-save" :disabled="fIletisim.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- SOSYAL -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'sosyal' }">
                <form @submit.prevent="saveSosyal" class="s-form">
                    <div v-for="soc in socials" :key="soc.key" class="pf-social-row" style="margin-bottom:.65rem;">
                        <div class="pf-social-icon"><i class="bi" :class="soc.icon"></i></div>
                        <div class="pf-input-pre" style="flex:1;">
                            <span class="pf-pre-label" style="font-size:.75rem;">{{ soc.prefix }}</span>
                            <input type="text" v-model="fSosyal[soc.key]" :placeholder="`${soc.label} kullanıcı adı`">
                        </div>
                    </div>
                    <div class="s-foot"><button type="submit" class="pf-btn-save" :disabled="fSosyal.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
                </form>
            </div>

            <!-- ÖDEME -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'odeme' }">
                <form @submit.prevent="saveOdeme" class="s-form">
                    <div class="pf-toggle-list">
                        <div class="pf-trow pf-trow-border"><div class="pf-trow-info"><div class="pf-trow-title">iyzico</div><div class="pf-trow-desc">iyzico ödeme altyapısını aktif et</div></div><label class="s-sw"><input type="checkbox" v-model="fOdeme.iyzico_enabled"><span class="s-sl"></span></label></div>
                        <div class="pf-trow pf-trow-border"><div class="pf-trow-info"><div class="pf-trow-title">Havale / EFT</div><div class="pf-trow-desc">Banka transferi seçeneğini göster</div></div><label class="s-sw"><input type="checkbox" v-model="fOdeme.bank_transfer_enabled"><span class="s-sl"></span></label></div>
                    </div>
                    <hr class="s-hr">
                    <div class="s-field"><label class="s-lbl">Ortam</label><select class="pf-input" v-model="fOdeme.iyzico_env"><option value="sandbox">Sandbox (Test)</option><option value="production">Production (Canlı)</option></select></div>
                    <div class="s-2col">
                        <div class="s-field"><label class="s-lbl">API Key</label><input class="pf-input" type="text" v-model="fOdeme.iyzico_api_key" placeholder="sandbox-..."></div>
                        <div class="s-field"><label class="s-lbl">Secret Key</label><input class="pf-input" type="password" v-model="fOdeme.iyzico_secret_key" placeholder="••••••••"><div class="s-hint">Boş bırakırsanız mevcut anahtar korunur</div></div>
                    </div>
                    <hr class="s-hr">
                    <div class="s-field"><label class="s-lbl">IBAN Bilgileri</label><textarea class="pf-input" v-model="fOdeme.bank_accounts" rows="4" placeholder="Ziraat Bankası"></textarea></div>
                    <div class="s-foot"><button type="submit" class="pf-btn-save" :disabled="fOdeme.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button></div>
                </form>
            </div>

            <!-- BAKIM -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'bakim' }">
                <div class="s-form">
                    <div class="s-info-grid">
                        <div v-for="info in systemInfo" :key="info[0]" class="s-info-item">
                            <div class="s-info-lbl">{{ info[0] }}</div>
                            <div class="s-info-val">{{ info[1] }}</div>
                        </div>
                    </div>
                    <hr class="s-hr">
                    <div class="s-sec"><i class="bi bi-lightning me-2"></i>Önbellek Yönetimi</div>
                    <div class="s-action-grid">
                        <button v-for="act in cacheActions" :key="act.route" type="button" class="s-action-btn" :style="{ '--ac': act.color }" @click="runAction(act.route)">
                            <i class="bi" :class="act.icon" :style="{ fontSize:'1.2rem', display:'block', marginBottom:'.3rem', color:'var(--ac)' }"></i>
                            <div :style="{ fontSize:'.8rem', fontWeight:700, color:'var(--ac)', marginBottom:'.1rem' }">{{ act.title }}</div>
                            <div style="font-size:.7rem;color:var(--muted);">{{ act.desc }}</div>
                        </button>
                    </div>
                    <hr class="s-hr">
                    <div class="s-sec" style="color:#f59e0b;"><i class="bi bi-exclamation-triangle me-2"></i>Tehlikeli Alan</div>
                    <div style="border:1.5px solid rgba(239,68,68,.3);border-radius:10px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1rem;gap:1rem;flex-wrap:wrap;">
                            <div>
                                <div style="font-weight:700;font-size:.88rem;color:#ef4444;margin-bottom:.15rem;">Tüm Önbelleği Sıfırla</div>
                                <div class="s-hint">Config, route, view önbelleği ve uygulama önbelleği temizlenir</div>
                            </div>
                            <button type="button" class="pf-btn-action-delete danger-cache-btn" @click="dangerReset"><i class="bi bi-trash"></i> Sıfırla</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
