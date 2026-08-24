<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
defineProps({ user: Object, stats: Object, myBids: Array, watchItems: Array });

function fmt(v) { return new Intl.NumberFormat('tr-TR').format(v || 0); }
function badge(status) {
    if (status === 'active') return { cls: 'success', text: 'Aktif' };
    if (status === 'sold') return { cls: 'info', text: 'Satıldı' };
    return { cls: 'warning', text: 'Bitti' };
}
</script>

<template>
    <Head title="Panelim" />
    <div class="dash-wrap py-4">
        <div class="admin-toolbar dash-hero">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="toolbar-title">Merhaba, {{ user.name }} 👋</div>
                    <div class="dash-hero-sub">Tekliflerini, favorilerini ve bakiyeni buradan takip et.</div>
                </div>
                <Link :href="route('general.balance.index')" class="btn-admin-pri" data-testid="dashboard-add-balance">
                    <i class="bi bi-wallet2"></i> Bakiye Yükle
                </Link>
            </div>
        </div>

        <div class="dash-stats">
            <div class="pf-stat-card">
                <div class="pf-stat-icon-wrapper dash-ic-blue"><i class="bi bi-wallet2"></i></div>
                <div><div class="pf-stat-number">{{ fmt(stats.balance) }} ₺</div><div class="pf-stat-label">Bakiye</div></div>
            </div>
            <div class="pf-stat-card">
                <div class="pf-stat-icon-wrapper dash-ic-green"><i class="bi bi-graph-up-arrow"></i></div>
                <div><div class="pf-stat-number">{{ stats.active_bids }}</div><div class="pf-stat-label">Aktif Teklif</div></div>
            </div>
            <div class="pf-stat-card">
                <div class="pf-stat-icon-wrapper dash-ic-pink"><i class="bi bi-heart-fill"></i></div>
                <div><div class="pf-stat-number">{{ stats.fav_count }}</div><div class="pf-stat-label">Favori</div></div>
            </div>
            <div class="pf-stat-card">
                <div class="pf-stat-icon-wrapper dash-ic-gold"><i class="bi bi-trophy-fill"></i></div>
                <div><div class="pf-stat-number">{{ stats.won_count }}</div><div class="pf-stat-label">Kazanılan</div></div>
            </div>
        </div>

        <div class="dash-grid">
            <div class="admin-card">
                <div class="admin-card-head">
                    <div class="admin-card-title"><i class="bi bi-clock-history"></i> Son Tekliflerim</div>
                    <Link href="/my-bids" class="btn-admin-sec">Tümü</Link>
                </div>
                <div v-if="!myBids.length" class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-hammer"></i></div>
                    <div class="pf-empty-title">Henüz teklif vermedin</div>
                    <div class="pf-empty-sub">Müzayedelere göz at ve ilk teklifini ver.</div>
                    <Link :href="route('browse.auctions')" class="btn-admin-pri dash-mt">Müzayedeleri Keşfet</Link>
                </div>
                <div v-else class="pf-table-responsive">
                    <table class="admin-table">
                        <thead><tr><th>İlan</th><th>Teklifim</th><th>Durum</th><th>Tarih</th></tr></thead>
                        <tbody>
                            <tr v-for="bid in myBids" :key="bid.id">
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
            </div>

            <div class="admin-card">
                <div class="admin-card-head">
                    <div class="admin-card-title"><i class="bi bi-heart"></i> Favorilerim</div>
                    <Link href="/favorites" class="btn-admin-sec">Tümü</Link>
                </div>
                <div v-if="!watchItems.length" class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-heart"></i></div>
                    <div class="pf-empty-title">Favori listen boş</div>
                    <div class="pf-empty-sub">Beğendiğin ilanları favorilere ekle.</div>
                </div>
                <div v-else class="dash-fav-grid">
                    <Link v-for="w in watchItems" :key="w.show_url" class="dash-fav-card" :href="w.show_url">
                        <img :src="w.cover_url" :alt="w.title">
                        <div class="dash-fav-body">
                            <div class="dash-fav-title">{{ w.title }}</div>
                            <div class="dash-fav-price">{{ w.display_price }}</div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
