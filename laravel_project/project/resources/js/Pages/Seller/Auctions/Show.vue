<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ auction: Object });
const a = props.auction;

const page = usePage();
const flash = computed(() => page.props.flash || {});

const mainImg = ref(a.cover_url);
const activeImg = ref(a.cover_url);
function switchImg(url) { mainImg.value = url; activeImg.value = url; }

const streamForm = useForm({
    stream_mode: a.stream_mode === 'video' ? 'video' : 'live',
    promo_video_url: a.promo_video_url || '',
});
function saveStream() {
    streamForm.post(a.stream_settings_url, { preserveScroll: true });
}
const liveDisabled = computed(() => streamForm.stream_mode === 'video');

function deleteAuction() {
    const doDelete = () => router.delete(a.destroy_url, {
        onSuccess: () => router.visit(a.index_url),
    });
    if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({
            title: 'İlanı silmek istediğine emin misin?',
            text: 'Bu işlem geri alınamaz.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Evet, sil', cancelButtonText: 'Vazgeç',
            reverseButtons: true, confirmButtonColor: '#ef4444', heightAuto: false,
        }).then((r) => { if (r.isConfirmed) doDelete(); });
    } else if (confirm('İlanı silmek istediğine emin misin? Bu işlem geri alınamaz.')) {
        doDelete();
    }
}

const details = computed(() => [
    ['bi-tag', 'Kategori', a.category_name],
    ['bi-currency-lira', 'Başlangıç', a.starting_price],
    ['bi-arrow-up', 'Min. artış', a.min_bid_increment],
    ['bi-shield-lock', 'Taban fiyat', a.reserve_price],
    ['bi-lightning', 'Hemen al', a.buy_now_price],
    ['bi-star', 'Ürün durumu', a.condition_label],
    ['bi-geo-alt', 'Konum', a.location],
    ['bi-calendar', 'Başlangıç', a.starts_at],
    ['bi-calendar-x', 'Bitiş', a.ends_at],
]);
</script>

