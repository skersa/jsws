<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({ stats: Object, roles: Array, filters: Object, users: Object, create_url: String });
const page = usePage();
const flash = computed(() => page.props.flash || {});

const q = ref(props.filters.q || '');
const role = ref(props.filters.role || '');
const verified = ref(props.filters.verified || '');
watch(() => props.filters, (f) => { q.value = f.q || ''; role.value = f.role || ''; verified.value = f.verified || ''; });

const statCards = computed(() => [
    { lbl: 'Toplam Üye', num: props.stats.total, icon: 'bi-people', color: 'var(--primary)', bg: 'rgba(21,94,239,.1)', col: 12 },
    { lbl: 'Doğrulanmış', num: props.stats.verified, icon: 'bi-shield-check', color: '#10b981', bg: 'rgba(16,185,129,.1)', col: 3 },
    { lbl: 'Beklemede', num: props.stats.pending, icon: 'bi-clock', color: '#fbbf24', bg: 'rgba(251,191,36,.1)', col: 3 },
    { lbl: 'Satıcı', num: props.stats.sellers, icon: 'bi-shop', color: '#06b6d4', bg: 'rgba(6,182,212,.1)', col: 3 },
    { lbl: 'Alıcı', num: props.stats.buyers, icon: 'bi-person', color: 'var(--primary)', bg: 'rgba(21,94,239,.08)', col: 3 },
]);
const hasFilters = computed(() => !!(props.filters.q || props.filters.role || props.filters.verified));

