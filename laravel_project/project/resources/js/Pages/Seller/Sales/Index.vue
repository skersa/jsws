<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({ orders: Object });

const page = usePage();
const flash = computed(() => page.props.flash || {});
</script>

<template>
    <Head title="Satışlarım" />
    <div class="dash-wrap py-4">

        <div class="admin-toolbar dash-hero">
            <div>
                <div class="toolbar-title">Satışlarım</div>
                <div class="dash-hero-sub">Sattığın ürünlerin ödeme, kargo ve teslimat durumunu yönet.</div>
            </div>
        </div>

        <div v-if="flash.success" class="alert alert-success" style="border-radius:12px">{{ flash.success }}</div>
        <div v-if="flash.error" class="alert alert-danger" style="border-radius:12px">{{ flash.error }}</div>

        <div class="admin-card" data-testid="sales-index-card">
            <div v-if="!orders.data.length" class="pf-empty">
                <div class="pf-empty-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="pf-empty-title">Henüz satışın yok</div>
                <div class="pf-empty-sub">Bir ürünün satıldığında burada görünecek.</div>
            </div>
            <template v-else>
                <div class="pf-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Sipariş</th>
                                <th>Alıcı</th>
                                <th>Tutar</th>
                                <th>Durum</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders.data" :key="order.id" :data-testid="'sale-row-' + order.id">
                                <td>
                                    <div class="dash-item">
                                        <img class="a-avatar" :src="order.cover_url" alt="">
                                        <div>
                                            <Link class="dash-item-title" :href="order.show_url">{{ order.title }}</Link>
                                            <div class="dash-muted" style="font-size:11px">{{ order.order_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="dash-muted">{{ order.buyer_name }}</td>
                                <td class="dash-amount">{{ order.amount }}</td>
                                <td><span class="ord-status-pill" :style="{ background: order.status_color }"><i class="bi" :class="order.status_icon"></i> {{ order.status_label }}</span></td>
                                <td><Link :href="order.show_url" class="btn-admin-ghost" :data-testid="'sale-detail-btn-' + order.id">Yönet</Link></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="dash-mt">
                    <Pagination v-if="orders.has_pages" :links="orders.links" />
                </div>
            </template>
        </div>
    </div>
</template>
