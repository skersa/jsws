<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
defineProps({ bids: Object });

function badge(status) {
    if (status === 'active') return { cls: 'success', text: 'Aktif' };
    if (status === 'sold') return { cls: 'info', text: 'Satıldı' };
    return { cls: 'warning', text: 'Bitti' };
}
</script>

<template>
    <Head title="Tekliflerim" />
    <div class="dash-wrap py-4">
        <div class="admin-toolbar dash-hero">
            <div>
                <div class="toolbar-title">Tekliflerim</div>
                <div class="dash-hero-sub">Verdiğin tüm teklifleri buradan takip edebilirsin.</div>
            </div>
        </div>

        <div class="admin-card" data-testid="my-bids-card">
            <div v-if="!bids.data.length" class="pf-empty">
                <div class="pf-empty-icon"><i class="bi bi-hammer"></i></div>
                <div class="pf-empty-title">Henüz teklif vermedin</div>
                <div class="pf-empty-sub">Müzayedelere göz at ve ilk teklifini ver.</div>
                <Link :href="route('browse.auctions')" class="btn-admin-pri dash-mt">Müzayedeleri Keşfet</Link>
            </div>
            <template v-else>
                <div class="pf-table-responsive">
                    <table class="admin-table">
                        <thead><tr><th>İlan</th><th>Teklifim</th><th>Durum</th><th>Tarih</th></tr></thead>
                        <tbody>
                            <tr v-for="bid in bids.data" :key="bid.id">
                                <td>
                                    <div class="dash-item">
                                        <img class="a-avatar" :src="bid.cover_url" alt="">
                                        <Link class="dash-item-title" :href="bid.show_url">{{ bid.title }}</Link>
                                    </div>
                                </td>
                                <td class="dash-amount">{{ bid.amount }}</td>
                                <td><span class="a-badge" :class="badge(bid.status).cls">{{ badge(bid.status).text }}</span></td>
                                <td class="dash-muted">{{ bid.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination v-if="bids.has_pages" :links="bids.links" />
            </template>
        </div>
    </div>
</template>
