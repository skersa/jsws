<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ counts: Object, filters: Object, auctions: Object });

const page = usePage();
const flash = computed(() => page.props.flash || {});

const search = ref(props.filters.search || '');

const statCards = computed(() => [
    { lbl: 'Toplam',     num: props.counts.all,      icon: 'bi-box-seam',  color: '#155eef', bg: 'rgba(21,94,239,.1)',   filter: '' },
    { lbl: 'Bekleyen',   num: props.counts.draft,    icon: 'bi-hourglass', color: '#f59e0b', bg: 'rgba(245,158,11,.1)',  filter: 'draft' },
    { lbl: 'Aktif',      num: props.counts.active,   icon: 'bi-broadcast', color: '#10b981', bg: 'rgba(16,185,129,.1)',  filter: 'active' },
    { lbl: 'Reddedilen', num: props.counts.rejected, icon: 'bi-x-circle',  color: '#ef4444', bg: 'rgba(239,68,68,.1)',   filter: 'rejected' },
    { lbl: 'Biten',      num: props.counts.ended,    icon: 'bi-flag',      color: '#6b7280', bg: 'rgba(107,114,128,.1)', filter: 'ended' },
]);

function cardUrl(filter) {
    return route('seller.auctions.index', filter ? { status: filter } : {});
}

function submitSearch() {
    const params = {};
    if (props.filters.status) params.status = props.filters.status;
    if (search.value) params.search = search.value;
    router.get(route('seller.auctions.index'), params, { preserveState: true, preserveScroll: true });
}

const hasFilters = computed(() => !!(props.filters.search || props.filters.status));

