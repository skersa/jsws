<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    sellerName: String,
    stats: Object,
    walletBalance: String,
    walletPct: Number,
    chartData: Array,
    chartLabels: Array,
    liveAuctions: Array,
    broadcastableAuctions: Array,
    broadcastableCount: Number,
    topBidAuctions: Array,
    recentActivities: Array,
    latestAuctions: Array,
    links: Object,
});

const salesChart = ref(null);
let chartInstance = null;

function fmt(n) { return new Intl.NumberFormat('tr-TR').format(n || 0); }

const maxBid = () => (props.topBidAuctions.length ? props.topBidAuctions[0].bids : 0);
function bidPct(bids) {
    const m = maxBid();
    return m > 0 ? Math.round((bids / m) * 100) : 0;
}

function loadChartJs() {
    return new Promise((resolve) => {
        if (window.Chart) return resolve();
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        s.onload = () => resolve();
        s.onerror = () => resolve();
        document.head.appendChild(s);
    });
}

async function renderChart() {
    await loadChartJs();
    const ctx = salesChart.value;
    if (!ctx || !window.Chart) return;
    if (chartInstance) { try { chartInstance.destroy(); } catch (e) {} }

    const data = Array.isArray(props.chartData) ? props.chartData : [];
    const labels = props.chartLabels?.length ? props.chartLabels : data.map((_, i) => i + 1 + '.');

    chartInstance = new window.Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Satış (₺)',
                data,
                backgroundColor: 'rgba(21,94,239,.2)',
                hoverBackgroundColor: 'rgba(21,94,239,.65)',
                borderRadius: 5,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { padding: 10, callbacks: { label: (c) => ' ' + c.parsed.y.toLocaleString('tr-TR') + ' ₺' } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8', maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } },
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,.05)' }, ticks: { font: { size: 10 }, color: '#94a3b8', callback: (v) => (v >= 1000 ? (v / 1000).toFixed(1) + 'K' : v) } },
            },
        },
    });
}

onMounted(renderChart);
onBeforeUnmount(() => { if (chartInstance) { try { chartInstance.destroy(); } catch (e) {} } });
</script>

