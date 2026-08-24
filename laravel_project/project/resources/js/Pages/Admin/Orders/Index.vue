<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ status: String, counts: Object, orders: Object });
const page = usePage();
const flash = computed(() => page.props.flash || {});

const tabs = computed(() => [
    { key: 'all', label: `Tümü (${props.counts.all})` },
    { key: 'disputed', label: `Anlaşmazlık (${props.counts.disputed})` },
    { key: 'active', label: `Devam Eden (${props.counts.active})` },
    { key: 'completed', label: `Tamamlanan (${props.counts.completed})` },
]);

const cur = computed(() => props.status || 'all');
function tabHref(key) {
    return key === 'all' ? route('admin.orders.index') : route('admin.orders.index', { status: key });
}
</script>

<template>
    <Head title="Siparişler & Anlaşmazlıklar" />
    <div class="dash-wrap py-4">
        <div class="admin-toolbar dash-hero">
            <div>
                <div class="toolbar-title">Siparişler & Anlaşmazlıklar</div>
                <div class="dash-hero-sub">Tüm siparişleri izleyin, anlaşmazlıkları çözün.</div>
            </div>
        </div>

        <div v-if="flash.success" class="alert alert-success" style="border-radius:12px">{{ flash.success }}</div>

        <div class="admin-card orders-tabs" data-testid="admin-orders-tabs">
            <Link v-for="t in tabs" :key="t.key" :href="tabHref(t.key)"
                  :class="cur === t.key ? 'btn-admin-pri' : 'btn-admin-ghost'"
                  :data-testid="`admin-orders-tab-${t.key}`">{{ t.label }}</Link>
        </div>

        <div class="admin-card" data-testid="admin-orders-card">
            <template v-if="!orders.data.length">
                <div class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-inbox"></i></div><div class="pf-empty-title">Kayıt yok</div></div>
            </template>
            <template v-else>
                <div class="pf-table-responsive">
                    <table class="admin-table">
                        <thead><tr><th>Sipariş No</th><th>Ürün</th><th>Alıcı</th><th>Satıcı</th><th>Tutar</th><th>Durum</th><th></th></tr></thead>
                        <tbody>
                            <tr v-for="o in orders.data" :key="o.id" :data-testid="`admin-order-row-${o.id}`">
                                <td class="dash-muted" style="font-size:12px">{{ o.order_number }}</td>
                                <td>{{ o.title }}</td>
                                <td class="dash-muted">{{ o.buyer_name }}</td>
                                <td class="dash-muted">{{ o.seller_name }}</td>
                                <td class="dash-amount">{{ o.amount }}</td>
                                <td><span class="ord-status-pill" :style="{ background: o.status_color }"><i class="bi" :class="o.status_icon"></i> {{ o.status_label }}</span></td>
                                <td><Link :href="o.show_url" class="btn-admin-ghost">İncele</Link></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="orders.has_pages" class="dash-mt fl-pagination">
                    <Link v-for="(l, i) in orders.links" :key="i" :href="l.url || '#'"
                          class="pf-pagination-item" :class="{ active: l.active, disabled: !l.url }" v-html="l.label" />
                </div>
            </template>
        </div>
    </div>
</template>
