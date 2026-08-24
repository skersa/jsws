<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({ stats: Object, filters: Object, categories: Object, create_url: String });

const page = usePage();
const flash = computed(() => page.props.flash || {});

const q = ref(props.filters.q || '');
const status = ref(props.filters.status || '');
const type = ref(props.filters.type || '');

watch(() => props.filters, (f) => {
    q.value = f.q || '';
    status.value = f.status || '';
    type.value = f.type || '';
});

const statCards = computed(() => [
    { lbl: 'Toplam',   num: props.stats.total,   icon: 'bi-grid-3x3-gap-fill', color: 'var(--primary)', bg: 'rgba(21,94,239,.1)',   col: 12 },
    { lbl: 'Aktif',    num: props.stats.active,  icon: 'bi-check-circle',      color: '#10b981',        bg: 'rgba(16,185,129,.1)', col: 3 },
    { lbl: 'Pasif',    num: props.stats.passive, icon: 'bi-pause-circle',      color: '#fbbf24',        bg: 'rgba(251,191,36,.1)', col: 3 },
    { lbl: 'Ana Kat.', num: props.stats.roots,   icon: 'bi-folder2-open',      color: '#06b6d4',        bg: 'rgba(6,182,212,.1)',  col: 3 },
    { lbl: 'Alt Kat.', num: props.stats.subs,    icon: 'bi-folder2',           color: '#f87171',        bg: 'rgba(248,113,113,.1)', col: 3 },
]);

const hasFilters = computed(() => !!(props.filters.q || props.filters.status || props.filters.type));

function submitFilter() {
    const params = {};
    if (q.value) params.q = q.value;
    if (status.value) params.status = status.value;
    if (type.value) params.type = type.value;
    router.get(route('admin.categories.index'), params, { preserveState: true, preserveScroll: true });
}

function toggle(cat) {
    router.post(cat.toggle_url, {}, { preserveScroll: true });
}

function deleteCategory(cat) {
    const doDelete = () => {
        const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        const fd = new FormData();
        fd.append('_method', 'DELETE');
        fetch(cat.destroy_url, {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin',
        }).then((res) => {
            if (!res.ok) return res.json().then((e) => { throw new Error(e.message || 'Silme başarısız'); });
            return res.json().catch(() => ({}));
        }).then((data) => {
            if (window.ajaxToast) window.ajaxToast('success', (data && data.message) || 'Kategori silindi');
            router.reload({ only: ['categories', 'stats'], preserveScroll: true });
        }).catch((err) => {
            if (window.ajaxToast) window.ajaxToast('error', err.message); else alert(err.message);
        });
    };

    const childTxt = cat.children_count > 0 ? `<br><strong>${cat.children_count}</strong> alt kategori de silinecek.` : '';
    if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({
            title: 'Kategoriyi sil?',
            html: `<strong>${cat.name}</strong> silinecek.${childTxt}`,
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Evet, sil', cancelButtonText: 'Vazgeç',
            reverseButtons: true, confirmButtonColor: '#ef4444', heightAuto: false,
        }).then((r) => { if (r.isConfirmed) doDelete(); });
    } else if (confirm(cat.name + ' silinecek. Emin misiniz?')) {
        doDelete();
    }
}

function pageItems() { return props.categories.links.slice(1, -1); }
const prevLink = computed(() => props.categories.links[0]);
const nextLink = computed(() => props.categories.links[props.categories.links.length - 1]);
</script>

