<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ profileData: Object });
const p = props.profileData;

const page = usePage();
const flash = computed(() => page.props.flash || {});

const statusMap = {
    approved: { label: '✓ Onaylı Satıcı', color: 'rgba(16,185,129,.15)', border: 'rgba(16,185,129,.3)', text: '#10b981' },
    pending:  { label: '⏳ İncelemede',    color: 'rgba(245,158,11,.15)', border: 'rgba(245,158,11,.3)', text: '#f59e0b' },
    rejected: { label: '✗ Reddedildi',     color: 'rgba(239,68,68,.15)',  border: 'rgba(239,68,68,.3)',  text: '#ef4444' },
};
const st = computed(() => statusMap[p.verification_status] || statusMap.pending);
const docStatusLabel = { pending: 'İncelemede', approved: 'Onaylı', rejected: 'Reddedildi' };
const docBadgeClass = computed(() => p.verification_status === 'approved' ? 'success' : (p.verification_status === 'rejected' ? 'danger' : 'warning'));

const activeTab = ref(flash.value.profile_section || 'kisisel');
function switchTab(key) { activeTab.value = key; }

const kisiselForm = useForm({ name: p.name, phone: p.phone });
const sirketForm  = useForm({ company_name: p.company_name, tax_number: p.tax_number });
const odemeForm   = useForm({ iban: '' });
const docForm     = useForm({ id_document: null });

const ibanDigits = ref(p.iban_input || '');
const ibanDisplay = computed(() => ibanDigits.value.replace(/(.{4})/g, '$1 ').trim());
function onIbanInput(e) {
    ibanDigits.value = e.target.value.replace(/\D/g, '').slice(0, 24);
    // DOM'u zorla senkronla — 24 haneye ulaşıldıktan sonra girilen fazla/hatalı
    // karakterlerin input'ta takılı kalmasını önler (computed değişmediğinde Vue DOM'u atlıyor).
    e.target.value = ibanDisplay.value;
}

function saveKisisel() { kisiselForm.put(route('seller.profile.update', 'kisisel'), { preserveScroll: true }); }
function saveSirket()  { sirketForm.put(route('seller.profile.update', 'sirket'), { preserveScroll: true }); }
function saveOdeme() {
    odemeForm.iban = ibanDigits.value ? 'TR' + ibanDigits.value : '';
    odemeForm.put(route('seller.profile.update', 'odeme'), { preserveScroll: true });
}

const docName = ref('');
function onDocChange(e) {
    const f = e.target.files?.[0];
    if (!f) return;
    docForm.id_document = f;
    docName.value = f.name;
}
function uploadDoc() {
    docForm.post(route('seller.profile.document.upload'), { preserveScroll: true, forceFormData: true });
}

function saveActiveTab() {
    if (activeTab.value === 'kisisel') saveKisisel();
    else if (activeTab.value === 'sirket') saveSirket();
    else if (activeTab.value === 'odeme') saveOdeme();
    else if (activeTab.value === 'belge') uploadDoc();
}
</script>

