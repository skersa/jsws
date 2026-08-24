<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Select2 from '@/Components/Select2.vue';

const props = defineProps({ auction: Object, categories: Array });
const a = props.auction;

const form = useForm({
    title: a.title,
    description: a.description,
    category_id: a.category_id || '',
    condition: a.condition,
    location: a.location || '',
    status: a.status,
    starting_price: a.starting_price,
    min_bid_increment: a.min_bid_increment,
    reserve_price: a.reserve_price ?? '',
    buy_now_price: a.buy_now_price ?? '',
    starts_at: a.starts_at,
    ends_at: a.ends_at,
});
const errCount = computed(() => Object.keys(form.errors).length);
function submit() { form.transform((d) => ({ ...d, _method: 'put' })).post(a.update_url); }
</script>

<template>
    <Head title="İlanı Düzenle" />
    <div class="au-page-wrap">
        <div class="au-page-head">
            <div class="au-head-left">
                <Link :href="a.show_url" class="au-back-link"><i class="bi bi-arrow-left"></i></Link>
                <h1 class="au-page-title">İlanı Düzenle</h1>
                <span class="pf-role-badge">🛡 Admin</span>
            </div>
        </div>

        <div v-if="errCount" class="au-card au-error-card mb-3">
            <div class="au-error-body"><i class="bi bi-exclamation-circle me-1"></i> {{ errCount }} hata var, lütfen düzelt.</div>
        </div>

        <form @submit.prevent="submit">
            <div class="au-card">
                <div class="au-card-body">
                    <div class="au-section-label">Ürün Bilgileri</div>
                    <div class="pf-field">
                        <label class="pf-label">Başlık <span class="pf-req">*</span></label>
                        <input class="pf-input" type="text" v-model="form.title" maxlength="120" data-testid="auction-title">
                        <div v-if="form.errors.title" class="pf-error">{{ form.errors.title }}</div>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Açıklama <span class="pf-req">*</span></label>
                        <textarea class="pf-input" v-model="form.description" rows="5" maxlength="5000" data-testid="auction-description"></textarea>
                        <div v-if="form.errors.description" class="pf-error">{{ form.errors.description }}</div>
                    </div>
                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Kategori</label>
                            <Select2 v-model="form.category_id" :options="categories" label-key="name"
                                     placeholder="Kategori seçin" :allow-clear="true"
                                     testid="auction-category" />
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Ürün Durumu <span class="pf-req">*</span></label>
                            <select class="pf-input" v-model="form.condition" data-testid="auction-condition">
                                <option value="new">Sıfır</option>
                                <option value="used">İkinci El</option>
                                <option value="refurbished">Yenilenmiş</option>
                            </select>
                            <div v-if="form.errors.condition" class="pf-error">{{ form.errors.condition }}</div>
                        </div>
                    </div>
                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Konum</label>
                            <input class="pf-input" type="text" v-model="form.location" placeholder="Örn: İstanbul, Kadıköy">
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Durum <span class="pf-req">*</span></label>
                            <select class="pf-input" v-model="form.status" data-testid="auction-status">
                                <option value="draft">Bekliyor</option>
                                <option value="active">Aktif</option>
                                <option value="rejected">Reddedildi</option>
                                <option value="ended">Bitti</option>
                                <option value="cancelled">İptal</option>
                                <option value="sold">Satıldı</option>
                            </select>
                            <div v-if="form.errors.status" class="pf-error">{{ form.errors.status }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="au-card">
                <div class="au-card-body">
                    <div class="au-section-label">Fiyatlandırma</div>
                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Başlangıç fiyatı (₺) <span class="pf-req">*</span></label>
                            <input class="pf-input" type="number" v-model="form.starting_price" min="1" step="0.01">
                            <div v-if="form.errors.starting_price" class="pf-error">{{ form.errors.starting_price }}</div>
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Min. teklif artışı (₺) <span class="pf-req">*</span></label>
                            <input class="pf-input" type="number" v-model="form.min_bid_increment" min="1" step="0.01">
                            <div v-if="form.errors.min_bid_increment" class="pf-error">{{ form.errors.min_bid_increment }}</div>
                        </div>
                    </div>
                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Gizli taban fiyat (₺)</label>
                            <input class="pf-input" type="number" v-model="form.reserve_price" min="0" step="0.01" placeholder="İsteğe bağlı">
                            <div class="pf-hint">Alıcılar görmez.</div>
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Hemen al fiyatı (₺)</label>
                            <input class="pf-input" type="number" v-model="form.buy_now_price" min="0" step="0.01" placeholder="İsteğe bağlı">
                            <div v-if="form.errors.buy_now_price" class="pf-error">{{ form.errors.buy_now_price }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="au-card">
                <div class="au-card-body">
                    <div class="au-section-label">Zamanlama</div>
                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Başlangıç <span class="pf-req">*</span></label>
                            <input class="pf-input" type="datetime-local" v-model="form.starts_at">
                            <div v-if="form.errors.starts_at" class="pf-error">{{ form.errors.starts_at }}</div>
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Bitiş <span class="pf-req">*</span></label>
                            <input class="pf-input" type="datetime-local" v-model="form.ends_at">
                            <div v-if="form.errors.ends_at" class="pf-error">{{ form.errors.ends_at }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="au-footer">
                <Link :href="a.show_url" class="pf-btn-reset">İptal</Link>
                <button type="submit" class="pf-btn-save" :disabled="form.processing" data-testid="auction-save"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
            </div>
        </form>
    </div>
</template>
