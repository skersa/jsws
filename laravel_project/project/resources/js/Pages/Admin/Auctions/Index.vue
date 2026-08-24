<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({ counts: Object, filters: Object, auctions: Object });
const page = usePage();
const flash = computed(() => page.props.flash || {});
const money = (n) => new Intl.NumberFormat('tr-TR').format(Math.round(n || 0)) + ' ₺';

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
watch(() => props.filters, (f) => { search.value = f.search || ''; status.value = f.status || ''; });

const statCards = computed(() => [
    { lbl: 'Toplam', num: props.counts.all, icon: 'bi-box-seam', color: 'var(--primary)', bg: 'rgba(21,94,239,.1)', filter: null, col: 12 },
    { lbl: 'Bekleyen', num: props.counts.draft, icon: 'bi-hourglass', color: '#f59e0b', bg: 'rgba(245,158,11,.1)', filter: 'draft', col: 3 },
    { lbl: 'Aktif', num: props.counts.active, icon: 'bi-broadcast', color: '#10b981', bg: 'rgba(16,185,129,.1)', filter: 'active', col: 3 },
    { lbl: 'Reddedilen', num: props.counts.rejected, icon: 'bi-x-circle', color: '#ef4444', bg: 'rgba(239,68,68,.1)', filter: 'rejected', col: 3 },
    { lbl: 'Biten', num: props.counts.ended, icon: 'bi-flag', color: '#6b7280', bg: 'rgba(107,114,128,.1)', filter: 'ended', col: 3 },
]);
const hasFilters = computed(() => !!(props.filters.search || props.filters.status));

