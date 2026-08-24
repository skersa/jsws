<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    transaction: Object,
});

const copied = ref(false);
function copyValue(text) {
    navigator.clipboard.writeText(text).then(() => {
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    });
}
</script>

<template>
    <Head title="İşlem Detayı" />
    <div class="au-page-wrap">

        <div class="au-page-head mb-4">
            <div class="au-head-left">
                <Link :href="route('general.balance.index')" class="au-back-link">
                    <i class="bi bi-arrow-left"></i>
                </Link>
                <div>
                    <h1 class="au-page-title">İşlem Detayı</h1>
                </div>
            </div>
        </div>

        <div class="admin-card mx-auto">
            <div class="card-body p-4 text-center border-bottom border-soft">
                <div class="au-tx-detail-amount fw-bold mb-2" :class="transaction.is_credit ? 'text-success' : 'text-danger'">
                    {{ transaction.formatted_amount }}
                </div>

                <div class="mb-2">
                    <span class="a-badge" :class="transaction.status === 'completed' ? 'success' : (transaction.status === 'pending' ? 'warning' : 'danger')">
                        <i class="bi me-1" :class="transaction.status === 'completed' ? 'bi-check-circle-fill' : (transaction.status === 'pending' ? 'bi-clock-history' : 'bi-x-circle-fill')"></i>
                        {{ transaction.status_label }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="d-flex flex-column gap-3">

                    <div class="p-3 border rounded info-row-bg d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-semibold">İşlem Türü</span>
                        <span class="fw-bold target-text-color">{{ transaction.type_label }}</span>
                    </div>

                    <div class="p-3 border rounded info-row-bg d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-semibold">Açıklama</span>
                        <span class="fw-medium target-text-color text-end" style="max-width: 60%;">{{ transaction.description }}</span>
                    </div>

                    <div class="p-3 border rounded info-row-bg d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-semibold">Referans No</span>
                        <div class="d-flex align-items-center gap-2">
                            <code class="fw-bold text-primary">{{ transaction.reference }}</code>
                            <button type="button" class="btn btn-sm btn-copy-modern py-1 px-2" :class="{ copied }" style="border-radius: var(--radius-sm);" @click="copyValue(transaction.reference)">
                                <i class="bi" :class="copied ? 'bi-check2' : 'bi-clipboard'"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-3 border rounded info-row-bg d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-semibold">Ödeme Yöntemi</span>
                        <span class="fw-semibold target-text-color">{{ transaction.payment_method }}</span>
                    </div>

                    <div class="p-3 border rounded info-row-bg d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-semibold">İşlem Öncesi Bakiye</span>
                        <span class="fw-semibold text-muted">{{ transaction.balance_before }}</span>
                    </div>

                    <div class="p-3 border rounded info-row-bg d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-semibold">İşlem Sonrası Bakiye</span>
                        <span class="fw-bold target-text-color">{{ transaction.balance_after }}</span>
                    </div>

                    <div class="p-3 border rounded info-row-bg d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-semibold">Tarih</span>
                        <span class="text-muted fw-medium">{{ transaction.created_at }}</span>
                    </div>

                </div>

                <div class="mt-4 pt-2 text-center">
                    <Link :href="route('general.balance.index')" class="btn-admin-sec text-decoration-none w-100">
                        <i class="bi bi-arrow-left me-1"></i> Listeye Geri Dön
                    </Link>
                </div>
            </div>
        </div>

    </div>
</template>
