<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ category: Object });
const c = props.category;
const page = usePage();
const flash = computed(() => page.props.flash || {});

const tab = ref('bilgiler');

const infoRows = computed(() => [
    ['bi-hash', 'ID', '#' + c.id, true],
    ['bi-tag', 'Adı', c.name, false],
    ['bi-link-45deg', 'Slug', '/' + c.slug, true],
    ['bi-folder2-open', 'Üst Kategori', c.parent_name || '— Ana Kategori —', false],
    ['bi-sort-down', 'Sıralama', c.sort_order, false],
    ['bi-calendar3', 'Oluşturulma', c.created_at, false],
    ['bi-pencil-square', 'Güncellenme', c.updated_at, false],
]);

function toggle() { router.post(c.toggle_url, {}, { preserveScroll: true }); }

function del() {
    const doDelete = () => {
        const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        const fd = new FormData(); fd.append('_method', 'DELETE');
        fetch(c.destroy_url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin' })
            .then((r) => { if (!r.ok) return r.json().then((e) => { throw new Error(e.message || 'Silme başarısız'); }); return r.json().catch(() => ({})); })
            .then((data) => { if (window.ajaxToast) window.ajaxToast('success', (data && data.message) || 'Kategori silindi'); router.visit(c.index_url); })
            .catch((e) => { if (window.ajaxToast) window.ajaxToast('error', e.message); else alert(e.message); });
    };
    const childTxt = c.children_count > 0 ? `<br><strong>${c.children_count}</strong> alt kategori de silinecek.` : 'Bu işlem geri alınamaz.';
    if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({ title: 'Kategoriyi sil?', html: `<strong>${c.name}</strong> silinecek.<br>${childTxt}`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet, sil', cancelButtonText: 'Vazgeç', reverseButtons: true, confirmButtonColor: '#ef4444', heightAuto: false }).then((r) => { if (r.isConfirmed) doDelete(); });
    } else if (confirm(c.name + ' silinecek. Emin misiniz?')) { doDelete(); }
}
</script>

<template>
    <Head :title="c.name + ' — Kategori Detay'" />
    <div class="pf-root">
        <div class="pf-top">
            <div class="pf-cover"></div>
            <div class="pf-identity">
                <div class="pf-avatar-wrap">
                    <div class="pf-avatar-outer pf-cat-avatar-outer">
                        <img :src="c.image_url" :alt="c.name" class="pf-avatar-img pf-cat-avatar-img">
                    </div>
                </div>
                <div class="pf-identity-right">
                    <div>
                        <div class="pf-uname-row">
                            <span class="pf-uname">{{ c.name }}</span>
                            <span v-if="c.is_active" class="pf-role-badge pf-badge-active"><span class="pf-pulse-dot"></span> Aktif</span>
                            <span v-else class="pf-role-badge pf-badge-passive">⏸ Pasif</span>
                        </div>
                        <div class="pf-handle">/{{ c.slug }}</div>
                        <div class="pf-bio">{{ c.description || 'Açıklama eklenmemiş.' }}</div>
                    </div>
                </div>
            </div>

            <div class="pf-stats-row">
                <div class="pf-stat"><div class="pf-stat-num">{{ c.auctions_count }}</div><div class="pf-stat-label">İLAN</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ c.children_count }}</div><div class="pf-stat-label">ALT KAT.</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ c.sort_order }}</div><div class="pf-stat-label">SIRA</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ c.created_year }}</div><div class="pf-stat-label">OLUŞTURMA</div></div>
            </div>

            <div class="pf-action-row pf-action-row-custom">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')" class="pf-link-primary">Admin</Link></li>
                        <li class="breadcrumb-item"><Link :href="c.index_url" class="pf-link-primary">Kategoriler</Link></li>
                        <li class="breadcrumb-item active pf-text-muted">{{ c.name }}</li>
                    </ol>
                </nav>
                <div class="pf-btn-gap">
                    <Link :href="c.edit_url" class="pf-btn-save pf-btn-edit-custom"><i class="bi bi-pencil"></i> Düzenle</Link>
                    <Link :href="c.index_url" class="pf-btn-reset pf-btn-back-custom"><i class="bi bi-arrow-left"></i> Geri</Link>
                </div>
            </div>
        </div>

        <div v-if="flash.category_success" class="pf-alert-success">
            <i class="bi bi-check-circle-fill"></i> {{ flash.category_success }}
        </div>

        <div class="pf-content-area">
            <div class="pf-tab-bar">
                <button class="pf-ptab" :class="{ active: tab==='bilgiler' }" @click="tab='bilgiler'" type="button"><i class="bi bi-info-circle me-1"></i> Bilgiler</button>
                <button class="pf-ptab" :class="{ active: tab==='altlar' }" @click="tab='altlar'" type="button"><i class="bi bi-folder2 me-1"></i> Alt Kategoriler <span v-if="c.children_count>0" class="pf-badge pf-badge-cyan ms-1">{{ c.children_count }}</span></button>
                <button class="pf-ptab" :class="{ active: tab==='islemler' }" @click="tab='islemler'" type="button"><i class="bi bi-gear me-1"></i> İşlemler</button>
            </div>

            <div v-show="tab==='bilgiler'">
                <div class="pf-edit-drawer open pf-drawer-clean">
                    <div class="pf-epanel active pf-panel-custom">
                        <div v-for="(row, i) in infoRows" :key="i" class="pf-sec-item pf-sec-item-spacing">
                            <div class="pf-sec-icon"><i class="bi pf-icon-color" :class="row[0]"></i></div>
                            <div class="pf-sec-info">
                                <div class="pf-sec-title">{{ row[1] }}</div>
                                <div class="pf-sec-sub pf-sec-val-text" :class="{ 'pf-font-mono': row[3] }">{{ row[2] }}</div>
                            </div>
                        </div>
                        <div v-if="c.description" class="pf-sec-item pf-sec-item-spacing">
                            <div class="pf-sec-icon"><i class="bi bi-text-paragraph pf-icon-color"></i></div>
                            <div class="pf-sec-info">
                                <div class="pf-sec-title">Açıklama</div>
                                <div class="pf-sec-sub pf-desc-text">{{ c.description }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="tab==='altlar'">
                <div v-if="c.children.length" class="pf-table-responsive">
                    <table class="pf-table-clean">
                        <thead>
                            <tr class="pf-table-border-bottom">
                                <th class="pf-table-th text-start">Kategori</th>
                                <th class="pf-table-th text-start">İlan</th>
                                <th class="pf-table-th text-start">Durum</th>
                                <th class="pf-table-th text-start">Sıra</th>
                                <th class="pf-table-th text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="child in c.children" :key="child.id" class="pf-table-border-bottom">
                                <td class="pf-table-td">
                                    <div class="pf-child-info-row">
                                        <img :src="child.image_url" :alt="child.name" class="pf-child-thumb">
                                        <div><div class="pf-child-name">{{ child.name }}</div><div class="pf-child-slug">/{{ child.slug }}</div></div>
                                    </div>
                                </td>
                                <td class="pf-table-td pf-child-count">{{ child.auctions_count }}</td>
                                <td class="pf-table-td"><span class="pf-badge-status" :class="child.is_active ? 'status-active' : 'status-passive'">{{ child.is_active ? 'Aktif' : 'Pasif' }}</span></td>
                                <td class="pf-table-td pf-text-muted pf-font-size-13">{{ child.sort_order }}</td>
                                <td class="pf-table-td">
                                    <div class="pf-table-actions">
                                        <Link :href="child.show_url" class="pf-btn-icon pf-icon-btn-custom"><i class="bi bi-eye"></i></Link>
                                        <Link :href="child.edit_url" class="pf-btn-save pf-icon-btn-save-custom"><i class="bi bi-pencil"></i></Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="pf-empty">
                    <div class="pf-empty-icon"><i class="bi bi-folder2"></i></div>
                    <div class="pf-empty-title">Alt kategori yok</div>
                    <div class="pf-empty-sub">Bu kategoriye alt kategori ekleyebilirsin.</div>
                    <Link :href="c.create_child_url" class="pf-btn-save mt-3 d-inline-flex align-items-center gap-1"><i class="bi bi-plus-lg"></i> Alt Kategori Ekle</Link>
                </div>
            </div>

            <div v-show="tab==='islemler'" class="pf-actions-tab-padding">
                <div class="pf-toggle-list">
                    <div class="pf-trow pf-trow-border">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Aktif / Pasif</div>
                            <div class="pf-trow-desc">Kategori şu an {{ c.is_active ? 'aktif' : 'pasif' }}</div>
                        </div>
                        <button type="button" class="pf-btn-toggle-status" :class="c.is_active ? 'active' : 'passive'" @click="toggle" data-testid="category-toggle">
                            <i class="bi" :class="c.is_active ? 'bi-pause-circle' : 'bi-play-circle'"></i> {{ c.is_active ? 'Pasife Al' : 'Aktif Et' }}
                        </button>
                    </div>
                    <div class="pf-trow pf-trow-border">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title">Kategoriyi Düzenle</div>
                            <div class="pf-trow-desc">Ad, görsel, açıklama ve ayarları güncelle</div>
                        </div>
                        <Link :href="c.edit_url" class="pf-btn-save pf-btn-action-edit"><i class="bi bi-pencil"></i> Düzenle</Link>
                    </div>
                    <div class="pf-trow pf-trow-padding">
                        <div class="pf-trow-info">
                            <div class="pf-trow-title pf-text-danger">Kategoriyi Sil</div>
                            <div class="pf-trow-desc">{{ c.children_count > 0 ? c.children_count + ' alt kategori de silinir.' : 'Bu işlem geri alınamaz.' }}</div>
                        </div>
                        <button type="button" class="delete-btn pf-btn-action-delete" @click="del" data-testid="category-delete"><i class="bi bi-trash"></i> Sil</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