<template>
    <Head title="Satıcı Profilim" />
    <div class="pf-root">

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
                        <span class="pf-uname">{{ p.name }}</span>
                        <span class="pf-role-badge" :style="{ background: st.color, borderColor: st.border, color: st.text }">{{ st.label }}</span>
                    </div>
                    <div class="pf-bio">Satıcı profilinizi buradan yönetebilirsiniz.</div>
                </div>
            </div>

            <div class="pf-stats-row">
                <div class="pf-stat"><div class="pf-stat-num">{{ p.auctions_count }}</div><div class="pf-stat-label">İLAN</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ p.active_count }}</div><div class="pf-stat-label">AKTİF</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ p.verification_status === 'approved' ? '✓' : '—' }}</div><div class="pf-stat-label">DOĞRULAMA</div></div>
                <div class="pf-stat">
                    <div class="pf-stat-num" :style="{ fontSize: 'var(--fs-sm)', color: p.iban_masked ? '#10b981' : 'var(--muted)' }">{{ p.iban_masked ? 'Tanımlı' : 'Eksik' }}</div>
                    <div class="pf-stat-label">IBAN</div>
                </div>
            </div>

            <div class="pf-action-row breadcrumb-action-row">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                        <li class="breadcrumb-item"><Link :href="route('index')" class="pf-link-primary">Ana Sayfa</Link></li>
                        <li class="breadcrumb-item active pf-text-muted">Satıcı Profilim</li>
                    </ol>
                </nav>
                <div class="pf-action-buttons">
                    <button type="button" class="pf-btn-save" @click="saveActiveTab"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                </div>
            </div>
        </div>

        <div v-if="p.verification_status === 'rejected' && p.rejection_reason" style="display:flex;align-items:flex-start;gap:10px;padding:.8rem 1rem;border-radius:10px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444;font-size:var(--fs-sm);">
            <i class="bi bi-x-circle-fill" style="flex-shrink:0;margin-top:2px;"></i>
            <div><strong>Başvurunuz reddedildi:</strong> {{ p.rejection_reason }}</div>
        </div>

        <div class="pf-content-area" style="padding:0 0 1rem;">

            <div class="pf-tab-bar wraping">
                <button class="pf-ptab bar-item" :class="{ active: activeTab === 'kisisel' }" @click="switchTab('kisisel')"><i class="bi bi-person me-1"></i> Kişisel</button>
                <button class="pf-ptab bar-item" :class="{ active: activeTab === 'sirket' }" @click="switchTab('sirket')"><i class="bi bi-building me-1"></i> Şirket</button>
                <button class="pf-ptab bar-item" :class="{ active: activeTab === 'odeme' }" @click="switchTab('odeme')"><i class="bi bi-credit-card me-1"></i> Ödeme</button>
                <button class="pf-ptab bar-item" :class="{ active: activeTab === 'belge' }" @click="switchTab('belge')"><i class="bi bi-file-earmark-person me-1"></i> Belge</button>
            </div>

            <div v-if="flash.success" style="display:flex;align-items:center;gap:8px;padding:.65rem 1rem;border-radius:8px;margin:.75rem .75rem 0;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#10b981;font-size:var(--fs-sm);">
                <i class="bi bi-check-circle-fill"></i> {{ flash.success }}
            </div>

            <!-- Kişisel -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'kisisel' }">
                <form @submit.prevent="saveKisisel" class="s-form" style="padding:1.25rem 1rem .5rem;">
                    <div class="s-2col">
                        <div class="s-field">
                            <label class="s-lbl">Ad Soyad <span class="pf-req">*</span></label>
                            <input class="pf-input" type="text" v-model="kisiselForm.name" placeholder="Ad Soyad">
                            <div v-if="kisiselForm.errors.name" class="pf-error">{{ kisiselForm.errors.name }}</div>
                        </div>
                        <div class="s-field">
                            <label class="s-lbl">E-posta</label>
                            <input class="pf-input" type="email" :value="p.email" disabled>
                            <div class="s-hint">E-posta değiştirilemez.</div>
                        </div>
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Telefon</label>
                        <div class="pf-input-pre">
                            <span class="pf-pre-label">+90</span>
                            <input type="tel" v-model="kisiselForm.phone" placeholder="5xx xxx xx xx">
                        </div>
                        <div v-if="kisiselForm.errors.phone" class="pf-error">{{ kisiselForm.errors.phone }}</div>
                    </div>
                    <div class="s-foot">
                        <button type="submit" class="pf-btn-save" :disabled="kisiselForm.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- Şirket -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'sirket' }">
                <form @submit.prevent="saveSirket" class="s-form" style="padding:1.25rem 1rem .5rem;">
                    <div class="s-hint mb-3" style="padding:.6rem .8rem;border-radius:8px;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);color:var(--muted);">
                        <i class="bi bi-info-circle me-1"></i> Şirket ve vergi bilgileri doğrulama sürecinde kullanılır. Kurumsal satıcılar için zorunludur.
                    </div>
                    <div class="s-2col">
                        <div class="s-field">
                            <label class="s-lbl">Şirket / Marka Adı</label>
                            <input class="pf-input" type="text" v-model="sirketForm.company_name" placeholder="Artirdim A.Ş.">
                            <div v-if="sirketForm.errors.company_name" class="pf-error">{{ sirketForm.errors.company_name }}</div>
                        </div>
                        <div class="s-field">
                            <label class="s-lbl">Vergi / TC Kimlik No</label>
                            <input class="pf-input" type="text" v-model="sirketForm.tax_number" placeholder="10 veya 11 hane" maxlength="11">
                            <div v-if="sirketForm.errors.tax_number" class="pf-error">{{ sirketForm.errors.tax_number }}</div>
                        </div>
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">Doğrulama Durumu</label>
                        <div style="display:flex;align-items:center;gap:10px;padding:.7rem .9rem;border-radius:9px;background:var(--bg-soft);border:1px solid var(--border);">
                            <span :style="{ width:'9px',height:'9px',borderRadius:'50%',flexShrink:0,background: st.text }"></span>
                            <span :style="{ fontSize:'var(--fs-sm)',fontWeight:600,color: st.text }">{{ st.label }}</span>
                            <span v-if="p.verified_at" style="margin-left:auto;font-size:var(--fs-xs);color:var(--muted);">{{ p.verified_at }}</span>
                        </div>
                    </div>
                    <div class="s-foot">
                        <button type="submit" class="pf-btn-save" :disabled="sirketForm.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- Ödeme -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'odeme' }">
                <form @submit.prevent="saveOdeme" class="s-form" style="padding:1.25rem 1rem .5rem;">
                    <div class="s-hint mb-3" style="padding:.6rem .8rem;border-radius:8px;background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.2);color:var(--muted);">
                        <i class="bi bi-shield-lock me-1"></i> IBAN bilginiz şifreli olarak saklanır ve yalnızca ödeme transferlerinde kullanılır.
                    </div>
                    <div class="s-field">
                        <label class="s-lbl">IBAN <span class="pf-req">*</span></label>
                        <div class="pf-input-pre">
                            <span class="pf-pre-label">TR</span>
                            <input type="text" :value="ibanDisplay" @input="onIbanInput" placeholder="00 0000 0000 0000 0000 0000 00" maxlength="30">
                        </div>
                        <div v-if="odemeForm.errors.iban" class="pf-error">{{ odemeForm.errors.iban }}</div>
                        <div class="s-hint">TR ile başlayan 26 haneli IBAN. Örn: TR33 0006 1005 1978 6457 8413 26</div>
                    </div>
                    <div v-if="p.iban_masked" style="display:flex;align-items:center;gap:8px;padding:.65rem .9rem;border-radius:8px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);">
                        <i class="bi bi-check-circle" style="color:#10b981;"></i>
                        <span style="font-size:var(--fs-sm);color:var(--muted);">Kayıtlı:</span>
                        <span style="font-size:var(--fs-sm);font-weight:700;color:var(--text);letter-spacing:.05em;">{{ p.iban_masked }}</span>
                    </div>
                    <div class="s-foot">
                        <button type="submit" class="pf-btn-save" :disabled="odemeForm.processing"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- Belge -->
            <div class="s-panel" :class="{ 's-active': activeTab === 'belge' }">
                <div class="s-form" style="padding:1.25rem 1rem .5rem;">
                    <div class="s-hint mb-3" style="padding:.6rem .8rem;border-radius:8px;background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.2);color:var(--muted);">
                        <i class="bi bi-exclamation-triangle me-1" style="color:#f59e0b;"></i> Kimlik belgesi yüklemeniz satıcı hesabınızın onaylanması için zorunludur. Belge 48 saat içinde incelenir.
                    </div>

                    <div v-if="p.has_document" style="display:flex;align-items:center;gap:10px;padding:.75rem 1rem;border-radius:9px;background:var(--bg-soft);border:1px solid var(--border);margin-bottom:1rem;">
                        <i class="bi bi-file-earmark-check" style="font-size:1.4rem;color:#10b981;flex-shrink:0;"></i>
                        <div>
                            <div style="font-weight:600;font-size:var(--fs-sm);">Belge yüklendi</div>
                            <div style="font-size:var(--fs-xs);color:var(--muted);">{{ p.document_updated }} tarihinde güncellendi</div>
                        </div>
                        <span class="a-badge" :class="docBadgeClass" style="margin-left:auto;">{{ docStatusLabel[p.verification_status] }}</span>
                    </div>

                    <form @submit.prevent="uploadDoc">
                        <div class="s-field">
                            <label class="s-lbl">{{ p.has_document ? 'Belgeyi Güncelle' : 'Kimlik Belgesi Yükle' }}</label>
                            <label for="id_document" style="display:flex;align-items:center;gap:14px;padding:.9rem 1rem;border-radius:10px;border:1.5px dashed var(--border);background:var(--bg-soft);cursor:pointer;" :style="{ borderColor: docName ? 'rgba(16,185,129,.4)' : 'var(--border)' }">
                                <i class="bi" :class="docName ? 'bi-file-earmark-check' : 'bi-cloud-upload'" :style="{ fontSize:'1.6rem', color: docName ? '#10b981' : 'var(--muted)', flexShrink:0 }"></i>
                                <div>
                                    <div style="font-weight:600;font-size:var(--fs-sm);">{{ docName || 'Dosya seç veya sürükle' }}</div>
                                    <div style="font-size:var(--fs-xs);color:var(--muted);">JPG, PNG, PDF · Maks. 5MB · Nüfus cüzdanı veya pasaport</div>
                                </div>
                            </label>
                            <input type="file" id="id_document" accept=".jpg,.jpeg,.png,.pdf" class="d-none" @change="onDocChange">
                            <div v-if="docForm.errors.id_document" class="pf-error">{{ docForm.errors.id_document }}</div>
                        </div>
                        <div class="s-foot">
                            <button type="submit" class="pf-btn-save" :disabled="docForm.processing"><i class="bi bi-cloud-upload me-1"></i> Yükle</button>
                        </div>
                    </form>

                    <div style="margin-top:.5rem;">
                        <div v-for="(t, i) in ['Belge tüm köşeleri görünür şekilde net çekilmiş olmalı','Ad, soyad ve TC/vergi numarası okunabilir olmalı','Belgeler yalnızca kimlik doğrulama için kullanılır']" :key="i" style="display:flex;align-items:center;gap:8px;padding:.4rem 0;font-size:var(--fs-xs);color:var(--muted);">
                            <i class="bi bi-check2" style="color:#10b981;flex-shrink:0;"></i> {{ t }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