<template>
    <Head title="Kategori Yönetimi" />
    <div class="pf-root container-fluid px-4 py-4">

        <div class="pf-toolbar mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="pf-toolbar-title mb-1">Kategori Yönetimi</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                            <li class="breadcrumb-item"><Link :href="route('admin.dashboard')" class="pf-breadcrumb-link">Admin</Link></li>
                            <li class="breadcrumb-item active pf-breadcrumb-active">Kategoriler</li>
                        </ol>
                    </nav>
                </div>
                <Link :href="create_url" class="pf-btn-save pf-btn-with-icon" data-testid="admin-category-create-btn">
                    <i class="bi bi-plus-lg"></i> Yeni Kategori
                </Link>
            </div>

            <div class="row g-3 mt-2">
                <div v-for="card in statCards" :key="card.lbl" :class="`col-6 col-md-4 col-xl-${card.col}`">
                    <div class="pf-stat-card">
                        <div class="pf-stat-icon-wrapper" :style="{ background: card.bg }">
                            <i class="bi" :class="card.icon" :style="{ color: card.color }"></i>
                        </div>
                        <div>
                            <div class="pf-stat-number">{{ new Intl.NumberFormat('tr-TR').format(card.num) }}</div>
                            <div class="pf-stat-label">{{ card.lbl }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="flash.category_success" class="pf-alert-success mb-4">
            <i class="bi bi-check-circle-fill"></i> {{ flash.category_success }}
        </div>

        <div class="pf-main-card">

            <div class="pf-filter-wrapper">
                <form @submit.prevent="submitFilter" class="pf-filter-form">
                    <div class="pf-search-input-wrapper">
                        <i class="bi bi-search pf-search-icon"></i>
                        <input type="text" v-model="q" class="pf-input pf-input-search" placeholder="Kategori adı, slug..." data-testid="admin-category-search">
                    </div>
                    <select v-model="status" class="pf-input pf-select-status" data-testid="admin-category-status">
                        <option value="">Tüm Durum</option>
                        <option value="active">✓ Aktif</option>
                        <option value="passive">⏸ Pasif</option>
                    </select>
                    <select v-model="type" class="pf-input pf-select-type" data-testid="admin-category-type">
                        <option value="">Tüm Türler</option>
                        <option value="root">📁 Ana Kategori</option>
                        <option value="sub">📂 Alt Kategori</option>
                    </select>
                    <button type="submit" class="pf-btn-save pf-btn-filter" data-testid="admin-category-filter">
                        <i class="bi bi-funnel me-1"></i> Filtrele
                    </button>
                    <Link v-if="hasFilters" :href="route('admin.categories.index')" class="pf-btn-reset pf-btn-clear">
                        <i class="bi bi-x-lg"></i>
                    </Link>
                </form>
            </div>

            <div class="table-responsive">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th class="text-start text-nowrap">Kategori</th>
                            <th class="text-start text-nowrap">Üst Kategori</th>
                            <th class="text-start text-nowrap">İlan</th>
                            <th class="text-start text-nowrap">Alt Kat.</th>
                            <th class="text-start text-nowrap">Sıra</th>
                            <th class="text-start text-nowrap">Durum</th>
                            <th class="text-end text-nowrap">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="categories.data.length">
                            <tr v-for="cat in categories.data" :key="cat.id" class="pf-table-row">
                                <td>
                                    <div class="pf-cat-info">
                                        <img :src="cat.image_url" :alt="cat.name" class="pf-cat-img">
                                        <div>
                                            <div class="pf-cat-name">{{ cat.name }}</div>
                                            <div class="pf-cat-slug">/{{ cat.slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span v-if="cat.parent_name" class="pf-badge pf-badge-cyan">{{ cat.parent_name }}</span>
                                    <span v-else class="pf-text-muted-sm">—</span>
                                </td>
                                <td class="pf-table-count">{{ cat.auctions_count }}</td>
                                <td class="pf-table-count">{{ cat.children_count }}</td>
                                <td class="pf-table-order">{{ cat.sort_order }}</td>
                                <td>
                                    <span v-if="cat.is_active" class="pf-badge pf-badge-success"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                                    <span v-else class="pf-badge pf-badge-warning"><i class="bi bi-pause-circle-fill"></i> Pasif</span>
                                </td>
                                <td>
                                    <div class="pf-actions-wrapper">
                                        <Link :href="cat.show_url" class="pf-btn-icon pf-action-btn" title="Detay"><i class="bi bi-eye"></i></Link>
                                        <Link :href="cat.edit_url" class="pf-btn-save pf-action-btn pf-action-edit" title="Düzenle"><i class="bi bi-pencil"></i></Link>
                                        <button type="button" @click="toggle(cat)"
                                                :title="cat.is_active ? 'Pasife al' : 'Aktif et'"
                                                class="pf-action-btn-toggle" :class="cat.is_active ? 'status-active' : 'status-passive'"
                                                :data-testid="`admin-category-toggle-${cat.id}`">
                                            <i class="bi" :class="cat.is_active ? 'bi-pause' : 'bi-play'"></i>
                                        </button>
                                        <button type="button" class="delete-btn pf-action-btn-delete"
                                                :data-name="cat.name" :data-children="cat.children_count" title="Sil"
                                                @click="deleteCategory(cat)"
                                                :data-testid="`admin-category-delete-${cat.id}`">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="7">
                                <div class="pf-empty pf-empty-container text-center">
                                    <div class="pf-empty-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                                    <div class="pf-empty-title">Kategori bulunamadı</div>
                                    <div class="pf-empty-sub">Filtreni değiştir veya yeni kategori oluştur.</div>
                                    <Link :href="create_url" class="pf-btn-save mt-3 pf-btn-with-icon d-inline-flex"><i class="bi bi-plus-lg"></i> Yeni Kategori</Link>                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="categories.has_pages" class="pf-pagination-wrapper">
                <span class="pf-pagination-info">
                    <strong class="text-dark-custom">{{ categories.from }}–{{ categories.to }}</strong> / {{ categories.total }} kategori
                </span>
                <div class="d-flex gap-1">
                    <Link v-if="prevLink.url" :href="prevLink.url" class="pf-btn-icon pf-pagination-nav-btn"><i class="bi bi-chevron-left"></i></Link>
                    <Link v-for="(l, i) in pageItems()" :key="i" :href="l.url || '#'" class="pf-pagination-item" :class="{ active: l.active }" v-html="l.label" />
                    <Link v-if="nextLink.url" :href="nextLink.url" class="pf-btn-icon pf-pagination-nav-btn"><i class="bi bi-chevron-right"></i></Link>
                </div>
            </div>

        </div>
    </div>
</template>