<template>
    <Head :title="auction.title" />
    <div class="container-fluid py-3">

        <div class="admin-toolbar mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="toolbar-title">{{ auction.title_short }}</div>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><Link :href="auction.index_url" class="pf-breadcrumb-link">İlanlarım</Link></li>
                            <li class="breadcrumb-item active">Detay</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <Link :href="auction.edit_url" class="btn-admin-pri" data-testid="edit-auction-btn"><i class="bi bi-pencil"></i> Düzenle</Link>
                    <button type="button" class="btn-admin-danger" @click="deleteAuction" data-testid="delete-auction-btn"><i class="bi bi-trash"></i> Kaldır</button>
                </div>
            </div>
        </div>

        <div v-if="flash.profile_success || flash.success" class="pf-alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i> <span>{{ flash.profile_success || flash.success }}</span>
        </div>

        <div class="admin-card mb-3" data-testid="stream-manage-card">
            <div class="admin-card-head">
                <div class="admin-card-title"><i class="bi bi-broadcast"></i> Yayın Yönetimi</div>
                <span v-if="auction.is_live" class="a-badge" style="background:rgba(220,38,38,.15);color:#ef4444;">● CANLI</span>
            </div>
            <div class="p-3">
                <div v-if="streamForm.errors.stream_mode || streamForm.errors.promo_video_url" class="pf-error mb-2">
                    <i class="bi bi-exclamation-circle"></i> {{ streamForm.errors.stream_mode || streamForm.errors.promo_video_url }}
                </div>

                <form @submit.prevent="saveStream" id="streamSettingsForm">
                    <div class="pf-label mb-2">Yayın Türü</div>
                    <div class="d-flex gap-2 mb-2 flex-wrap">
                        <label class="stream-mode-opt" :class="{ active: streamForm.stream_mode === 'live' }">
                            <input type="radio" value="live" v-model="streamForm.stream_mode" data-testid="stream-mode-live">
                            <i class="bi bi-camera-video"></i> Canlı Yayın
                        </label>
                        <label class="stream-mode-opt" :class="{ active: streamForm.stream_mode === 'video' }">
                            <input type="radio" value="video" v-model="streamForm.stream_mode" data-testid="stream-mode-video">
                            <i class="bi bi-film"></i> Tanıtım Videosu
                        </label>
                    </div>

                    <div v-show="streamForm.stream_mode === 'video'" id="videoUrlField">
                        <label class="pf-label">Tanıtım Videosu Linki (YouTube, Vimeo veya .mp4)</label>
                        <input type="url" v-model="streamForm.promo_video_url" class="pf-input" data-testid="promo-video-url-input" placeholder="https://www.youtube.com/watch?v=...">
                        <div class="pf-hint mt-1">İzleyiciler canlı yayın yerine bu videoyu izler.</div>
                    </div>

                    <button type="submit" class="pf-btn-save mt-3" data-testid="save-stream-settings-btn" :disabled="streamForm.processing">
                        <i class="bi bi-floppy me-1"></i> Yayın Ayarını Kaydet
                    </button>
                </form>

                <hr style="border-color:var(--border);margin:16px 0;">

                <template v-if="auction.can_broadcast">
                    <a :href="auction.broadcast_url" class="btn-admin-pri w-100 justify-content-center" data-testid="start-broadcast-btn"
                       :style="'background:#10b981;border-color:#10b981;' + (liveDisabled ? 'opacity:.5;pointer-events:none;' : '')">
                        <i class="bi bi-broadcast"></i> Canlı Yayına Başla
                    </a>
                    <div v-show="liveDisabled" class="pf-hint mt-2">Canlı yayına başlamak için yukarıdan "Canlı Yayın" türünü seç ve kaydet.</div>
                </template>
                <div v-else class="pf-hint">Bu ilan yayına uygun değil (durum: {{ auction.status }}).</div>

                <div v-if="auction.uses_promo_video" class="mt-3">
                    <div class="pf-label mb-1">Tanıtım Videosu Önizleme</div>
                    <div style="border-radius:10px;overflow:hidden;aspect-ratio:16/9;background:#000;" data-testid="promo-video-preview">
                        <video v-if="auction.is_direct_video" :src="auction.promo_video_url" controls style="width:100%;height:100%;object-fit:contain;"></video>
                        <iframe v-else :src="auction.embed_video_url" style="width:100%;height:100%;border:0;" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="admin-card mb-3">
                    <img :src="mainImg" class="w-100" style="height:380px;object-fit:contain;background:var(--bg);border-radius:16px;">
                    <div v-if="auction.images.length > 1" class="d-flex gap-2 p-3" style="overflow-x:auto;">
                        <img v-for="(img, i) in auction.images" :key="i" :src="img.url" @click="switchImg(img.url)"
                             class="thumb-img" :class="{ 'thumb-active': activeImg === img.url }"
                             :style="{ width:'64px', height:'64px', flexShrink:0, objectFit:'cover', borderRadius:'8px', cursor:'pointer', border:'2px solid transparent', transition:'.15s', opacity: activeImg === img.url ? 1 : 0.6 }">
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-file-text"></i> Açıklama</div>
                    </div>
                    <div class="p-3"><p class="pf-desc-text mb-0" style="white-space:pre-line;">{{ auction.description }}</p></div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="admin-card mb-3">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-bar-chart"></i> Özet</div>
                        <span class="a-badge" :class="auction.status_type">{{ auction.status_label }}</span>
                    </div>
                    <div class="p-3">
                        <div class="row g-2">
                            <div class="col-6"><div class="pf-stat-card"><div class="pf-stat-icon-wrapper" style="background:rgba(21,94,239,.12);"><i class="bi bi-currency-dollar" style="color:var(--primary);"></i></div><div><div class="pf-stat-number" style="font-size:18px;">{{ auction.display_price }}</div><div class="pf-stat-label">Mevcut Fiyat</div></div></div></div>
                            <div class="col-6"><div class="pf-stat-card"><div class="pf-stat-icon-wrapper" style="background:rgba(16,185,129,.12);"><i class="bi bi-people" style="color:#10b981;"></i></div><div><div class="pf-stat-number" style="font-size:18px;">{{ auction.bid_count }}</div><div class="pf-stat-label">Teklif</div></div></div></div>
                            <div class="col-6"><div class="pf-stat-card"><div class="pf-stat-icon-wrapper" style="background:rgba(251,191,36,.12);"><i class="bi bi-eye" style="color:#fbbf24;"></i></div><div><div class="pf-stat-number" style="font-size:18px;">{{ auction.view_count }}</div><div class="pf-stat-label">Görüntülenme</div></div></div></div>
                            <div class="col-6"><div class="pf-stat-card"><div class="pf-stat-icon-wrapper" style="background:rgba(239,68,68,.1);"><i class="bi bi-clock" style="color:#f87171;"></i></div><div><div class="pf-stat-number" style="font-size:18px;">{{ auction.time_left }}</div><div class="pf-stat-label">Kalan Süre</div></div></div></div>
                        </div>
                    </div>
                </div>

                <div class="admin-card mb-3">
                    <div class="admin-card-head"><div class="admin-card-title"><i class="bi bi-info-circle"></i> Detaylar</div></div>
                    <div class="p-3">
                        <div v-for="(row, i) in details" :key="i" class="admin-info-row">
                            <div class="admin-info-icon"><i class="bi" :class="row[0]"></i></div>
                            <div class="flex-grow-1">
                                <div class="admin-info-lbl">{{ row[1] }}</div>
                                <div class="admin-info-val">{{ row[2] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="auction.bids.length" class="admin-card">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-list-ol"></i> Son Teklifler</div>
                        <span class="a-badge info">{{ auction.bid_count }} teklif</span>
                    </div>
                    <table class="admin-table">
                        <thead><tr><th>Kullanıcı</th><th>Tutar</th><th>Zaman</th></tr></thead>
                        <tbody>
                            <tr v-for="(bid, i) in auction.bids" :key="i">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img :src="bid.user_avatar" class="a-avatar" style="border-radius:50%;">
                                        <div>
                                            <div style="font-weight:600;font-size:var(--fs-sm);">{{ bid.user_name }}</div>
                                            <span v-if="bid.is_auto" class="a-badge info">Otomatik</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="pf-table-td-amount">{{ bid.amount }}</td>
                                <td class="pf-text-muted-sm">{{ bid.time }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
