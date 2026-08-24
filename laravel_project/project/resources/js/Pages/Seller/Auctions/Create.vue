<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Select2 from '@/Components/Select2.vue';

const props = defineProps({ categories: Array, defaults: Object });

const form = useForm({
    title: '',
    description: '',
    category_id: '',
    condition: 'new',
    location: '',
    starting_price: '',
    min_bid_increment: 1,
    reserve_price: '',
    buy_now_price: '',
    starts_at: props.defaults.starts_at,
    ends_at: props.defaults.ends_at,
    images: [],
});

const fileInput = ref(null);
const previews = ref([]); // { url }
const dragOver = ref(false);

function openPicker() { fileInput.value?.click(); }

function addFiles(fileList) {
    Array.from(fileList).forEach((f) => {
        if (!f.type.startsWith('image/')) return;
        if (form.images.length >= 10) return;
        form.images.push(f);
        previews.value.push({ url: URL.createObjectURL(f) });
    });
}

function onFileChange(e) { addFiles(e.target.files); e.target.value = ''; }
function onDrop(e) { dragOver.value = false; addFiles(e.dataTransfer.files); }

function removeImage(i) {
    try { URL.revokeObjectURL(previews.value[i].url); } catch (err) {}
    previews.value.splice(i, 1);
    form.images.splice(i, 1);
}

const quickDates = [['1 gün', 1], ['3 gün', 3], ['7 gün', 7], ['14 gün', 14], ['30 gün', 30]];
function setEndDate(days) {
    const base = form.starts_at ? new Date(form.starts_at) : new Date();
    base.setDate(base.getDate() + days);
    const off = base.getTimezoneOffset();
    form.ends_at = new Date(base.getTime() - off * 60000).toISOString().slice(0, 16);
}

const imagesError = computed(() => form.errors.images || form.errors['images.0'] || Object.keys(form.errors).find((k) => k.startsWith('images.')) && form.errors[Object.keys(form.errors).find((k) => k.startsWith('images.'))]);

function submit() {
    form.post(route('seller.auctions.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="İlan Oluştur" />
    <div class="au-page-wrap">
        <div class="au-page-head">
            <div class="au-head-left">
                <Link :href="route('seller.auctions.index')" class="au-back-link"><i class="bi bi-arrow-left"></i></Link>
                <h1 class="au-page-title">Yeni İlan Oluştur</h1>
                <span class="pf-role-badge">🏪 Seller</span>
            </div>
        </div>

        <div v-if="Object.keys(form.errors).length" class="au-card au-error-card mb-3">
            <div class="au-error-body">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ Object.keys(form.errors).length }} hata var, lütfen düzelt.
            </div>
        </div>

        <form @submit.prevent="submit">

            <div class="au-card">
                <div class="au-card-body">
                    <div class="pf-label mb-2">
                        Görseller <span class="pf-req">*</span>
                        <span class="pf-hint ms-2">İlk görsel kapak olur · Maks. 10 fotoğraf</span>
                    </div>
                    <div class="au-dropzone" :class="{ 'au-dropzone-hover': dragOver }"
                         @click="openPicker"
                         @dragover.prevent="dragOver = true"
                         @dragleave="dragOver = false"
                         @drop.prevent="onDrop"
                         data-testid="auction-dropzone">
                        <i class="bi bi-cloud-upload"></i>
                        <div class="au-dropzone-title">Tıkla veya sürükle bırak</div>
                        <div class="au-dropzone-hint">PNG, JPG, WEBP · Her biri maks. 4MB</div>
                    </div>
                    <input ref="fileInput" type="file" accept=".png,.jpg,.jpeg,.webp" multiple class="d-none" @change="onFileChange">

                    <div v-if="previews.length" class="au-preview-grid">
                        <div v-for="(pv, i) in previews" :key="i" class="au-preview-item">
                            <img :src="pv.url" alt="">
                            <div v-if="i === 0" class="au-preview-cover">Kapak</div>
                            <button type="button" class="au-img-delete-btn" style="position:absolute;top:6px;right:6px;" @click.stop="removeImage(i)" :data-testid="'remove-image-' + i"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                    <div v-if="imagesError" class="pf-error">{{ imagesError }}</div>
                </div>
            </div>

            <div class="au-card">
                <div class="au-card-body">
                    <div class="au-section-label">Ürün Bilgileri</div>

                    <div class="pf-field">
                        <label class="pf-label">Başlık <span class="pf-req">*</span></label>
                        <input class="pf-input" type="text" v-model="form.title" placeholder="Örn: Vintage Rolex Oyster 1967" maxlength="120" data-testid="auction-title">
                        <div v-if="form.errors.title" class="pf-error">{{ form.errors.title }}</div>
                    </div>

                    <div class="pf-field">
                        <label class="pf-label">Açıklama <span class="pf-req">*</span></label>
                        <textarea class="pf-input" v-model="form.description" rows="4" maxlength="5000" placeholder="Ürün hakkında detaylı bilgi, kusur varsa belirt..." data-testid="auction-description"></textarea>
                        <div v-if="form.errors.description" class="pf-error">{{ form.errors.description }}</div>
                    </div>

                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Kategori</label>
                            <Select2
                                v-model="form.category_id"
                                :options="categories"
                                placeholder="Kategori seçin"
                                :allow-clear="true"
                                select-class="pf-input js-select2"
                                testid="auction-category"
                            />
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

                    <div class="pf-field">
                        <label class="pf-label">Konum</label>
                        <input class="pf-input" type="text" v-model="form.location" placeholder="Örn: İstanbul, Kadıköy">
                    </div>
                </div>
            </div>

            <div class="au-card">
                <div class="au-card-body">
                    <div class="au-section-label">Fiyatlandırma</div>
                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Başlangıç fiyatı (₺) <span class="pf-req">*</span></label>
                            <input class="pf-input" type="number" v-model="form.starting_price" min="1" step="0.01" placeholder="500" data-testid="auction-starting-price">
                            <div v-if="form.errors.starting_price" class="pf-error">{{ form.errors.starting_price }}</div>
                        </div>
                        <div class="pf-field">
                            <label class="pf-label">Min. teklif artışı (₺) <span class="pf-req">*</span></label>
                            <input class="pf-input" type="number" v-model="form.min_bid_increment" min="1" step="0.01" placeholder="50">
                            <div v-if="form.errors.min_bid_increment" class="pf-error">{{ form.errors.min_bid_increment }}</div>
                        </div>
                    </div>
                    <div class="pf-two-col">
                        <div class="pf-field">
                            <label class="pf-label">Gizli taban fiyat (₺)</label>
                            <input class="pf-input" type="number" v-model="form.reserve_price" min="0" step="0.01" placeholder="İsteğe bağlı">
                            <div class="pf-hint">Alıcılar görmez, bu fiyatın altında satılmaz.</div>
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
                    <div class="au-quick-dates">
                        <button v-for="[lbl, d] in quickDates" :key="d" type="button" class="pf-btn-reset" @click="setEndDate(d)">{{ lbl }}</button>
                    </div>
                </div>
            </div>

            <div class="au-footer">
                <Link :href="route('seller.auctions.index')" class="pf-btn-reset text-decoration-none">İptal</Link>
                <button type="submit" class="pf-btn-save" :disabled="form.processing" data-testid="auction-submit">
                    <i class="bi bi-rocket me-1"></i> İlanı Yayınla
                </button>
            </div>
        </form>
    </div>
</template>
