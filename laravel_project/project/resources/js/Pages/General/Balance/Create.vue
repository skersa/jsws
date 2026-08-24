<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    presets: Array,
});

const form = useForm({
    amount: '',
    payment_method: 'credit_card',
    card_holder: '',
    card_number: '',
    card_expiry: '',
    card_cvv: '',
});

const activePreset = ref(null);

function setAmount(val) {
    form.amount = val;
    activePreset.value = val;
}

function onAmountInput() {
    activePreset.value = null;
}

function selectMethod(method) {
    form.payment_method = method;
}

const isCard = computed(() => form.payment_method === 'credit_card');

function formatCardNumber(e) {
    let val = e.target.value.replace(/\D/g, '').slice(0, 16);
    form.card_number = val.match(/.{1,4}/g)?.join(' ') ?? val;
}

function formatExpiry(e) {
    let val = e.target.value.replace(/\D/g, '').slice(0, 4);
    if (val.length >= 3) val = val.slice(0, 2) + '/' + val.slice(2);
    form.card_expiry = val;
}

const copiedKey = ref(null);
function copyValue(text, key) {
    navigator.clipboard.writeText(text).then(() => {
        copiedKey.value = key;
        setTimeout(() => (copiedKey.value = null), 2000);
    });
}

function submit() {
    form.post(route('general.balance.store'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Bakiye Yükle" />
    <div class="au-page-wrap balance-create" :class="'pm-' + form.payment_method">

        <div class="au-page-head mb-4">
            <div class="au-head-left">
                <Link :href="route('general.balance.index')" class="au-back-link">
                    <i class="bi bi-arrow-left"></i>
                </Link>
                <div>
                    <h1 class="au-page-title">Bakiye Yükle</h1>
                    <div class="text-muted small">
                        <i class="bi bi-shield-lock-fill text-success me-1"></i> 256-Bit SSL korumalı güvenli ödeme altyapısı
                    </div>
                </div>
            </div>
        </div>

        <div v-if="Object.keys(form.errors).length" class="admin-card mb-4 alert-card-danger">
            <div class="card-body p-3 text-danger d-flex align-items-center gap-2">
                <i class="bi bi-shield-exclamation fs-5"></i>
                <div>{{ Object.keys(form.errors).length }} hata bulundu, lütfen bilgilerinizi kontrol edin.</div>
            </div>
        </div>

        <form @submit.prevent="submit" id="paymentForm">

            <div class="admin-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="section-label">Yüklenecek Tutar</div>
                        <span class="a-badge info"><i class="bi bi-lock-fill"></i> Güvenli İşlem</span>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button v-for="preset in presets" :key="preset" type="button"
                                class="btn-preset" :class="{ active: activePreset === preset }"
                                @click="setAmount(preset)">
                            {{ new Intl.NumberFormat('tr-TR').format(preset) }} ₺
                        </button>
                    </div>

                    <div class="pf-field">
                        <label class="pf-label mb-2 fw-semibold">Tutar Girin <span class="text-danger">*</span></label>
                        <div class="input-group customs-input-group has-validation">
                            <span class="input-group-text fw-bold text-muted border-end-0">₺</span>
                            <input class="pf-input form-control border-start-0 dynamic-input amount-input-style"
                                   :class="{ 'is-invalid-error': form.errors.amount }"
                                   type="number" id="amount" v-model="form.amount" @input="onAmountInput"
                                   min="10" max="50000" step="0.01" placeholder="Örn: 500" required>
                        </div>

                        <div v-if="form.errors.amount" class="pf-input-error-msg">
                            <i class="bi bi-exclamation-circle"></i>
                            <span>{{ form.errors.amount }}</span>
                        </div>

                        <div class="text-muted mt-2" style="font-size: var(--fs-xs);">
                            <i class="bi bi-info-circle me-1"></i> Minimum 10 ₺ · Maksimum 50.000 ₺ arası anında bakiye yükleyebilirsiniz.
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card mb-4">
                <div class="card-body p-4">
                    <div class="section-label mb-3">Ödeme Yöntemi Seçin</div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="payment-tile-modern" :class="{ active: form.payment_method === 'credit_card' }" @click="selectMethod('credit_card')">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="tile-icon"><i class="bi bi-credit-card-2-front"></i></div>
                                    <div>
                                        <div class="tile-title">Kredi / Banka Kartı</div>
                                        <div class="tile-desc">Anında Yüklenir</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="payment-tile-modern" :class="{ active: form.payment_method === 'bank_transfer' }" @click="selectMethod('bank_transfer')">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="tile-icon"><i class="bi bi-bank"></i></div>
                                    <div>
                                        <div class="tile-title">Havale / EFT / Fast</div>
                                        <div class="tile-desc">1-2 İş Günü</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="payment-tile-modern" :class="{ active: form.payment_method === 'papara' }" @click="selectMethod('papara')">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="tile-icon"><i class="bi bi-wallet2"></i></div>
                                    <div>
                                        <div class="tile-title">Papara ile Öde</div>
                                        <div class="tile-desc">7/24 Hızlı Transfer</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div v-if="form.errors.payment_method" class="pf-input-error-msg mt-2">
                        <i class="bi bi-exclamation-circle"></i>
                        <span>{{ form.errors.payment_method }}</span>
                    </div>
                </div>
            </div>

            <div v-show="isCard" class="admin-card mb-4" id="cardFields">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="section-label">Kart Bilgileri</div>
                        <span class="text-muted small"><i class="bi bi-shield-check text-success"></i> 3D Secure Aktif</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="pf-label mb-2">Kart Sahibi</label>
                            <input class="pf-input form-control dynamic-input" :class="{ 'is-invalid-error': form.errors.card_holder }" type="text" v-model="form.card_holder" :required="isCard" placeholder="Ad Soyad">
                            <div v-if="form.errors.card_holder" class="pf-input-error-msg"><i class="bi bi-exclamation-circle"></i> <span>{{ form.errors.card_holder }}</span></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="pf-label mb-2">Kart Numarası</label>
                            <input class="pf-input form-control dynamic-input" :class="{ 'is-invalid-error': form.errors.card_number }" type="text" :value="form.card_number" :required="isCard" placeholder="0000 0000 0000 0000" maxlength="19" @input="formatCardNumber">
                            <div v-if="form.errors.card_number" class="pf-input-error-msg"><i class="bi bi-exclamation-circle"></i> <span>{{ form.errors.card_number }}</span></div>
                        </div>
                        <div class="col-6">
                            <label class="pf-label mb-2">Son Kullanma Tarihi (SKT)</label>
                            <input class="pf-input form-control dynamic-input" :class="{ 'is-invalid-error': form.errors.card_expiry }" type="text" :value="form.card_expiry" :required="isCard" placeholder="AA/YY" maxlength="5" @input="formatExpiry">
                            <div v-if="form.errors.card_expiry" class="pf-input-error-msg"><i class="bi bi-exclamation-circle"></i> <span>{{ form.errors.card_expiry }}</span></div>
                        </div>
                        <div class="col-6">
                            <label class="pf-label mb-2">Güvenlik Kodu (CVV)</label>
                            <input class="pf-input form-control dynamic-input" :class="{ 'is-invalid-error': form.errors.card_cvv }" type="password" v-model="form.card_cvv" :required="isCard" placeholder="•••" maxlength="4">
                            <div v-if="form.errors.card_cvv" class="pf-input-error-msg"><i class="bi bi-exclamation-circle"></i> <span>{{ form.errors.card_cvv }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="form.payment_method === 'bank_transfer'" class="admin-card mb-4" id="bankFields">
                <div class="card-body p-4">
                    <div class="section-label mb-3">Banka Transfer Bilgileri</div>

                    <div class="p-3 mb-3 border rounded d-flex gap-2 text-muted alert-box-info">
                        <i class="bi bi-exclamation-circle-fill text-primary"></i>
                        <div>Transferi yaparken <b>Açıklama</b> alanına <b>Kullanıcı Adınızı veya ID</b> bilginizi eksiksiz yazmanız işlemleri hızlandıracaktır.</div>
                    </div>

                    <div class="p-3 border rounded d-flex justify-content-between align-items-center info-row-bg">
                        <div>
                            <div class="text-muted small fw-semibold">Alıcı / IBAN</div>
                            <div class="fw-bold tracking-wide mt-1 target-text-color">TR00 0000 0000 0000 0000 0000 00</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-copy-modern" :class="{ copied: copiedKey === 'iban' }" @click="copyValue('TR00000000000000000000000000', 'iban')">
                            <i class="bi me-1" :class="copiedKey === 'iban' ? 'bi-check-all' : 'bi-clipboard'"></i> <span>{{ copiedKey === 'iban' ? 'Kopyalandı!' : 'Kopyala' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div v-show="form.payment_method === 'papara'" class="admin-card mb-4" id="paparaFields">
                <div class="card-body p-4">
                    <div class="section-label mb-3">Papara Hesap Bilgileri</div>

                    <div class="p-3 mb-3 border rounded d-flex gap-2 text-muted alert-box-warning">
                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                        <div>7/24 anında transfer. Açıklama kısmına kullanıcı ID numaranızı girmeyi unutmayınız.</div>
                    </div>

                    <div class="p-3 border rounded d-flex justify-content-between align-items-center info-row-bg">
                        <div>
                            <div class="text-muted small fw-semibold">Papara Numarası</div>
                            <div class="fw-bold tracking-wide mt-1 target-text-color">1234567890</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-copy-modern" :class="{ copied: copiedKey === 'papara' }" @click="copyValue('1234567890', 'papara')">
                            <i class="bi me-1" :class="copiedKey === 'papara' ? 'bi-check-all' : 'bi-clipboard'"></i> <span>{{ copiedKey === 'papara' ? 'Kopyalandı!' : 'Kopyala' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <Link :href="route('general.balance.index')" class="btn-admin-sec text-decoration-none">
                        Vazgeç
                    </Link>
                    <button type="submit" class="btn-admin-pri" :disabled="form.processing">
                        <i class="bi bi-shield-check"></i> Güvenli Ödemeyi Başlat
                    </button>
                </div>
            </div>

        </form>
    </div>
</template>