function deleteAuction(auction) {
    const doDelete = () => router.delete(auction.destroy_url, { preserveScroll: true });
    if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({
            title: 'İlan silinsin mi?',
            html: `<strong>${auction.title}</strong> kalıcı olarak kaldırılacak.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil',
            cancelButtonText: 'Vazgeç',
            reverseButtons: true,
            confirmButtonColor: '#ef4444',
            heightAuto: false,
        }).then((r) => { if (r.isConfirmed) doDelete(); });
    } else if (confirm(auction.title + ' kalıcı olarak kaldırılacak. Emin misiniz?')) {
        doDelete();
    }
}

function pageItems() {
    // links[0]=prev, links[last]=next, middle=numbers
    return props.auctions.links.slice(1, -1);
}
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
                            <li class="breadcrumb-item">
                                <Link :href="route('seller.dashboard')" class="pf-breadcrumb-link">Admin</Link>
                            </li>
                            <li class="breadcrumb-item active pf-breadcrumb-active">İlanlar</li>
                        </ol>
                    </nav>
                </div>
                <Link :href="route('seller.auctions.create')"
                      class="pf-btn-save d-inline-flex align-items-center gap-1"
                      data-testid="seller-create-auction-btn">
                    <i class="bi bi-plus-lg"></i> Yeni İlan
                </Link>
            </div>

            <div class="row g-3 mt-2">
                <div v-for="card in statCards" :key="card.lbl" class="col-6 col-md">
                    <Link :href="cardUrl(card.filter)"
                          class="pf-stat-card text-decoration-none d-flex"
                          :class="{ 'ring-active': filters.status === card.filter && (card.filter !== '' || !filters.status) }"
                          :style="filters.status === card.filter && card.filter !== '' ? 'border:1.5px solid ' + card.color + ';' : ''">
                        <div class="pf-stat-icon-wrapper" :style="{ background: card.bg }">
                            <i class="bi" :class="card.icon" :style="{ color: card.color }"></i>
                        </div>
                        <div>
                            <div class="pf-stat-number">{{ new Intl.NumberFormat('tr-TR').format(card.num) }}</div>
                            <div class="pf-stat-label">{{ card.lbl }}</div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <div v-if="flash.success" class="pf-alert-success mb-4">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ flash.success }}</span>
        </div>

        <div class="pf-main-card">
            <div v-if="!auctions.data.length && !hasFilters" class="pf-empty pf-empty-container text-center py-5">
                <div class="pf-empty-icon"><i class="bi bi-box-seam"></i></div>
                <div class="pf-empty-title">Gösterilecek ilan yok</div>
                <div class="pf-empty-sub">İlk ilanını oluşturmak için aşağıdaki butonu kullan.</div>
                <Link :href="route('seller.auctions.create')" class="pf-btn-save d-inline-flex align-items-center gap-1 mt-3" data-testid="seller-empty-create-btn">
                    <i class="bi bi-plus-lg"></i> Yeni İlan Oluştur
                </Link>
            </div>

            <template v-else>
                <div class="p-3">
                    <form @submit.prevent="submitSearch" class="d-flex gap-2 flex-wrap align-items-center">
                        <input type="text" v-model="search" class="pf-input" style="max-width:280px;" placeholder="İlan başlığı ara...">
                        <button class="pf-btn-save" type="submit">
                            <i class="bi bi-search me-1"></i> Ara
                        </button>
                        <Link v-if="hasFilters" :href="route('seller.auctions.index')" class="pf-btn-reset">
                            <i class="bi bi-x me-1"></i> Temizle
                        </Link>
                    </form>
                </div>

                <div v-if="!auctions.data.length" class="pf-empty pf-empty-container text-center py-5">
                    <div class="pf-empty-icon"><i class="bi bi-box-seam"></i></div>
                    <div class="pf-empty-title">Gösterilecek ilan yok</div>
                    <div class="pf-empty-sub">Arama kriterlerini değiştirmeyi dene.</div>
                </div>

                <div v-else class="table-responsive">
                    <table class="pf-table">
                        <thead>
                            <tr>
                                <th>İlan</th>
                                <th>Satıcı</th>
                                <th class="text-center">Başlangıç Fiyatı</th>
                                <th class="text-center">Durum</th>
                                <th class="text-center">Tarih</th>
                                <th class="text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in auctions.data" :key="a.id" class="pf-table-row">
                                <td>
                                    <div class="pf-cat-info">
                                        <img :src="a.cover_url" :alt="a.title" class="pf-cat-img" style="border-radius:14px;object-fit:cover;">
                                        <div>
                                            <div class="pf-cat-name">{{ a.title }}</div>
                                            <div class="pf-cat-slug">
                                                {{ a.category_name }}<template v-if="a.location"> · {{ a.location }}</template>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="pf-cat-name">{{ a.seller_name }}</div>
                                    <div class="pf-cat-slug" style="text-transform:none;">{{ a.seller_email }}</div>
                                </td>
                                <td class="text-center fw-semibold">{{ a.starting_price }}</td>
                                <td class="text-center">
                                    <span class="pf-badge" :class="a.status_class">{{ a.status_label }}</span>
                                </td>
                                <td class="text-center pf-text-muted-sm">{{ a.date }}</td>
                                <td>
                                    <div class="pf-actions-wrapper justify-content-end">
                                        <Link :href="a.show_url" class="pf-btn-icon pf-action-btn" title="İncele"><i class="bi bi-eye"></i></Link>
                                        <Link :href="a.edit_url" class="pf-btn-save pf-action-btn pf-action-edit" title="Düzenle"><i class="bi bi-pencil"></i></Link>
                                        <button type="button" class="pf-action-btn-delete" :data-title="a.title" title="Sil" @click="deleteAuction(a)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="auctions.has_pages" class="pf-pagination-wrapper">
                    <span class="pf-pagination-info">
                        <strong class="text-dark-custom">{{ auctions.from }}–{{ auctions.to }}</strong>
                        / {{ auctions.total }} ilan
                    </span>
                    <div class="d-flex gap-1">
                        <Link v-if="prevLink.url" :href="prevLink.url" class="pf-btn-icon pf-pagination-nav-btn">
                            <i class="bi bi-chevron-left"></i>
                        </Link>
                        <Link v-for="(l, i) in pageItems()" :key="i" :href="l.url || '#'"
                              class="pf-pagination-item" :class="{ active: l.active }" v-html="l.label" />
                        <Link v-if="nextLink.url" :href="nextLink.url" class="pf-btn-icon pf-pagination-nav-btn">
                            <i class="bi bi-chevron-right"></i>
                        </Link>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
