<script>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { h } from 'vue';
export default {
    layout: (hh, page) => h(AuthLayout, { activeAuctions: page.props.activeAuctions || 0 }, () => page),
};
</script>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ activeAuctions: Number });

const form = useForm({
    role: 'buyer',
    name: '', username: '', email: '', phone: '',
    company_name: '', tax_number: '', iban: '', id_document: null,
    password: '', password_confirmation: '', terms: false,
});

const step = ref(1);
const termsError = ref(false);
const mismatchError = ref(false);
const idFileName = ref('');
const localErrors = reactive({});

const isSeller = computed(() => form.role === 'seller');
const finalStepLabel = computed(() => isSeller.value ? 'Adım 3 / 3' : 'Adım 2 / 2');

function clearErrs(fields) { fields.forEach(f => { delete localErrors[f]; }); }
function err(field) { return form.errors[field] || localErrors[field]; }

function validateStep1() {
    clearErrs(['name', 'username', 'email', 'phone', 'role']);
    if (!form.role) localErrors.role = 'Hesap türü seçiniz.';
    if (!form.name.trim()) localErrors.name = 'Ad Soyad zorunludur.';
    const u = form.username.trim();
    if (!u) localErrors.username = 'Kullanıcı adı zorunludur.';
    else if (u.length < 3) localErrors.username = 'Kullanıcı adı en az 3 karakter olmalı.';
    else if (u.length > 30) localErrors.username = 'Kullanıcı adı en fazla 30 karakter olabilir.';
    else if (!/^[a-zA-Z0-9_.]+$/.test(u)) localErrors.username = 'Sadece harf, rakam, nokta ve alt çizgi kullanılabilir.';
    const e = form.email.trim();
    if (!e) localErrors.email = 'E-posta zorunludur.';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)) localErrors.email = 'Geçerli bir e-posta adresi girin.';
    if (!form.phone.trim()) localErrors.phone = 'GSM numarası zorunludur.';
    return !['name', 'username', 'email', 'phone', 'role'].some(f => localErrors[f]);
}

function validateStep2() {
    clearErrs(['tax_number', 'iban', 'id_document']);
    if (!form.tax_number.trim()) localErrors.tax_number = 'Vergi numarası zorunludur.';
    else if (form.tax_number.trim().length > 20) localErrors.tax_number = 'Vergi numarası en fazla 20 karakter olabilir.';
    const ib = form.iban.trim();
    if (!ib) localErrors.iban = 'IBAN zorunludur.';
    else if (ib.length < 26 || ib.length > 34) localErrors.iban = 'IBAN 26-34 karakter arası olmalıdır.';
    if (!form.id_document) localErrors.id_document = 'Kimlik belgesi zorunludur.';
    return !['tax_number', 'iban', 'id_document'].some(f => localErrors[f]);
}

function goStep1Next() {
    if (!validateStep1()) return;
    if (isSeller.value) step.value = 2;
    else step.value = 3;
}
function goStep2Next() {
    if (!validateStep2()) return;
    step.value = 3;
}

const strength = computed(() => {
    const p = form.password || '';
    let score = 0;
    if (p.length >= 8) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[0-9]/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;
    return score;
});
const strengthMeta = computed(() => {
    const map = [
        { w: '0%', c: 'transparent', t: '' },
        { w: '25%', c: '#ef4444', t: 'Zayıf' },
        { w: '50%', c: '#f59e0b', t: 'Orta' },
        { w: '75%', c: '#3b82f6', t: 'İyi' },
        { w: '100%', c: '#10b981', t: 'Güçlü' },
    ];
    return map[strength.value];
});

function onFile(e) {
    form.id_document = e.target.files[0] || null;
    idFileName.value = e.target.files[0]?.name || '';
}

function submit() {
    mismatchError.value = form.password !== form.password_confirmation;
    termsError.value = !form.terms;
    if (mismatchError.value || termsError.value) return;
    form.post(route('register'), { forceFormData: true });
}

onMounted(() => {
    if (form.errors.tax_number || form.errors.iban || form.errors.company_name || form.errors.id_document) {
        step.value = 2;
    } else if (form.errors.password) {
        step.value = 3;
    }
});
</script>

