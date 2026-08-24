<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import OrderProgress from '@/Components/OrderProgress.vue';
import OrderTimeline from '@/Components/OrderTimeline.vue';

const props = defineProps({ order: Object });
const o = props.order;
const page = usePage();
const flash = computed(() => page.props.flash || {});

function resolve(decision) {
    const msg = decision === 'buyer' ? 'Alıcıya iade edilsin mi?' : 'Ödeme satıcıya aktarılsın mı?';
    if (window.confirm(msg)) {
        router.post(o.resolve_url, { decision }, { preserveScroll: true });
    }
}
</script>

<template>
    <Head :title="`Sipariş ${o.order_number}`" />
    <div class="dash-wrap py-4">
        <div class="admin-toolbar dash-hero">
            <div>
                <div class="toolbar-title">Sipariş {{ o.order_number }}</div>
                <div class="dash-hero-sub">{{ o.auction_title }}</div>
            </div>
            <Link :href="o.index_url" class="btn-admin-ghost"><i class="bi bi-arrow-left"></i> Geri</Link>
        </div>

        <div v-if="flash.success" class="alert alert-success" style="border-radius:12px">{{ flash.success }}</div>

        <div class="admin-card" style="margin-bottom:16px">
            <OrderProgress :steps="o.progress_steps" :cancelled="o.is_cancelled"
                           :status-color="o.status_color" :status-icon="o.status_icon" :status-label="o.status_label" />
        </div>

        <div class="ord-grid">
            <div>
                <div v-if="o.status === 'disputed'" class="ord-box" style="border-color:rgba(239,68,68,.4)" data-testid="admin-dispute-box">
                    <div class="ord-box-title" style="color:#f87171"><i class="bi bi-exclamation-octagon"></i> Anlaşmazlık Çözümü</div>
                    <div class="ord-info-row"><span class="k">Alıcının şikayeti</span></div>
                    <p class="pf-text-muted-sm" style="margin:6px 0 14px">{{ o.dispute_reason }}</p>
                    <div style="display:flex;gap:10px">
                        <button @click="resolve('buyer')" class="btn-admin-danger" style="flex:1" data-testid="resolve-buyer-btn">Alıcı Lehine (İade)</button>
                        <button @click="resolve('seller')" class="btn-admin-pri" style="flex:1" data-testid="resolve-seller-btn">Satıcı Lehine (Öde)</button>
                    </div>
                </div>

                <div class="ord-box">
                    <div class="ord-box-title"><i class="bi bi-info-circle"></i> Sipariş Bilgileri</div>
                    <div class="ord-info-row"><span class="k">Alıcı</span><span class="v">{{ o.buyer_name }}</span></div>
                    <div class="ord-info-row"><span class="k">Satıcı</span><span class="v">{{ o.seller_name }}</span></div>
                    <div class="ord-info-row"><span class="k">Emanet Durumu</span><span class="v">{{ o.escrow_status }}</span></div>
                    <div class="ord-info-row"><span class="k">Kargo</span><span class="v">{{ o.carrier ? o.carrier + ' • ' + o.tracking_number : '—' }}</span></div>
                    <div v-if="o.has_shipping_address" class="ord-info-row"><span class="k">Adres</span><span class="v" style="max-width:60%">{{ o.recipient_name }}, {{ o.shipping_address }}, {{ o.address_city }}</span></div>
                </div>

                <div class="ord-box">
                    <div class="ord-box-title"><i class="bi bi-clock-history"></i> Zaman Çizelgesi</div>
                    <OrderTimeline :events="o.events" />
                </div>
            </div>

            <div>
                <div class="ord-box">
                    <img :src="o.cover_url" alt="" style="width:100%;height:170px;object-fit:cover;border-radius:12px;margin-bottom:12px">
                    <div style="font-weight:700;color:var(--text)">{{ o.auction_title }}</div>
                    <div class="ord-info-row" style="margin-top:10px"><span class="k">Tutar</span><span class="v">{{ o.amount }}</span></div>
                    <div class="ord-info-row"><span class="k">Komisyon</span><span class="v">{{ o.commission_amount }}</span></div>
                    <div class="ord-info-row"><span class="k">Durum</span><span class="v" :style="{ color: o.status_color }">{{ o.status_label }}</span></div>
                </div>
            </div>
        </div>
    </div>
</template>
