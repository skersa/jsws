<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import OrderProgress from '@/Components/OrderProgress.vue';
import OrderTimeline from '@/Components/OrderTimeline.vue';
import ReviewForm from '@/Components/ReviewForm.vue';

const props = defineProps({ order: Object, buyerBalance: String, review: Object });
const page = usePage();
const flash = computed(() => page.props.flash || {});

const showDispute = ref(!!(page.props.errors && page.props.errors.reason));

const payForm = useForm({});
function pay() { payForm.post(route('orders.pay', props.order.id), { preserveScroll: true }); }

const addrForm = useForm({
    recipient_name: props.order.recipient_name || '',
    recipient_phone: props.order.recipient_phone || '',
    address_line: props.order.shipping_address || '',
    address_city: props.order.address_city || '',
    address_district: props.order.address_district || '',
    address_zip: props.order.address_zip || '',
});
function saveAddress() { addrForm.post(route('orders.address', props.order.id), { preserveScroll: true }); }

const confirmForm = useForm({});
function confirmDelivery() {
    if (confirm('Ürünü teslim aldığınızı onaylıyor musunuz? Ödeme satıcıya aktarılacak.')) {
        confirmForm.post(route('orders.confirm', props.order.id), { preserveScroll: true });
    }
}

const disputeForm = useForm({ reason: '' });
function submitDispute() { disputeForm.post(route('orders.dispute', props.order.id), { preserveScroll: true }); }

const showAddress = computed(() => ['awaiting_payment', 'paid'].includes(props.order.status));
const showTracking = computed(() => ['shipped', 'delivered', 'completed'].includes(props.order.status) && props.order.tracking_number);
const showDisputeBox = computed(() => ['paid', 'shipped', 'delivered'].includes(props.order.status));
</script>

