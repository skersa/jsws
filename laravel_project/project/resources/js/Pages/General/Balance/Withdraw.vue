<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    presets: Array,
    formatted_balance: String,
});

const page = usePage();
const flashError = computed(() => page.props.flash?.error || null);

const form = useForm({
    amount: '',
    iban: '',
});

const activePreset = ref(null);
function setWithdraw(v) {
    form.amount = v;
    activePreset.value = v;
}

function submit() {
    form.post(route('general.balance.withdraw'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Para Çek" />
    <div class="au-page-wrap">

        <div class="au-page-head mb-4">
            <div class="au-head-left">
                <Link :href="route('general.balance.index')" class="au-back-link">
                    <i class="bi bi-arrow-left"></i>
                </Link>
                <div>
                    <h1 class="au-page-title">Para Çek</h1>
                    <div class="text-muted small">
                        <i class="bi bi-bank me-1"></i> Kazancını IBAN adresine aktar
                    </div>
                </div>
            </div>
        </div>

        <div v-if="Object.keys(form.errors).length || flashError" class="admin-card mb-4 alert-card-danger">
            <div class="card-body p-3 text-danger d-flex align-items-center gap-2">
                <i class="bi bi-shield-exclamation fs-5"></i>
                <div>{{ flashError || form.errors.amount || form.errors.iban }}</div>
            </div>
        </div>

        <div class="card mb-4" style="box-shadow:none !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="section-label m-0">Kullanılabilir Bakiye</div>
                    <div class="text-muted small fw-semibold">Çekebileceğin maksimum tutar</div>
                </div>
                <div class="display-6 fw-bold" style="color: var(--primary);">{{ formatted_balance }}</div>
            </div>
        </div>

        <form @submit.prevent="submit" id="withdrawForm">

            <div class="admin-card mb-4">
                <div class="card-body p-4">
                    <div class="section-label mb-3">Çekilecek Tutar</div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button v-for="preset in presets" :key="preset" type="button" class="btn-preset" :class="{ active: activePreset === preset }" @click="setWithdraw(preset)">
                            {{ new Intl.NumberFormat('tr-TR').format(preset) }} ₺
                        </button>
                    </div>

                    <div class="pf-field mb-4">
                        <label class="pf-label mb-2 fw-semibold">Tutar (₺) <span class="text-danger">*</span></label>
                        <input class="pf-input form-control" :class="{ 'is-invalid': form.errors.amount }"
                               type="number" step="0.01" min="10" v-model="form.amount"
                               placeholder="0,00" data-testid="withdraw-amount">
                        <div v-if="form.errors.amount" class="text-danger small mt-1">{{ form.errors.amount }}</div>
                    </div>

                    <div class="pf-field">
                        <label class="pf-label mb-2 fw-semibold">IBAN <span class="text-danger">*</span></label>
                        <input class="pf-input form-control" :class="{ 'is-invalid': form.errors.iban }"
                               type="text" v-model="form.iban"
                               placeholder="TR__ ____ ____ ____ ____ ____ __" maxlength="34" data-testid="withdraw-iban">
                        <div v-if="form.errors.iban" class="text-danger small mt-1">{{ form.errors.iban }}</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-admin-pri w-100 justify-content-center" data-testid="withdraw-submit" :disabled="form.processing">
                <i class="bi bi-cash-coin me-1"></i> Çekim Talebi Oluştur
            </button>
        </form>
    </div>
</template>