<template>
    <Head title="Satıcı Paneli" />
    <div class="pf-root container-fluid px-2 px-md-4 py-4">

        <div class="pf-toolbar mb-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1 class="pf-toolbar-title mb-1">Satıcı Paneli</h1>
                    <div class="pf-text-muted-sm">
                        Merhaba <strong style="color:var(--text)">{{ sellerName }}</strong>, performansını ve ilanlarını tek yerden yönet
                    </div>
                </div>
                <span class="pf-badge pf-badge-success d-inline-flex align-items-center gap-1" style="font-size:var(--fs-xs); padding:6px 14px; border-radius:20px;">
                    <span class="pf-pulse-dot"></span> Canlı
                </span>
            </div>
        </div>

        <div v-if="liveAuctions.length" class="admin-card seller-live-card seller-live-card--on mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="seller-live-icon seller-live-icon--pulse"><i class="bi bi-broadcast-pin"></i></div>
                    <div>
                        <div class="fw-bold" style="font-size:var(--fs-lg); color:var(--text)">Canlı Yayındasın · {{ liveAuctions.length }} ilan</div>
                        <div class="pf-text-muted-sm">Yayın paneline dön veya yeni yayın başlat</div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a v-for="(la, i) in liveAuctions.slice(0, 3)" :key="i" :href="la.broadcast_url" class="pf-btn-save d-flex align-items-center gap-2" style="padding:10px 14px;">
                        <span class="pf-pulse-dot" style="background:#fff"></span>{{ la.title }}
                    </a>
                </div>
            </div>
        </div>
        <div v-else-if="broadcastableAuctions.length" class="admin-card seller-live-card mb-3" id="canliya-basla">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="seller-live-icon"><i class="bi bi-broadcast"></i></div>
                    <div>
                        <div class="fw-bold" style="font-size:var(--fs-lg); color:var(--text)">Canlı Yayına Başla</div>
                        <div class="pf-text-muted-sm">{{ broadcastableCount }} aktif ilanın kamera açmayı bekliyor</div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <a v-if="broadcastableCount === 1" :href="broadcastableAuctions[0].broadcast_url" class="pf-btn-save d-flex align-items-center gap-2" style="padding:10px 18px; font-weight:600;" data-testid="seller-quick-broadcast-btn">
                        <i class="bi bi-camera-video"></i> "{{ broadcastableAuctions[0].title }}" için yayın aç
                    </a>
                    <div v-else class="seller-live-list d-flex gap-2 flex-wrap">
                        <a v-for="(ba, i) in broadcastableAuctions.slice(0, 4)" :key="i" :href="ba.broadcast_url" class="pf-btn-secondary d-flex align-items-center gap-2" style="padding:8px 14px; text-decoration:none; font-size:var(--fs-sm);">
                            <i class="bi bi-camera-video" style="color:var(--primary)"></i>{{ ba.title_short }}
                        </a>
                        <Link v-if="broadcastableCount > 4" :href="links.auctions_index" class="pf-btn-secondary d-flex align-items-center" style="padding:8px 14px; text-decoration:none; font-size:var(--fs-sm);">+{{ broadcastableCount - 4 }} daha</Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
                <div class="pf-stat-card" style="position:relative;overflow:hidden;">
                    <div class="pf-stat-icon-wrapper" style="background:rgba(21,94,239,.12)"><i class="bi bi-box-seam" style="color:var(--primary); font-size:var(--fs-md)"></i></div>
                    <div>
                        <div class="pf-stat-number">{{ stats.auctions ?? 0 }}</div>
                        <div class="pf-stat-label">Toplam İlan</div>
                        <div class="pf-text-muted-sm" style="color:#10b981;margin-top:3px">↑ {{ stats.auctions_this_month ?? 0 }} bu ay</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="pf-stat-card">
                    <div class="pf-stat-icon-wrapper" style="background:rgba(16,185,129,.12)"><i class="bi bi-broadcast" style="color:#10b981; font-size:var(--fs-md)"></i></div>
                    <div>
                        <div class="pf-stat-number">{{ stats.active ?? 0 }}</div>
                        <div class="pf-stat-label">Aktif İlan</div>
                        <div class="pf-text-muted-sm" style="color:#10b981;margin-top:3px">↑ {{ stats.active_this_week ?? 0 }} bu hafta</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="pf-stat-card">
                    <div class="pf-stat-icon-wrapper" style="background:rgba(251,191,36,.12)"><i class="bi bi-hand-index-thumb" style="color:#fbbf24; font-size:var(--fs-md)"></i></div>
                    <div>
                        <div class="pf-stat-number">{{ stats.bids ?? 0 }}</div>
                        <div class="pf-stat-label">Toplam Teklif</div>
                        <div class="pf-text-muted-sm" style="color:#10b981;margin-top:3px">↑ {{ stats.bids_today ?? 0 }} bugün</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="pf-stat-card">
                    <div class="pf-stat-icon-wrapper" style="background:rgba(6,182,212,.12)"><i class="bi bi-cash-coin" style="color:#06b6d4; font-size:var(--fs-md)"></i></div>
                    <div>
                        <div class="pf-stat-number">{{ stats.sales ?? 0 }}</div>
                        <div class="pf-stat-label">Satış</div>
                        <div class="pf-text-muted-sm" style="color:#06b6d4;margin-top:3px">{{ stats.sales_this_month ?? 0 }} bu ay</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-4">
                <div class="pf-stat-card flex-column text-center gap-1" style="padding:14px 8px;">
                    <div class="pf-stat-number" style="font-size:var(--fs-xl); color:#10b981">{{ stats.completion_rate ?? 0 }}%</div>
                    <div class="pf-stat-label">Tamamlanma</div>
                    <div class="pf-text-muted-sm">{{ stats.sales ?? 0 }} satış</div>
                </div>
            </div>
            <div class="col-4">
                <div class="pf-stat-card flex-column text-center gap-1" style="padding:14px 8px;">
                    <div class="pf-stat-number" style="font-size:var(--fs-xl)">{{ stats.seller_rating ?? '0.0' }} ★</div>
                    <div class="pf-stat-label">Satıcı Puanı</div>
                    <div class="pf-text-muted-sm">{{ stats.review_count ?? 0 }} değerlendirme</div>
                </div>
            </div>
            <div class="col-4">
                <div class="pf-stat-card flex-column text-center gap-1" style="padding:14px 8px;">
                    <div class="pf-stat-number" style="font-size:var(--fs-xl); color:var(--primary)">₺{{ fmt(stats.avg_price) }}</div>
                    <div class="pf-stat-label">Ort. Fiyat</div>
                    <div class="pf-text-muted-sm">Aktif ilanlar</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-8">
                <div class="admin-card h-100">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-graph-up-arrow" style="color:var(--primary)"></i> Satış Performansı</div>
                        <span class="pf-text-muted-sm">Son 30 gün</span>
                    </div>
                    <div style="padding:16px 20px;">
                        <div style="position:relative; width:100%; height:200px;">
                            <canvas ref="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="admin-card h-100 d-flex flex-column">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-wallet2" style="color:var(--primary)"></i> Cüzdan</div>
                    </div>
                    <div style="padding:18px 20px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="font-size:var(--fs-2xl); font-weight:800; color:var(--primary); line-height:1; margin-bottom:4px;">{{ walletBalance }}</div>
                            <div class="pf-text-muted-sm mb-3">Kullanılabilir bakiye</div>
                            <div style="height:5px; border-radius:10px; background:var(--border); margin-bottom:18px; overflow:hidden;">
                                <div :style="{ height:'100%', borderRadius:'10px', background:'var(--primary)', width: walletPct + '%' }"></div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="s-info-item text-center">
                                        <div class="s-info-lbl" style="font-size:var(--fs-xs)">Bu ay kazanılan</div>
                                        <div class="s-info-val" style="font-size:var(--fs-sm); color:#10b981">₺{{ fmt(stats.earned_this_month) }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="s-info-item text-center">
                                        <div class="s-info-lbl" style="font-size:var(--fs-xs)">Bekleyen</div>
                                        <div class="s-info-val" style="font-size:var(--fs-sm); color:#fbbf24">₺{{ fmt(stats.pending_balance) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-7">
                                <Link :href="links.withdraw" class="pf-btn-save w-100 d-flex align-items-center justify-content-center gap-1" style="padding:10px 0;"><i class="bi bi-arrow-down-circle"></i> Para Çek</Link>
                            </div>
                            <div class="col-5">
                                <Link :href="links.balance_index" class="pf-btn-secondary w-100 d-flex align-items-center justify-content-center gap-1" style="padding:10px 0; text-decoration:none; font-size:var(--fs-sm);"><i class="bi bi-clock-history"></i> Geçmiş</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-6">
                <div class="admin-card h-100">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-trophy" style="color:#fbbf24"></i> En Çok Teklif Alan İlanlar</div>
                        <Link :href="links.auctions_index" class="pf-link-primary" style="font-size:var(--fs-xs); text-decoration:none;">Tümü →</Link>
                    </div>
                    <div style="padding:6px 20px 16px;">
                        <template v-if="topBidAuctions.length">
                            <div v-for="(item, i) in topBidAuctions" :key="i" class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid var(--border)">
                                <span class="pf-text-muted-sm" style="width:18px; text-align:center; font-weight:700;">{{ i + 1 }}</span>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:var(--fs-sm); font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ item.title }}</div>
                                    <div style="height:3px; border-radius:4px; background:var(--border); margin-top:5px; overflow:hidden;">
                                        <div :style="{ height:'100%', borderRadius:'4px', background:'var(--primary)', width: bidPct(item.bids) + '%' }"></div>
                                    </div>
                                </div>
                                <span class="pf-badge pf-badge-success">{{ item.bids }}</span>
                            </div>
                        </template>
                        <div v-else class="pf-empty" style="padding:26px 0;">
                            <div class="pf-empty-icon"><i class="bi bi-trophy"></i></div>
                            <div class="pf-empty-title">Henüz teklif alan ilan yok</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="admin-card h-100">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-activity" style="color:var(--primary)"></i> Son Aktivite</div>
                    </div>
                    <div style="padding:4px 20px 16px;">
                        <template v-if="recentActivities.length">
                            <div v-for="(act, i) in recentActivities" :key="i" class="d-flex align-items-start gap-3 py-2" style="border-bottom:1px solid var(--border)">
                                <div :style="{ width:'8px', height:'8px', borderRadius:'50%', background: act.color, flexShrink:0, marginTop:'5px' }"></div>
                                <div style="flex:1;">
                                    <div style="font-size:var(--fs-sm); color:var(--text); line-height:1.5;" v-html="act.text"></div>
                                    <div class="pf-text-muted-sm" style="margin-top:2px;">{{ act.time }}</div>
                                </div>
                            </div>
                        </template>
                        <div v-else class="pf-empty" style="padding:26px 0;">
                            <div class="pf-empty-icon"><i class="bi bi-activity"></i></div>
                            <div class="pf-empty-title">Henüz aktivite yok</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-8">
                <div class="admin-card h-100">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-box-seam" style="color:var(--primary)"></i> Son İlanlar</div>
                        <Link :href="links.auctions_index" class="pf-link-primary" style="font-size:var(--fs-xs); text-decoration:none;">Tümünü Gör →</Link>
                    </div>

                    <div v-if="!latestAuctions.length" class="pf-empty">
                        <div class="pf-empty-icon"><i class="bi bi-box-seam"></i></div>
                        <div class="pf-empty-title">Henüz ilan yok</div>
                        <div class="pf-empty-sub">İlk ilanını oluşturmak için "Yeni İlan Oluştur" butonunu kullan.</div>
                    </div>
                    <template v-else>
                        <div class="d-none d-md-block">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>İlan</th>
                                        <th class="text-end">Fiyat</th>
                                        <th class="text-center">Durum</th>
                                        <th class="text-center">Teklif</th>
                                        <th class="text-center">Süre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(a, i) in latestAuctions" :key="i">
                                        <td>
                                            <div class="pf-cat-info">
                                                <img :src="a.cover_url" class="pf-cat-img" :alt="a.title">
                                                <div>
                                                    <div class="pf-cat-name">{{ a.title }}</div>
                                                    <div class="pf-cat-slug">{{ a.created_ago }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end" style="font-weight:700; color:var(--text);">{{ a.display_price }}</td>
                                        <td class="text-center"><span class="pf-badge" :class="a.status_class">{{ a.status_label }}</span></td>
                                        <td class="text-center" style="font-weight:600; color:var(--text);">{{ a.bid_count }}</td>
                                        <td class="text-center pf-text-muted-sm">{{ a.ends_ago }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column gap-2 d-md-none p-3">
                            <div v-for="(a, i) in latestAuctions" :key="i" class="d-flex align-items-center gap-3 p-2" style="border:1px solid var(--border); border-radius:12px; background:var(--bg-soft);">
                                <img :src="a.cover_url" style="width:44px;height:44px;border-radius:10px;object-fit:cover;flex-shrink:0;border:1px solid var(--border);" :alt="a.title">
                                <div style="flex:1;min-width:0;">
                                    <div class="pf-cat-name">{{ a.title_short }}</div>
                                    <div style="font-size:var(--fs-sm); font-weight:700; color:var(--primary);">{{ a.display_price }}</div>
                                </div>
                                <span class="pf-badge" :class="a.status_class" style="flex-shrink:0;">{{ a.status_label }}</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="admin-card h-100">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-lightning-charge" style="color:#fbbf24"></i> Hızlı İşlemler</div>
                    </div>
                    <div style="padding:16px 20px; display:flex; flex-direction:column; gap:10px;">
                        <Link :href="links.auctions_create" class="pf-btn-save w-100 d-flex align-items-center justify-content-center gap-2" style="padding:12px;"><i class="bi bi-plus-lg"></i> Yeni İlan Oluştur</Link>
                        <div class="s-action-grid" style="grid-template-columns:1fr 1fr;">
                            <Link :href="links.auctions_index" class="s-action-btn text-decoration-none">
                                <i class="bi bi-list-ul" style="color:var(--primary); font-size:var(--fs-md)"></i>
                                <div class="s-info-lbl mt-1" style="font-size:10px;">İlanlar</div>
                            </Link>
                            <Link :href="links.profile_edit" class="s-action-btn text-decoration-none">
                                <i class="bi bi-person" style="color:var(--primary); font-size:var(--fs-md)"></i>
                                <div class="s-info-lbl mt-1" style="font-size:10px;">Profil</div>
                            </Link>
                            <Link :href="links.balance_index" class="s-action-btn text-decoration-none">
                                <i class="bi bi-graph-up" style="color:#10b981; font-size:var(--fs-md)"></i>
                                <div class="s-info-lbl mt-1" style="font-size:10px;">Cüzdan</div>
                            </Link>
                            <Link href="/messages" class="s-action-btn text-decoration-none">
                                <i class="bi bi-chat-dots" style="color:#06b6d4; font-size:var(--fs-md)"></i>
                                <div class="s-info-lbl mt-1" style="font-size:10px;">Mesajlar</div>
                            </Link>
                            <Link :href="links.profile_edit" class="s-action-btn text-decoration-none">
                                <i class="bi bi-gear" style="color:var(--muted); font-size:var(--fs-md)"></i>
                                <div class="s-info-lbl mt-1" style="font-size:10px;">Ayarlar</div>
                            </Link>
                            <Link href="/support" class="s-action-btn text-decoration-none">
                                <i class="bi bi-question-circle" style="color:var(--muted); font-size:var(--fs-md)"></i>
                                <div class="s-info-lbl mt-1" style="font-size:10px;">Yardım</div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