<template>
    <Head :title="`Sipariş ${order.order_number}`" />
    <div class="dash-wrap py-4">
        <div class="admin-toolbar dash-hero">
            <div>
                <div class="toolbar-title">Sipariş {{ order.order_number }}</div>
                <div class="dash-hero-sub">{{ order.auction_title }}</div>
            </div>
            <Link :href="route('orders.index')" class="btn-admin-ghost"><i class="bi bi-arrow-left"></i> Siparişlerim</Link>
        </div>

        <div v-if="flash.success" class="alert alert-success" style="border-radius:12px">{{ flash.success }}</div>
        <div v-if="flash.error" class="alert alert-danger" style="border-radius:12px">{{ flash.error }}</div>

        <div class="admin-card" style="margin-bottom:16px">
            <OrderProgress :steps="order.progress_steps" :cancelled="order.is_cancelled"
                           :status-color="order.status_color" :status-icon="order.status_icon" :status-label="order.status_label" />
        </div>

        <div class="ord-grid">
            <div>
                <div v-if="order.status === 'awaiting_payment'" class="ord-box" data-testid="order-pay-box">
                    <div class="ord-box-title"><i class="bi bi-wallet2"></i> Ödeme Gerekli</div>
                    <p class="pf-text-muted-sm" style="margin-bottom:12px">Kazandığınız ürünün tutarı güvenli <strong>emanet</strong> hesabında tutulur ve ürün elinize ulaşıp onayladığınızda satıcıya aktarılır. Böylece hem siz hem satıcı korunur.</p>
                    <div class="ord-total" style="margin-bottom:14px"><span>Ödenecek</span><span>{{ order.amount }}</span></div>
                    <div class="pf-text-muted-sm" style="margin-bottom:12px">Bakiyeniz: <strong>{{ buyerBalance }}</strong></div>
                    <form @submit.prevent="pay">
                        <button type="submit" class="btn-admin-pri" style="width:100%" :disabled="payForm.processing" data-testid="order-pay-btn"><i class="bi bi-shield-lock me-1"></i> Öde ve Emanete Al</button>
                    </form>
                </div>

                <div v-if="showAddress" class="ord-box" data-testid="order-address-box">
                    <div class="ord-box-title"><i class="bi bi-geo-alt"></i> Teslimat Adresi</div>
                    <form @submit.prevent="saveAddress">
                        <div class="ord-field"><label>Ad Soyad</label><input v-model="addrForm.recipient_name" required data-testid="addr-name"></div>
                        <div class="ord-field"><label>Telefon</label><input v-model="addrForm.recipient_phone" required data-testid="addr-phone"></div>
                        <div class="ord-field"><label>Adres</label><textarea v-model="addrForm.address_line" rows="2" required data-testid="addr-line"></textarea></div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                            <div class="ord-field"><label>İl</label><input v-model="addrForm.address_city" required data-testid="addr-city"></div>
                            <div class="ord-field"><label>İlçe</label><input v-model="addrForm.address_district"></div>
                            <div class="ord-field"><label>Posta Kodu</label><input v-model="addrForm.address_zip"></div>
                        </div>
                        <button type="submit" class="btn-admin-pri" style="width:100%" :disabled="addrForm.processing" data-testid="addr-save-btn"><i class="bi bi-save me-1"></i> Adresi Kaydet</button>
                    </form>
                </div>

                <div v-if="showTracking" class="ord-box" data-testid="order-tracking-box">
                    <div class="ord-box-title"><i class="bi bi-truck"></i> Kargo Takibi</div>
                    <div class="ord-info-row"><span class="k">Kargo Firması</span><span class="v">{{ order.carrier }}</span></div>
                    <div class="ord-info-row"><span class="k">Takip No</span><span class="v">{{ order.tracking_number }}</span></div>
                    <div v-if="order.shipped_at" class="ord-info-row"><span class="k">Gönderim</span><span class="v">{{ order.shipped_at }}</span></div>
                    <a v-if="order.tracking_url" :href="order.tracking_url" target="_blank" class="ord-track-badge" style="margin-top:10px"><i class="bi bi-box-arrow-up-right"></i> Kargoyu Takip Et</a>
                </div>

                <div v-if="order.status === 'shipped'" class="ord-box" data-testid="order-confirm-box">
                    <div class="ord-box-title"><i class="bi bi-box-seam"></i> Teslimat Onayı</div>
                    <p class="pf-text-muted-sm" style="margin-bottom:12px">Ürünü elinize ulaştıysa onaylayın; ödeme satıcıya aktarılacaktır. <template v-if="order.auto_release_at">Onaylamazsanız <strong>{{ order.auto_release_at }}</strong> tarihinde otomatik tamamlanır.</template></p>
                    <form @submit.prevent="confirmDelivery">
                        <button type="submit" class="btn-admin-pri" style="width:100%;margin-bottom:10px" data-testid="order-confirm-btn"><i class="bi bi-check-circle me-1"></i> Teslim Aldım, Onayla</button>
                    </form>
                    <button type="button" class="btn-admin-ghost" style="width:100%" @click="showDispute = true" data-testid="order-dispute-open"><i class="bi bi-exclamation-triangle me-1"></i> Bir sorun mu var?</button>
                </div>

                <div v-if="showDisputeBox" class="ord-box" :style="{ display: showDispute ? 'block' : 'none' }" data-testid="order-dispute-box">
                    <div class="ord-box-title"><i class="bi bi-exclamation-octagon"></i> Sorun Bildir / Anlaşmazlık</div>
                    <form @submit.prevent="submitDispute">
                        <div class="ord-field">
                            <textarea v-model="disputeForm.reason" rows="3" placeholder="Yaşadığınız sorunu detaylıca yazın (örn: ürün hasarlı geldi)..." required data-testid="dispute-reason"></textarea>
                            <div v-if="disputeForm.errors.reason" class="text-danger" style="font-size:12px">{{ disputeForm.errors.reason }}</div>
                        </div>
                        <button type="submit" class="btn-admin-danger" style="width:100%" :disabled="disputeForm.processing" data-testid="dispute-submit-btn">Anlaşmazlık Aç</button>
                    </form>
                </div>

                <div v-if="order.status === 'disputed'" class="ord-box" style="border-color:rgba(239,68,68,.4)">
                    <div class="ord-box-title" style="color:#f87171"><i class="bi bi-exclamation-octagon"></i> Anlaşmazlık İnceleniyor</div>
                    <p class="pf-text-muted-sm">Talebiniz ekibimize iletildi. En kısa sürede sonuçlandırılacaktır.</p>
                    <div class="ord-info-row"><span class="k">Sebep</span><span class="v">{{ order.dispute_reason }}</span></div>
                </div>

                <ReviewForm v-if="order.status === 'completed' && review.seller_username" :review="review" />

                <div class="ord-box">
                    <div class="ord-box-title"><i class="bi bi-clock-history"></i> Sipariş Geçmişi</div>
                    <OrderTimeline :events="order.events" />
                </div>
            </div>

            <div>
                <div class="ord-box">
                    <img :src="order.cover_url" alt="" style="width:100%;height:170px;object-fit:cover;border-radius:12px;margin-bottom:12px">
                    <div style="font-weight:700;font-size:15px;color:var(--text)">{{ order.auction_title }}</div>
                    <div class="pf-text-muted-sm" style="margin:4px 0 14px">Satıcı: {{ order.seller_name }}</div>
                    <div class="ord-info-row"><span class="k">Sipariş No</span><span class="v">{{ order.order_number }}</span></div>
                    <div class="ord-info-row"><span class="k">Durum</span><span class="v" :style="{ color: order.status_color }">{{ order.status_label }}</span></div>
                    <div class="ord-info-row"><span class="k">Ürün Tutarı</span><span class="v">{{ order.amount }}</span></div>
                    <div class="ord-total"><span>Toplam</span><span>{{ order.amount }}</span></div>
                </div>
            </div>
        </div>
    </div>
</template>