<template>
    <Head title="Kaydol" />
    <form class="form w-100" @submit.prevent="submit" enctype="multipart/form-data">
        <div class="auth-header text-center mb-8">
            <img src="/assets/media/logos/logo-light.svg" class="logo-light auth-logo" alt="Artirdim">
            <img src="/assets/media/logos/logo-dark.svg" class="logo-dark auth-logo" alt="Artirdim">
        </div>

        <!-- STEP 1 -->
        <div v-show="step === 1">
            <div class="mb-6">
                <label class="form-label text-muted fs-7 fw-semibold mb-3">Hesap Türü</label>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="d-block cursor-pointer" @click="form.role = 'buyer'">
                            <div class="role-card p-4 rounded-2 text-center" :class="{ selected: form.role === 'buyer' }">
                                <div class="symbol symbol-40px mx-auto mb-3">
                                    <div class="symbol-label rounded-circle role-icon-wrap">
                                        <i class="bi bi-person-fill fs-4"></i>
                                    </div>
                                </div>
                                <div class="fw-bold role-label">Alıcı</div>
                                <div class="text-muted fs-8 mt-1">Teklif ver, satın al</div>
                            </div>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="d-block cursor-pointer" @click="form.role = 'seller'">
                            <div class="role-card p-4 rounded-2 text-center" :class="{ selected: form.role === 'seller' }">
                                <div class="symbol symbol-40px mx-auto mb-3">
                                    <div class="symbol-label rounded-circle role-icon-wrap">
                                        <i class="bi bi-shop fs-4"></i>
                                    </div>
                                </div>
                                <div class="fw-bold role-label">Satıcı</div>
                                <div class="text-muted fs-8 mt-1">İlan ver, sat</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div v-if="err('role')" class="text-danger small mt-2">{{ err('role') }}</div>
            </div>

            <div class="fv-row mb-4">
                <div class="form-floating">
                    <input type="text" v-model="form.name" class="form-control" :class="{ 'is-invalid': err('name') }" placeholder="Ad Soyad" data-testid="register-name">
                    <label>Ad Soyad</label>
                </div>
                <div v-if="err('name')" class="text-danger small mt-1">{{ err('name') }}</div>
            </div>
            <div class="fv-row mb-4">
                <div class="form-floating">
                    <input type="text" v-model="form.username" maxlength="30" class="form-control" :class="{ 'is-invalid': err('username') }" placeholder="kullanici_adi" autocomplete="username" data-testid="register-username">
                    <label>Kullanıcı Adı</label>
                </div>
                <div v-if="err('username')" class="text-danger small mt-1">{{ err('username') }}</div>
            </div>
            <div class="fv-row mb-4">
                <div class="form-floating">
                    <input type="email" v-model="form.email" class="form-control" :class="{ 'is-invalid': err('email') }" placeholder="E-posta" data-testid="register-email">
                    <label>E-posta</label>
                </div>
                <div v-if="err('email')" class="text-danger small mt-1">{{ err('email') }}</div>
            </div>
            <div class="fv-row mb-6">
                <div class="form-floating">
                    <input type="text" v-model="form.phone" class="form-control" :class="{ 'is-invalid': err('phone') }" placeholder="Telefon" data-testid="register-phone">
                    <label>GSM Numarası</label>
                </div>
                <div v-if="err('phone')" class="text-danger small mt-1">{{ err('phone') }}</div>
            </div>

            <button type="button" class="btn btn-auth-primary btn-lg w-100" @click="goStep1Next" data-testid="register-step1-next">Devam et</button>
            <div class="text-center mt-4">
                <Link :href="route('login')" class="btn btn-auth-outline btn-lg w-100">Zaten hesabın var mı? Giriş yap</Link>
            </div>
        </div>

        <!-- STEP 2 (seller) -->
        <div v-show="step === 2">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted fs-8">Adım 2 / 3</span>
                <span class="text-primary fs-8 fw-semibold">Satıcı Doğrulama</span>
            </div>
            <div class="progress h-6px mb-8"><div class="progress-bar bg-primary" style="width:66%"></div></div>

            <div class="fv-row mb-4">
                <div class="form-floating">
                    <input type="text" v-model="form.company_name" class="form-control" :class="{ 'is-invalid': form.errors.company_name }" placeholder="Şirket Adı">
                    <label>Şirket Adı <span class="text-muted fs-8">(opsiyonel)</span></label>
                </div>
                <div v-if="form.errors.company_name" class="text-danger small mt-1">{{ form.errors.company_name }}</div>
            </div>
            <div class="fv-row mb-4">
                <div class="form-floating">
                    <input type="text" v-model="form.tax_number" class="form-control" :class="{ 'is-invalid': err('tax_number') }" placeholder="Vergi Numarası">
                    <label>Vergi Numarası</label>
                </div>
                <div v-if="err('tax_number')" class="text-danger small mt-1">{{ err('tax_number') }}</div>
            </div>
            <div class="fv-row mb-4">
                <div class="form-floating">
                    <input type="text" v-model="form.iban" maxlength="34" class="form-control" :class="{ 'is-invalid': err('iban') }" placeholder="IBAN" style="text-transform:uppercase;letter-spacing:1px">
                    <label>IBAN</label>
                </div>
                <div v-if="err('iban')" class="text-danger small mt-1">{{ err('iban') }}</div>
            </div>
            <div class="fv-row mb-6">
                <label class="form-label text-muted fs-7 fw-semibold mb-2">Kimlik Belgesi <span class="text-muted fs-8">(JPG, PNG veya PDF — maks. 5MB)</span></label>
                <input type="file" class="form-control" :class="{ 'is-invalid': err('id_document') }" accept=".jpg,.jpeg,.png,.pdf" @change="onFile" data-testid="register-id-document">
                <div v-if="idFileName" class="text-muted small mt-1">{{ idFileName }}</div>
                <div v-if="err('id_document')" class="text-danger small mt-1">{{ err('id_document') }}</div>
            </div>

            <div class="d-flex gap-3">
                <button type="button" class="btn btn-auth-outline btn-lg py-3 fw-semibold" style="width:30%" @click="step = 1">Geri</button>
                <button type="button" class="btn btn-auth-primary btn-lg py-3 fw-semibold flex-grow-1" @click="goStep2Next" data-testid="register-step2-next">Devam et</button>
            </div>
        </div>

        <!-- STEP 3 (password) -->
        <div v-show="step === 3">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted fs-8">{{ finalStepLabel }}</span>
                <span class="text-success fs-8 fw-semibold">Şifre</span>
            </div>
            <div class="progress h-6px mb-8"><div class="progress-bar bg-success" style="width:100%"></div></div>

            <div class="fv-row mb-4">
                <div class="form-floating position-relative" data-kt-password-meter="true">
                    <input type="password" v-model="form.password" class="form-control auth-input pe-5" :class="{ 'is-invalid': form.errors.password }" placeholder="Şifre" autocomplete="off" data-testid="register-password">
                    <label>Şifre</label>
                    <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0" data-kt-password-meter-control="visibility">
                        <i class="bi bi-eye-slash fs-2"></i><i class="bi bi-eye fs-2 d-none"></i>
                    </span>
                </div>
                <div v-if="form.errors.password" class="text-danger small mt-1">{{ form.errors.password }}</div>
            </div>

            <div class="mb-4">
                <div style="height:4px;border-radius:2px;background:var(--border,#e4e6ef);overflow:hidden;margin-bottom:5px">
                    <div :style="{ height: '100%', width: strengthMeta.w, background: strengthMeta.c, borderRadius: '2px', transition: 'width .3s, background .3s' }"></div>
                </div>
                <span :style="{ fontSize: '11px', fontWeight: 600, color: strengthMeta.c }">{{ strengthMeta.t }}</span>
            </div>

            <div class="fv-row mb-6">
                <div class="form-floating position-relative" data-kt-password-meter="true">
                    <input type="password" v-model="form.password_confirmation" class="form-control auth-input pe-5" placeholder="Şifre Tekrar" autocomplete="off" data-testid="register-password-confirm">
                    <label>Şifre Tekrar</label>
                    <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0" data-kt-password-meter-control="visibility">
                        <i class="bi bi-eye-slash fs-2"></i><i class="bi bi-eye fs-2 d-none"></i>
                    </span>
                </div>
                <div v-if="mismatchError" class="text-danger small mt-1">Şifreler eşleşmiyor.</div>
            </div>

            <div class="mb-8">
                <label class="form-check form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" v-model="form.terms" data-testid="register-terms">
                    <span class="form-check-label text-muted fs-7"><a href="#" class="text-primary">Kullanım koşullarını</a> okudum ve kabul ediyorum</span>
                </label>
                <div v-if="termsError" class="text-danger small mt-1">Kullanım koşullarını kabul etmelisiniz.</div>
            </div>

            <div class="d-flex gap-3">
                <button type="button" class="btn btn-auth-outline btn-lg py-3 fw-semibold" style="width:30%" @click="step = isSeller ? 2 : 1">Geri</button>
                <button type="submit" class="btn btn-auth-primary btn-lg py-3 fw-semibold flex-grow-1" :disabled="form.processing" data-testid="register-submit">
                    <span v-if="!form.processing">Kayıt Ol</span>
                    <span v-else>Lütfen bekleyin... <span class="spinner-border spinner-border-sm ms-2 align-middle"></span></span>
                </button>
            </div>
        </div>
    </form>
</template>