function submitFilter() {
    const params = {};
    if (q.value) params.q = q.value;
    if (role.value) params.role = role.value;
    if (verified.value) params.verified = verified.value;
    router.get(route('admin.users.index'), params, { preserveState: true, preserveScroll: true });
}
function toggleVerify(u) {
    router.post(u.is_verified ? u.unverify_url : u.verify_url, {}, { preserveScroll: true });
}
function deleteUser(u) {
    const doDelete = () => {
        const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        const fd = new FormData(); fd.append('_method', 'DELETE');
        fetch(u.destroy_url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin' })
            .then((r) => { if (!r.ok) return r.json().then((e) => { throw new Error(e.message || 'Silme başarısız'); }); return r.json().catch(() => ({})); })
            .then((data) => { if (window.ajaxToast) window.ajaxToast('success', (data && data.message) || 'Silindi'); router.reload({ only: ['users', 'stats'], preserveScroll: true }); })
            .catch((e) => { if (window.ajaxToast) window.ajaxToast('error', e.message); else alert(e.message); });
    };
    if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({ title: 'Kullanıcıyı sil?', html: `<strong>${u.name}</strong> silinecek. Tüm verileri kaldırılır.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet, sil', cancelButtonText: 'Vazgeç', reverseButtons: true, confirmButtonColor: '#ef4444' }).then((r) => { if (r.isConfirmed) doDelete(); });
    } else if (confirm(u.name + ' silinecek. Emin misiniz?')) doDelete();
}
const pageItems = () => props.users.links.slice(1, -1);
const prevLink = computed(() => props.users.links[0]);
const nextLink = computed(() => props.users.links[props.users.links.length - 1]);
</script>

<template>
    <Head title="Kullanıcı Yönetimi" />
    <div class="pf-root container-fluid px-4 py-4">
        <div class="pf-toolbar mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="pf-toolbar-title mb-1">Kullanıcı Yönetimi</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                            <li class="breadcrumb-item"><Link :href="route('admin.dashboard')" class="pf-breadcrumb-link">Admin</Link></li>
                            <li class="breadcrumb-item active pf-breadcrumb-active">Kullanıcılar</li>
                        </ol>
                    </nav>
                </div>
                <Link :href="create_url" class="pf-btn-save pf-btn-with-icon" data-testid="admin-user-create-btn">
                    <i class="bi bi-person-plus"></i> Kullanıcı Ekle
                </Link>
            </div>
            <div class="row g-3 mt-2">
                <div v-for="card in statCards" :key="card.lbl" :class="`col-6 col-md-4 col-xl-${card.col}`">
                    <div class="pf-stat-card">
                        <div class="pf-stat-icon-wrapper" :style="{ background: card.bg }"><i class="bi" :class="card.icon" :style="{ color: card.color }"></i></div>
                        <div>
                            <div class="pf-stat-number">{{ new Intl.NumberFormat('tr-TR').format(card.num) }}</div>
                            <div class="pf-stat-label">{{ card.lbl }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="flash.success" class="pf-alert-success mb-4"><i class="bi bi-check-circle-fill"></i> {{ flash.success }}</div>

        <div class="pf-main-card">
            <div class="pf-filter-wrapper">
                <form @submit.prevent="submitFilter" class="pf-filter-form">
                    <div class="pf-search-input-wrapper">
                        <i class="bi bi-search pf-search-icon"></i>
                        <input type="text" v-model="q" class="pf-input pf-input-search" placeholder="İsim, e-posta..." data-testid="user-search">
                    </div>
                    <select v-model="role" class="pf-input pf-select-status" data-testid="user-role-filter">
                        <option value="">Tüm Roller</option>
                        <option v-for="r in roles" :key="r.name" :value="r.name">{{ r.label }}</option>
                    </select>
                    <select v-model="verified" class="pf-input pf-select-type" data-testid="user-verified-filter">
                        <option value="">Tüm Durum</option>
                        <option value="yes">✓ Doğrulanmış</option>
                        <option value="no">⏳ Beklemede</option>
                    </select>
                    <button type="submit" class="pf-btn-save pf-btn-filter" data-testid="user-filter"><i class="bi bi-funnel me-1"></i> Filtrele</button>
                    <Link v-if="hasFilters" :href="route('admin.users.index')" class="pf-btn-reset pf-btn-clear"><i class="bi bi-x-lg"></i></Link>
                </form>
            </div>

            <div class="table-responsive">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th class="text-start text-nowrap">Kullanıcı</th>
                            <th class="text-start text-nowrap">Rol</th>
                            <th class="text-start text-nowrap">İlan</th>
                            <th class="text-start text-nowrap">Teklif</th>
                            <th class="text-start text-nowrap">Durum</th>
                            <th class="text-start text-nowrap">Kayıt</th>
                            <th class="text-end text-nowrap">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="users.data.length">
                            <tr v-for="u in users.data" :key="u.id" class="pf-table-row">
                                <td>
                                    <div class="pf-cat-info">
                                        <img v-if="u.avatar_url" :src="u.avatar_url" :alt="u.name" class="pf-cat-img" style="border-radius:50%">
                                        <div v-else class="pf-cat-img d-flex align-items-center justify-content-center fw-bold text-uppercase" style="background:rgba(21,94,239,.15);color:var(--primary);border-radius:50%;font-size:14px">{{ u.initial }}</div>
                                        <div>
                                            <div class="pf-cat-name">{{ u.name }}</div>
                                            <div class="pf-cat-slug" style="text-transform:none">{{ u.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span v-for="(r, i) in u.roles" :key="i" class="pf-badge" :class="r.badge">{{ r.label }}</span></td>
                                <td class="pf-table-count">{{ u.auctions_count }}</td>
                                <td class="pf-table-count">{{ u.bids_count }}</td>
                                <td>
                                    <span v-if="u.is_verified" class="pf-badge pf-badge-success"><i class="bi bi-shield-check"></i> Doğrulandı</span>
                                    <span v-else class="pf-badge pf-badge-warning"><i class="bi bi-clock"></i> Beklemede</span>
                                </td>
                                <td class="pf-text-muted-sm">{{ u.created_human }}</td>
                                <td>
                                    <div class="pf-actions-wrapper">
                                        <Link :href="u.show_url" class="pf-btn-icon pf-action-btn" title="Detay"><i class="bi bi-eye"></i></Link>
                                        <Link :href="u.edit_url" class="pf-btn-save pf-action-btn pf-action-edit" title="Düzenle"><i class="bi bi-pencil"></i></Link>
                                        <button type="button" @click="toggleVerify(u)" :title="u.is_verified ? 'Doğrulamayı kaldır' : 'Doğrula'" class="pf-action-btn-toggle" :class="u.is_verified ? 'status-passive' : 'status-active'" :data-testid="`user-verify-toggle-${u.id}`">
                                            <i class="bi" :class="u.is_verified ? 'bi-shield-x' : 'bi-shield-check'"></i>
                                        </button>
                                        <button v-if="!u.is_self" type="button" class="delete-btn pf-action-btn-delete" @click="deleteUser(u)" title="Sil" :data-testid="`user-delete-${u.id}`"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="7">
                                <div class="pf-empty pf-empty-container text-center">
                                    <div class="pf-empty-icon"><i class="bi bi-people"></i></div>
                                    <div class="pf-empty-title">Kullanıcı bulunamadı</div>
                                    <div class="pf-empty-sub">Arama kriterlerini değiştirmeyi dene.</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="users.has_pages" class="pf-pagination-wrapper">
                <span class="pf-pagination-info"><strong class="text-dark-custom">{{ users.from }}–{{ users.to }}</strong> / {{ users.total }} kullanıcı</span>
                <div class="d-flex gap-1">
                    <Link v-if="prevLink.url" :href="prevLink.url" class="pf-btn-icon pf-pagination-nav-btn"><i class="bi bi-chevron-left"></i></Link>
                    <Link v-for="(l, i) in pageItems()" :key="i" :href="l.url || '#'" class="pf-pagination-item" :class="{ active: l.active }" v-html="l.label" />
                    <Link v-if="nextLink.url" :href="nextLink.url" class="pf-btn-icon pf-pagination-nav-btn"><i class="bi bi-chevron-right"></i></Link>
                </div>
            </div>
        </div>
    </div>
</template>
