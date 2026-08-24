<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({ orders: Object });
const page = usePage();
const flash = computed(() => page.props.flash || {});
</script>

<template>
    <Head title="Siparişlerim" />
    <div class="dash-wrap py-4">
        <div class="admin-toolbar dash-hero">
            <div>
                <div class="toolbar-title">Siparişlerim</div>
                <div class="dash-hero-sub">Kazandığın açık artırmaların ödeme ve kargo durumunu buradan takip et.</div>
            </div>
        </div>

        <div v-if="flash.success" class="alert alert-success" style="border-radius:12px">{{ flash.success }}</div>
        <div v-if="flash.error" class="alert alert-danger" style="border-radius:12px">{{ flash.error }}</div>

        <div class="admin-card" data-testid="orders-index-card">
            <div v-if="!orders.data.length" class="pf-empty">
                <div class="pf-empty-icon"><i class="bi bi-bag-check"></i></div>
                <div class="pf-empty-title">Henüz siparişin yok</div>
                <div class="pf-empty-sub">Bir açık artırma kazandığında siparişin burada görünecek.</div>
                <Link :href="route('browse.auctions')" class="btn-admin-pri dash-mt">Müzayedeleri Keşfet</Link>
            </div>
            <template v-else>
                <div class="pf-table-responsive">
                    <table class="admin-table">
                        <thead><tr><th>Sipariş</th><th>Satıcı</th><th>Tutar</th><th>Durum</th><th></th></tr></thead>
                        <tbody>
                            <tr v-for="order in orders.data" :key="order.id" :data-testid="`order-row-${order.id}`">
                                <td>
                                    <div class="dash-item">
                                        <img class="a-avatar" :src="order.cover_url" alt="">
                                        <div>
                                            <Link class="dash-item-title" :href="order.show_url">{{ order.title }}</Link>
                                            <div class="dash-muted" style="font-size:11px">{{ order.order_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="dash-muted">{{ order.seller_name }}</td>
                                <td class="dash-amount">{{ order.amount }}</td>
                                <td><span class="ord-status-pill" :style="{ background: order.status_color }"><i class="bi" :class="order.status_icon"></i> {{ order.status_label }}</span></td>
                                <td><Link :href="order.show_url" class="btn-admin-ghost" :data-testid="`order-detail-btn-${order.id}`">Detay</Link></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination v-if="orders.has_pages" :links="orders.links" />
            </template>
        </div>
    </div>
</template>
