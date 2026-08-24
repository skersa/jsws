<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    admin_name: String, today: String, stats: Object, chart: Array,
    orderStatuses: Array, topSellers: Array, recentOrders: Array, activities: Array, links: Object,
});

const nf = (n) => new Intl.NumberFormat('tr-TR').format(n || 0);
const money = (n) => new Intl.NumberFormat('tr-TR').format(Math.round(n || 0)) + ' ₺';

const maxRev = computed(() => Math.max(1, ...props.chart.map((c) => c.revenue)));
const maxOrd = computed(() => Math.max(1, ...props.chart.map((c) => c.orders)));
const totalOrders = computed(() => props.orderStatuses.reduce((a, s) => a + s.count, 0));

function barHeight(c) {
    if (c.revenue > 0) return Math.max(6, Math.round((c.revenue / maxRev.value) * 150));
    if (c.orders > 0) return Math.max(6, Math.round((c.orders / maxOrd.value) * 60));
    return 3;
}
</script>

<template>
    <Head title="Admin Dashboard" />
    <div class="adm-wrap admin-fade">

        <div class="adm-hero">
            <div style="z-index:1">
                <h1>Yönetim Paneli</h1>
                <p>Hoş geldin, {{ admin_name }} · {{ today }}</p>
            </div>
            <div class="adm-live"><span class="dot"></span> Sistem Aktif</div>
        </div>

        <div class="adm-grid4">
            <div class="adm-stat" data-testid="stat-users">
                <div class="adm-stat-ic" style="background:rgba(21,94,239,.14);color:#3b82f6"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="adm-stat-num">{{ nf(stats.users) }}</div>
                    <div class="adm-stat-lbl">Toplam Kullanıcı</div>
                    <div class="adm-stat-sub" style="color:#10b981">↑ {{ stats.new_users_week }} bu hafta</div>
                </div>
            </div>
            <div class="adm-stat" data-testid="stat-auctions">
                <div class="adm-stat-ic" style="background:rgba(16,185,129,.14);color:#10b981"><i class="bi bi-hammer"></i></div>
                <div>
                    <div class="adm-stat-num">{{ nf(stats.active) }}</div>
                    <div class="adm-stat-lbl">Aktif Müzayede</div>
                    <div class="adm-stat-sub" style="color:var(--muted)">Toplam {{ nf(stats.auctions) }}</div>
                </div>
            </div>
            <div class="adm-stat" data-testid="stat-orders">
                <div class="adm-stat-ic" style="background:rgba(139,92,246,.14);color:#8b5cf6"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="adm-stat-num">{{ nf(stats.orders) }}</div>
                    <div class="adm-stat-lbl">Sipariş</div>
                    <div class="adm-stat-sub" style="color:#10b981">{{ stats.completed }} tamamlandı</div>
                </div>
            </div>
            <div class="adm-stat" data-testid="stat-revenue">
                <div class="adm-stat-ic" style="background:rgba(251,191,36,.14);color:#fbbf24"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="adm-stat-num">{{ money(stats.revenue) }}</div>
                    <div class="adm-stat-lbl">Toplam Ciro</div>
                    <div class="adm-stat-sub" style="color:#fbbf24">Komisyon {{ money(stats.commission) }}</div>
                </div>
            </div>
        </div>

        <div class="adm-grid-mini">
            <div class="adm-mini"><div class="adm-mini-lbl"><i class="bi bi-graph-up-arrow" style="color:#3b82f6"></i> Teklif</div><div class="adm-mini-num">{{ nf(stats.bids) }}</div></div>
            <div class="adm-mini"><div class="adm-mini-lbl"><i class="bi bi-shield-lock" style="color:#f59e0b"></i> Emanetteki Tutar</div><div class="adm-mini-num">{{ money(stats.escrow_held) }}</div></div>
            <div class="adm-mini"><div class="adm-mini-lbl"><i class="bi bi-exclamation-octagon" style="color:#ef4444"></i> Anlaşmazlık</div><div class="adm-mini-num">{{ stats.disputes }}</div></div>
            <div class="adm-mini"><div class="adm-mini-lbl"><i class="bi bi-person-check" style="color:#8b5cf6"></i> Onay Bekleyen</div><div class="adm-mini-num">{{ stats.pending }}</div></div>
        </div>

        <div class="adm-2col">
            <div class="adm-card" data-testid="admin-chart">
                <div class="adm-card-h">
                    <div class="adm-card-t"><i class="bi bi-bar-chart-line" style="color:var(--primary)"></i> Son 14 Gün — Sipariş Hacmi</div>
                    <span style="font-size:12px;color:var(--muted)">Ciro (tamamlanan) çubuk yüksekliğine yansır</span>
                </div>
                <div class="adm-chart">
                    <div v-for="(c, i) in chart" :key="i" class="adm-bar-col"
                         :title="`${c.label} · ${c.orders} sipariş · ${money(c.revenue)}`">
                        <div class="adm-bar" :style="{ height: barHeight(c) + 'px' }"></div>
                        <div class="adm-bar-lbl">{{ c.label }}</div>
                    </div>
                </div>

                <div style="margin-top:22px">
                    <div class="adm-card-t" style="font-size:13px;margin-bottom:14px"><i class="bi bi-pie-chart"></i> Sipariş Durum Dağılımı</div>
                    <template v-if="orderStatuses.length">
                        <div v-for="(st, i) in orderStatuses" :key="i" class="adm-break-row">
                            <span style="width:120px;color:var(--muted)">{{ st.label }}</span>
                            <div class="adm-break-bar"><div class="adm-break-fill" :style="{ width: (totalOrders ? Math.round(st.count/totalOrders*100) : 0) + '%', background: st.color }"></div></div>
                            <span style="width:34px;text-align:right;font-weight:700;color:var(--text)">{{ st.count }}</span>
                        </div>
                    </template>
                    <div v-else style="color:var(--muted);font-size:13px">Henüz sipariş yok.</div>
                </div>
            </div>

            <div class="adm-card">
                <div class="adm-card-h"><div class="adm-card-t"><i class="bi bi-lightning-charge" style="color:#fbbf24"></i> Hızlı İşlemler</div></div>
                <Link :href="links.users" class="adm-qa"><i class="bi bi-people lead" style="color:#3b82f6"></i><div style="flex:1"><div class="adm-qa-t">Kullanıcılar</div><div class="adm-qa-s">Üye yönetimi &amp; roller</div></div><i class="bi bi-chevron-right" style="color:var(--muted)"></i></Link>
                <Link :href="links.auctions" class="adm-qa"><i class="bi bi-hammer lead" style="color:#10b981"></i><div style="flex:1"><div class="adm-qa-t">Müzayedeler</div><div class="adm-qa-s">İlan &amp; teklif yönetimi</div></div><i class="bi bi-chevron-right" style="color:var(--muted)"></i></Link>
                <Link :href="links.disputes" class="adm-qa" data-testid="qa-disputes"><i class="bi bi-exclamation-octagon lead" style="color:#ef4444"></i><div style="flex:1"><div class="adm-qa-t">Anlaşmazlıklar</div><div class="adm-qa-s">{{ stats.disputes }} bekleyen çözüm</div></div><i class="bi bi-chevron-right" style="color:var(--muted)"></i></Link>
                <Link :href="links.orders" class="adm-qa"><i class="bi bi-box-seam lead" style="color:#8b5cf6"></i><div style="flex:1"><div class="adm-qa-t">Siparişler</div><div class="adm-qa-s">Tüm sipariş takibi</div></div><i class="bi bi-chevron-right" style="color:var(--muted)"></i></Link>
                <Link :href="links.categories" class="adm-qa"><i class="bi bi-diagram-3 lead" style="color:#06b6d4"></i><div style="flex:1"><div class="adm-qa-t">Kategoriler</div><div class="adm-qa-s">İç içe kategori yönetimi</div></div><i class="bi bi-chevron-right" style="color:var(--muted)"></i></Link>
            </div>
        </div>

        <div class="adm-2col-b">
            <div class="adm-card" data-testid="admin-recent-orders">
                <div class="adm-card-h"><div class="adm-card-t"><i class="bi bi-clock-history" style="color:var(--primary)"></i> Son Siparişler</div><Link :href="links.orders" style="font-size:12px;color:var(--primary);text-decoration:none">Tümü →</Link></div>
                <template v-if="recentOrders.length">
                    <div v-for="(o, i) in recentOrders" :key="i" class="adm-list-row">
                        <img class="adm-ava" :src="o.cover" alt="">
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;color:var(--text);font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ o.title }}</div>
                            <div style="font-size:11.5px;color:var(--muted)">{{ o.buyer }} · {{ money(o.amount) }}</div>
                        </div>
                        <span class="adm-badge" :style="{ background: o.status_color }">{{ o.status_label }}</span>
                    </div>
                </template>
                <div v-else style="color:var(--muted);font-size:13px;padding:16px 0;text-align:center">Henüz sipariş yok.</div>
            </div>

            <div>
                <div class="adm-card" style="margin-bottom:16px" data-testid="admin-top-sellers">
                    <div class="adm-card-h"><div class="adm-card-t"><i class="bi bi-trophy" style="color:#fbbf24"></i> En İyi Satıcılar</div></div>
                    <template v-if="topSellers.length">
                        <div v-for="(s, i) in topSellers" :key="i" class="adm-list-row">
                            <span style="width:18px;text-align:center;font-weight:800;color:var(--muted)">{{ i + 1 }}</span>
                            <img class="adm-ava" style="border-radius:50%" :src="s.avatar" alt="">
                            <div style="flex:1;min-width:0"><div style="font-weight:600;color:var(--text);font-size:13.5px">{{ s.name }}</div><div style="font-size:11.5px;color:var(--muted)">{{ s.sales }} satış</div></div>
                            <span style="font-weight:700;color:#10b981;font-size:13px">{{ money(s.total) }}</span>
                        </div>
                    </template>
                    <div v-else style="color:var(--muted);font-size:13px;padding:12px 0;text-align:center">Henüz tamamlanan satış yok.</div>
                </div>

                <div class="adm-card" data-testid="admin-activities">
                    <div class="adm-card-h"><div class="adm-card-t"><i class="bi bi-activity" style="color:#8b5cf6"></i> Son Aktiviteler</div></div>
                    <template v-if="activities.length">
                        <div v-for="(a, i) in activities" :key="i" class="adm-list-row">
                            <div class="adm-ava-c" :style="{ background: a.color }"><i class="bi" :class="a.icon"></i></div>
                            <div style="flex:1;min-width:0"><div style="font-size:13px;color:var(--text);line-height:1.4">{{ a.title }}</div><div style="font-size:11px;color:var(--muted);margin-top:2px">{{ a.time_human }}</div></div>
                        </div>
                    </template>
                    <div v-else style="color:var(--muted);font-size:13px;padding:12px 0;text-align:center">Aktivite bulunamadı.</div>
                </div>
            </div>
        </div>

    </div>
</template>
