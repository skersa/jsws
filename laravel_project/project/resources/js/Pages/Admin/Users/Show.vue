<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ user: Object });
const u = props.user;
const page = usePage();
const flash = computed(() => page.props.flash || {});
const tab = ref('bilgiler');
const money = (n) => new Intl.NumberFormat('tr-TR').format(Math.round(n || 0)) + ' ₺';

const infoRows = computed(() => [
    ['bi-hash', 'Kullanıcı ID', '#' + u.id],
    ['bi-envelope', 'E-posta', u.email],
    ['bi-phone', 'Telefon', u.phone || '—'],
    ['bi-calendar3', 'Üyelik Tarihi', u.created_at],
    ['bi-shield-check', 'Doğrulama', u.is_verified ? 'Doğrulanmış ✓' : 'Beklemede'],
    ['bi-person-badge', 'Kullanıcı Adı', u.username ? '@' + u.username : '—'],
]);

function toggleVerify() { router.post(u.is_verified ? u.unverify_url : u.verify_url, {}, { preserveScroll: true }); }
function del() {
    const doDelete = () => {
        const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        const fd = new FormData(); fd.append('_method', 'DELETE');
        fetch(u.destroy_url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin' })
            .then((r) => { if (!r.ok) return r.json().then((e) => { throw new Error(e.message || 'Silme başarısız'); }); return r.json().catch(() => ({})); })
            .then((data) => { if (window.ajaxToast) window.ajaxToast('success', (data && data.message) || 'Silindi'); router.visit(u.index_url); })
            .catch((e) => { if (window.ajaxToast) window.ajaxToast('error', e.message); else alert(e.message); });
    };
    if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({ title: 'Hesabı sil?', html: `<strong>${u.name}</strong> ve tüm verileri kalıcı silinecek.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet, sil', cancelButtonText: 'Vazgeç', reverseButtons: true, confirmButtonColor: '#ef4444' }).then((r) => { if (r.isConfirmed) doDelete(); });
    } else if (confirm(u.name + ' silinecek. Emin misiniz?')) doDelete();
}
</script>

<template>
    <Head :title="u.name + ' — Detay'" />
    <div class="pf-root">
        <div class="pf-top">
            <div class="pf-cover"></div>
            <div class="pf-identity">
                <div class="pf-avatar-wrap">
                    <div class="pf-avatar-outer"><img :src="u.avatar_url" :alt="u.name" class="pf-avatar-img"></div>
                </div>
                <div class="pf-identity-right">
                    <div>
                        <div class="pf-uname-row"><span class="pf-uname">{{ u.name }}</span><span class="pf-role-badge">{{ u.role_label }}</span></div>
                        <div v-if="u.username" class="pf-handle">@{{ u.username }}</div>
                        <div class="pf-bio">{{ u.email }}</div>
                    </div>
                </div>
            </div>
            <div class="pf-stats-row">
                <div class="pf-stat"><div class="pf-stat-num">{{ u.auctions_count }}</div><div class="pf-stat-label">İLAN</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ u.bids_count }}</div><div class="pf-stat-label">TEKLİF</div></div>
                <div class="pf-stat"><div class="pf-stat-num">{{ u.watchlist_count }}</div><div class="pf-stat-label">TAKİP</div></div>
                <div class="pf-stat"><div class="pf-stat-num status-indicator" :class="u.is_verified ? 'verified' : 'pending'">{{ u.is_verified ? '✓' : '⏳' }}</div><div class="pf-stat-label">DURUM</div></div>
            </div>
            <div class="pf-action-row breadcrumb-action-row">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                        <li class="breadcrumb-item"><Link :href="route('admin.dashboard')" class="pf-link-primary">Admin</Link></li>
                        <li class="breadcrumb-item"><Link :href="u.index_url" class="pf-link-primary">Kullanıcılar</Link></li>
                        <li class="breadcrumb-item active pf-text-muted">{{ u.name }}</li>
                    </ol>
                </nav>
                <div class="pf-action-buttons">
                    <Link :href="u.edit_url" class="pf-btn-save pf-btn-edit-custom"><i class="bi bi-pencil"></i> Düzenle</Link>
                    <Link :href="u.index_url" class="pf-btn-reset pf-btn-back-custom"><i class="bi bi-arrow-left"></i> Geri</Link>
                </div>
            </div>
        </div>

        <div v-if="flash.success" class="pf-alert-success"><i class="bi bi-check-circle-fill"></i> {{ flash.success }}</div>

        <div class="pf-content-area">
            <div class="pf-tab-bar">
                <button class="pf-ptab" :class="{ active: tab==='bilgiler' }" @click="tab='bilgiler'" type="button"><i class="bi bi-person me-1"></i> Bilgiler</button>
                <button class="pf-ptab" :class="{ active: tab==='ilanlar' }" @click="tab='ilanlar'" type="button"><i class="bi bi-grid-3x3-gap-fill me-1"></i> İlanlar</button>
                <button class="pf-ptab" :class="{ active: tab==='teklifler' }" @click="tab='teklifler'" type="button"><i class="bi bi-graph-up me-1"></i> Teklifler</button>
                <button class="pf-ptab" :class="{ active: tab==='islemler' }" @click="tab='islemler'" type="button"><i class="bi bi-gear me-1"></i> İşlemler</button>
            </div>

            <div v-show="tab==='bilgiler'">
                <div class="pf-edit-drawer open info-drawer-clean">
                    <div class="pf-epanel active info-panel-custom">
                        <div v-for="(row, i) in infoRows" :key="i" class="pf-sec-item pf-sec-item-spacing">
                            <div class="pf-sec-icon"><i class="bi pf-icon-color" :class="row[0]"></i></div>
                            <div class="pf-sec-info"><div class="pf-sec-title">{{ row[1] }}</div><div class="pf-sec-sub pf-sec-val-text">{{ row[2] }}</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="tab==='ilanlar'">
                <div v-if="u.auctions.length" class="pf-grid">
                    <a v-for="(a, i) in u.auctions" :key="i" href="#" class="pf-auction-card">
                        <div class="pf-card-img-wrap">
                            <img :src="a.cover" :alt="a.title">
                            <div class="pf-card-price">{{ money(a.price) }}</div>
                            <div class="pf-card-badge"><span v-if="a.status==='active'" class="pf-pulse-dot"></span> {{ a.status_label }}</div>
                        </div>
                        <div class="pf-card-body">
                            <div class="pf-card-title">{{ a.title }}</div>
                            <div class="pf-card-meta"><span><i class="bi bi-calendar3 me-1"></i>{{ a.created }}</span></div>
                        </div>
                    </a>
                </div>
                <div v-else class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-box-seam"></i></div><div class="pf-empty-title">Henüz ilan yok</div><div class="pf-empty-sub">Bu kullanıcı henüz ilan yayınlamamış.</div></div>
            </div>

            <div v-show="tab==='teklifler'">
                <div v-if="u.bids.length" class="pf-table-responsive">
                    <table class="pf-table-clean">
                        <thead><tr class="pf-table-border-bottom"><th class="pf-table-th">Müzayede</th><th class="pf-table-th">Tutar</th><th class="pf-table-th">Tarih</th></tr></thead>
                        <tbody>
                            <tr v-for="(b, i) in u.bids" :key="i" class="pf-table-border-bottom">
                                <td class="pf-table-td-title">{{ b.title }}</td>
                                <td class="pf-table-td-amount">{{ new Intl.NumberFormat('tr-TR',{minimumFractionDigits:2}).format(b.amount) }} ₺</td>
                                <td class="pf-table-td-date">{{ b.time_human }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="pf-empty"><div class="pf-empty-icon"><i class="bi bi-graph-up"></i></div><div class="pf-empty-title">Teklif bulunamadı</div><div class="pf-empty-sub">Bu kullanıcı henüz teklif vermemiş.</div></div>
            </div>

            <div v-show="tab==='islemler'" class="pf-actions-tab-padding">
                <div class="pf-toggle-list">
                    <div class="pf-trow pf-trow-border">
                        <div class="pf-trow-info"><div class="pf-trow-title">Doğrulama Durumu</div><div class="pf-trow-desc">{{ u.is_verified ? 'Hesap şu an doğrulanmış' : 'Hesap henüz doğrulanmamış' }}</div></div>
                        <button type="button" @click="toggleVerify" :class="u.is_verified ? 'pf-btn-status-unverify' : 'pf-btn-status-verify'" data-testid="user-verify-toggle">
                            <i class="bi" :class="u.is_verified ? 'bi-shield-x' : 'bi-shield-check'"></i> {{ u.is_verified ? 'Doğrulamayı Kaldır' : 'Hesabı Doğrula' }}
                        </button>
                    </div>
                    <div class="pf-trow pf-trow-border">
                        <div class="pf-trow-info"><div class="pf-trow-title">Kullanıcıyı Düzenle</div><div class="pf-trow-desc">Ad, e-posta, rol ve şifre güncelle</div></div>
                        <Link :href="u.edit_url" class="pf-btn-save pf-btn-action-edit"><i class="bi bi-pencil"></i> Düzenle</Link>
                    </div>
                    <div v-if="!u.is_self" class="pf-trow pf-trow-padding">
                        <div class="pf-trow-info"><div class="pf-trow-title pf-text-danger">Hesabı Sil</div><div class="pf-trow-desc">Tüm veriler kalıcı olarak silinir</div></div>
                        <button type="button" class="delete-btn pf-btn-action-delete" @click="del" data-testid="user-delete"><i class="bi bi-trash"></i> Kullanıcıyı Sil</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
