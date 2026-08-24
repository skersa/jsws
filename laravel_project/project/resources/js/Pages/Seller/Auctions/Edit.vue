<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Select2 from '@/Components/Select2.vue';

const props = defineProps({ auction: Object, categories: Array });
const a = props.auction;

const page = usePage();
const flash = computed(() => page.props.flash || {});

const form = useForm({
    title: a.title,
    description: a.description,
    category_id: a.category_id || '',
    condition: a.condition,
    location: a.location || '',
    starting_price: a.starting_price,
    min_bid_increment: a.min_bid_increment,
    reserve_price: a.reserve_price || '',
    buy_now_price: a.buy_now_price || '',
    ends_at: a.ends_at,
    delete_images: [],
    new_images: [],
});

function toggleDelete(id) {
    const idx = form.delete_images.indexOf(id);
    if (idx === -1) form.delete_images.push(id);
    else form.delete_images.splice(idx, 1);
}
const isMarkedDelete = (id) => form.delete_images.includes(id);

const fileInput = ref(null);
const newPreviews = ref([]);
function openPicker() { fileInput.value?.click(); }
function addFiles(list) {
    Array.from(list).forEach((f) => {
        if (!f.type.startsWith('image/')) return;
        form.new_images.push(f);
        newPreviews.value.push({ url: URL.createObjectURL(f) });
    });
}
function onFileChange(e) { addFiles(e.target.files); e.target.value = ''; }
function removeNew(i) {
    try { URL.revokeObjectURL(newPreviews.value[i].url); } catch (err) {}
    newPreviews.value.splice(i, 1);
    form.new_images.splice(i, 1);
}

function submit() {
    form.transform((data) => ({ ...data, _method: 'put' })).post(a.update_url, { forceFormData: true });
}
</script>

<template>
    <Head title="İlanı Düzenle" />
    <div class="au-page-wrap">
        <div class="au-page-head">
            <div class="au-head-left">
                <Link :href="a.show_url" class="au-back-link"><i class="bi bi-arrow-left"></i></Link>
                <h1 class="au-page-title">İlanı Düzenle</h1>
            </div>
        </div>

        <div v-if="flash.success" class="pf-alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i> <span>{{ flash.success }}</span>
        </div>

        <div v-if="Object.keys(form.errors).length" class="au-card au-error-card mb-3">
            <div class="au-error-body"><i class="bi bi-exclamation-circle me-1"></i> {{ Object.keys(form.errors).length }} hata var.</div>
        </div>

        <form @submit.prevent="submit">

            <div class="au-card">
                <div class="au-card-body">
                    <div class="pf-label mb-2">Mevcut Görseller</div>
                    <div class="au-img-grid">
                        <div v-for="img in a.images" :key="img.id" class="au-img-item" :style="{ opacity: isMarkedDelete(img.id) ? 0.3 : 1 }">
                            <img :src="img.url" :class="{ 'au-img-cover-ring': img.is_cover }" alt="">
                            <span v-if="img.is_cover" class="au-img-cover-badge">Kapak</span>
                            <button type="button" class="au-img-delete-label" style="border:0;background:transparent;padding:0;cursor:pointer;" @click="toggleDelete(img.id)" :data-testid="'toggle-delete-' + img.id">
                                <span class="au-img-delete-btn"><i class="bi" :class="isMarkedDelete(img.id) ? 'bi-arrow-counterclockwise' : 'bi-x'"></i></span>
                            </button>
                        </div>
                    </div>
                    <div class="pf-hint mt-2">X işaretli görseller kaydedince silinir.</div>

                    <div class="pf-label mt-3 mb-1">Yeni Görsel Ekle</div>
                    <div class="au-dropzone" @click="openPicker" data-testid="edit-add-images" style="padding:18px;">
                        <i class="bi bi-plus-circle"></i>
                        <div class="au-dropzone-title">Yeni fotoğraf ekle</div>
                    </div>
                    <input ref="fileInput" type="file" accept=".png,.jpg,.jpeg,.webp" multiple class="d-none" @change="onFileChange">
                    <div v-if="newPreviews.length" class="au-preview-grid">
                        <div v-for="(pv, i) in newPreviews" :key="i" class="au-preview-item">
                            <img :src="pv.url" alt="">
                            <button type="button" class="au-img-delete-btn" style="position:absolute;top:6px;right:6px;" @click.stop="removeNew(i)"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>
            </div>

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
                        <textarea class="pf-input" v-model="form.description" rows="4" maxlength="5000"></textarea>
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
                            <select class="pf-input" v-model="form.condition">
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
                            <input class="pf-input" type="number" v-model="form.starting_price" min="1" step="0.01" :disabled="a.has_bids">
                            <div v-if="a.has_bids" class="pf-hint">Teklif alındığı için değiştirilemez.</div>
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
                    <div class="pf-field">
                        <label class="pf-label">Bitiş <span class="pf-req">*</span></label>
                        <input class="pf-input" type="datetime-local" v-model="form.ends_at">
                        <div v-if="form.errors.ends_at" class="pf-error">{{ form.errors.ends_at }}</div>
                    </div>
                </div>
            </div>

            <div class="au-footer">
                <Link :href="a.show_url" class="pf-btn-reset text-decoration-none">İptal</Link>
                <button type="submit" class="pf-btn-save" :disabled="form.processing" data-testid="auction-update-submit">
                    <i class="bi bi-check-lg me-1"></i> Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
</template>