function goStat(f) { router.get(route('admin.auctions.index'), f ? { status: f } : {}, { preserveScroll: true }); }
function submitFilter() {
    const p = {};
    if (search.value) p.search = search.value;
    if (status.value) p.status = status.value;
    router.get(route('admin.auctions.index'), p, { preserveState: true, preserveScroll: true });
}
function approve(a) { router.post(a.approve_url, {}, { preserveScroll: true }); }
function reject(a) {
    if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({
            title: 'İlanı Reddet', input: 'textarea', inputPlaceholder: 'Gerekçe (isteğe bağlı, kullanıcıya iletilir)...',
            html: `<div style="font-size:13px;color:#94a3b8">"${a.raw_title}" reddedilecek.</div>`,
            showCancelButton: true, confirmButtonText: 'Reddet', cancelButtonText: 'Vazgeç', reverseButtons: true, confirmButtonColor: '#ef4444',
        }).then((r) => { if (r.isConfirmed) router.post(a.reject_url, { reason: r.value || '' }, { preserveScroll: true }); });
    } else {
        const reason = prompt(a.raw_title + ' reddedilecek. Gerekçe:'); if (reason !== null) router.post(a.reject_url, { reason }, { preserveScroll: true });
    }
}
function del(a) {
    const doDelete = () => {
        const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        const fd = new FormData(); fd.append('_method', 'DELETE');
        fetch(a.destroy_url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin' })
            .then((res) => { if (!res.ok) return res.json().then((e) => { throw new Error(e.message || 'Silme başarısız'); }); return res.json().catch(() => ({})); })
            .then((data) => { if (window.ajaxToast) window.ajaxToast('success', (data && data.message) || 'Silindi'); router.reload({ only: ['auctions', 'counts'], preserveScroll: true }); })
            .catch((e) => { if (window.ajaxToast) window.ajaxToast('error', e.message); else alert(e.message); });
    };
    if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({ title: 'İlanı sil?', html: `<strong>${a.raw_title}</strong> silinecek.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet, sil', cancelButtonText: 'Vazgeç', reverseButtons: true, confirmButtonColor: '#ef4444' }).then((r) => { if (r.isConfirmed) doDelete(); });
    } else if (confirm(a.raw_title + ' silinecek?')) doDelete();
}
const pageItems = () => props.auctions.links.slice(1, -1);
const prevLink = computed(() => props.auctions.links[0]);
const nextLink = computed(() => props.auctions.links[props.auctions.links.length - 1]);
</script>

<template>
    <Head title="İlan Yönetimi" />
    <div class="pf-root container-fluid px-4 py-4">
        <div class="pf-toolbar mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="pf-toolbar-title mb-1">İlan Yönetimi</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                            <li class="breadcrumb-item"><Link :href="route('admin.dashboard')" class="pf-breadcrumb-link">Admin</Link></li>
                            <li class="breadcrumb-item active pf-breadcrumb-active">İlanlar</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div v-for="card in statCards" :key="card.lbl" :class="`col-6 col-md-4 col-xl-${card.col}`">
                    <a href="#" @click.prevent="goStat(card.filter)" class="pf-stat-card text-decoration-none" :class="{ 'pf-stat-card-active': filters.status === card.filter && (card.filter || !filters.status) }" :style="(filters.status === card.filter && (card.filter || !filters.status)) ? { border: '1.5px solid ' + card.color } : {}" :data-testid="`auction-stat-${card.filter || 'all'}`">
                        <div class="pf-stat-icon-wrapper" :style="{ background: card.bg }"><i class="bi" :class="card.icon" :style="{ color: card.color }"></i></div>
                        <div>
                            <div class="pf-stat-number">{{ new Intl.NumberFormat('tr-TR').format(card.num) }}</div>
                            <div class="pf-stat-label">{{ card.lbl }}</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div v-if="flash.success" class="pf-alert-success mb-4"><i class="bi bi-check-circle-fill"></i> {{ flash.success }}</div>

        <div class="pf-main-card">
            <div class="pf-filter-wrapper">
                <form @submit.prevent="submitFilter" class="pf-filter-form">
                    <div class="pf-search-input-wrapper">
                        <i class="bi bi-search pf-search-icon"></i>
                        <input type="text" v-model="search" class="pf-input pf-input-search" placeholder="İlan başlığı ara..." data-testid="auction-search">
                    </div>
                    <select v-model="status" class="pf-input pf-select-status" data-testid="auction-status">
                        <option value="">Tüm Durum</option>
                        <option value="draft">⏳ Bekliyor</option>
                        <option value="active">✓ Aktif</option>
                        <option value="rejected">✕ Reddedildi</option>
                        <option value="ended">⚑ Bitti</option>
                        <option value="sold">✓ Satıldı</option>
                        <option value="cancelled">⏸ İptal</option>
                    </select>
                    <button type="submit" class="pf-btn-save pf-btn-filter" data-testid="auction-filter"><i class="bi bi-funnel me-1"></i> Filtrele</button>
                    <Link v-if="hasFilters" :href="route('admin.auctions.index')" class="pf-btn-reset pf-btn-clear"><i class="bi bi-x-lg"></i></Link>
                </form>
            </div>

            <div class="table-responsive">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th class="text-start text-nowrap">İlan</th>
                            <th class="text-start text-nowrap">Satıcı</th>
                            <th class="text-start text-nowrap">Başlangıç Fiyatı</th>
                            <th class="text-start text-nowrap">Durum</th>
                            <th class="text-start text-nowrap">Tarih</th>
                            <th class="text-end text-nowrap">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="auctions.data.length">
                            <tr v-for="a in auctions.data" :key="a.id" class="pf-table-row">
                                <td>
                                    <div class="pf-cat-info">
                                        <img :src="a.cover" :alt="a.title" class="pf-cat-img">
                                        <div>
                                            <div class="pf-cat-name">{{ a.title }}</div>
                                            <div class="pf-cat-slug">{{ a.category }}<template v-if="a.location"> · {{ a.location }}</template></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="pf-cat-name" style="font-size:13px;">{{ a.seller_name }}</div>
                                    <div class="pf-cat-slug">{{ a.seller_email }}</div>
                                </td>
                                <td class="pf-table-count text-nowrap">{{ money(a.starting_price) }}</td>
                                <td><span class="pf-badge" :class="a.status_class">{{ a.status_label }}</span></td>
                                <td class="pf-text-muted-sm text-nowrap">{{ a.created }}</td>
                                <td>
                                    <div class="pf-actions-wrapper">
                                        <Link :href="a.show_url" class="pf-btn-icon pf-action-btn" title="İncele"><i class="bi bi-eye"></i></Link>
                                        <Link :href="a.edit_url" class="pf-btn-save pf-action-btn pf-action-edit" title="Düzenle"><i class="bi bi-pencil"></i></Link>
                                        <template v-if="a.status === 'draft'">
                                            <button type="button" @click="approve(a)" class="pf-action-btn-toggle status-active" title="Onayla" :data-testid="`auction-approve-${a.id}`"><i class="bi bi-check-lg"></i></button>
                                            <button type="button" @click="reject(a)" class="pf-action-btn-toggle status-passive" title="Reddet" :data-testid="`auction-reject-${a.id}`"><i class="bi bi-x-lg"></i></button>
                                        </template>
                                        <button type="button" class="delete-btn pf-action-btn-delete" @click="del(a)" title="Sil" :data-testid="`auction-delete-${a.id}`"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="6">
                                <div class="pf-empty pf-empty-container text-center">
                                    <div class="pf-empty-icon"><i class="bi bi-inbox"></i></div>
                                    <div class="pf-empty-title">İlan bulunamadı</div>
                                    <div class="pf-empty-sub">Filtreni değiştir veya farklı bir arama dene.</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="auctions.has_pages" class="pf-pagination-wrapper">
                <span class="pf-pagination-info"><strong class="text-dark-custom">{{ auctions.from }}–{{ auctions.to }}</strong> / {{ auctions.total }} ilan</span>
                <div class="d-flex gap-1">
                    <Link v-if="prevLink.url" :href="prevLink.url" class="pf-btn-icon pf-pagination-nav-btn"><i class="bi bi-chevron-left"></i></Link>
                    <Link v-for="(l, i) in pageItems()" :key="i" :href="l.url || '#'" class="pf-pagination-item" :class="{ active: l.active }" v-html="l.label" />
                    <Link v-if="nextLink.url" :href="nextLink.url" class="pf-btn-icon pf-pagination-nav-btn"><i class="bi bi-chevron-right"></i></Link>
                </div>
            </div>
        </div>
    </div>
</template>
