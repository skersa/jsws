<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Select2 from '@/Components/Select2.vue';

const props = defineProps({ parents: Array, category: Object });
const c = props.category;

const tab = ref('genel');
const form = useForm({
    name: c.name,
    slug: c.slug || '',
    parent_id: c.parent_id || '',
    image: null,
    description: c.description || '',
    sort_order: c.sort_order,
    is_active: !!c.is_active,
});

const preview = ref(c.image_url);
const fileEl = ref(null);
function onImage(e) {
    const f = e.target.files[0];
    if (!f) return;
    form.image = f;
    preview.value = URL.createObjectURL(f);
}
const descLen = computed(() => (form.description || '').length);
const errList = computed(() => Object.values(form.errors));

function submit() {
    form.transform((d) => ({ ...d, _method: 'put' })).post(c.update_url, { forceFormData: true });
}
</script>

<template>
    <Head :title="'Düzenle — ' + c.name" />
    <div class="pf-root">
        <div class="pf-top pf-top-padding">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="pf-title-text">Düzenle — {{ c.name }}</div>
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><Link :href="route('admin.dashboard')" class="pf-link-primary">Admin</Link></li>
                            <li class="breadcrumb-item"><Link :href="c.index_url" class="pf-link-primary">Kategoriler</Link></li>
                            <li class="breadcrumb-item"><Link :href="c.show_url" class="pf-link-primary">{{ c.name }}</Link></li>
                            <li class="breadcrumb-item active pf-text-muted">Düzenle</li>
                        </ol>
                    </nav>
                </div>
                <Link :href="c.show_url" class="pf-btn-reset pf-btn-back-custom"><i class="bi bi-arrow-left"></i> Geri</Link>
            </div>
        </div>

        <div class="pf-edit-drawer open">
            <div class="pf-edit-tabs">
                <button class="pf-etab" :class="{ active: tab==='genel' }" @click="tab='genel'" type="button"><i class="bi bi-grid me-1"></i> Genel</button>
                <button class="pf-etab" :class="{ active: tab==='gorsel' }" @click="tab='gorsel'" type="button"><i class="bi bi-image me-1"></i> Görsel &amp; Açıklama</button>
                <button class="pf-etab" :class="{ active: tab==='ayarlar' }" @click="tab='ayarlar'" type="button"><i class="bi bi-sliders me-1"></i> Ayarlar</button>
            </div>

            <form @submit.prevent="submit" enctype="multipart/form-data">
                <div v-if="errList.length" class="pf-alert-success pf-alert-error-custom">
                    <i class="bi bi-exclamation-circle-fill pf-text-danger"></i>
                    <span class="pf-text-danger">{{ errList.join(' · ') }}</span>
                </div>

                <div class="pf-epanel" :class="{ active: tab==='genel' }">
                    <div class="pf-field">
                        <label class="pf-label">Kategori Adı <span class="pf-req">*</span></label>
                        <input class="pf-input" type="text" v-model="form.name" placeholder="Kategori adı" required data-testid="category-name">
                        <div v-if="form.errors.name" class="pf-error">{{ form.errors.name }}</div>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Slug</label>
                        <div class="pf-input-pre">
                            <span class="pf-pre-label">/</span>
                            <input type="text" v-model="form.slug" maxlength="191">
                        </div>
                        <div class="pf-hint">Değiştirmezsen ad güncellendiğinde otomatik üretilir.</div>
                        <div v-if="form.errors.slug" class="pf-error">{{ form.errors.slug }}</div>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Üst Kategori</label>
                        <Select2 v-model="form.parent_id" :options="parents" placeholder="— Ana Kategori —" :allow-clear="true" select-class="pf-input js-select2" testid="category-parent" />
                        <div v-if="form.errors.parent_id" class="pf-error">{{ form.errors.parent_id }}</div>
                    </div>
                </div>

                <div class="pf-epanel" :class="{ active: tab==='gorsel' }">
                    <div class="pf-avatar-upload-row">
                        <label class="pf-upload-avatar pf-category-upload-label" title="Görsel değiştir">
                            <img :src="preview" :alt="c.name" class="pf-category-preview-img">
                            <input ref="fileEl" type="file" accept=".png,.jpg,.jpeg,.webp" class="d-none" @change="onImage" data-testid="category-image">
                        </label>
                        <div>
                            <div class="pf-upload-title">Kategori görseli</div>
                            <div class="pf-upload-desc">PNG, JPG, WEBP · Maks. 2MB</div>
                            <label class="pf-btn-photo mt-2 d-inline-flex align-items-center gap-1 pf-cursor-pointer" @click="fileEl?.click()"><i class="bi bi-upload"></i> Görsel değiştir</label>
                            <div v-if="c.has_image" class="pf-hint mt-1">Mevcut görsel korunur, yeni yüklersen değişir.</div>
                        </div>
                    </div>
                    <div v-if="form.errors.image" class="pf-error mt-1">{{ form.errors.image }}</div>
                    <div class="pf-field mt-3">
                        <label class="pf-label">Açıklama</label>
                        <div class="pf-relative">
                            <textarea class="pf-input" v-model="form.description" rows="4" maxlength="1000" placeholder="Kategori hakkında kısa açıklama..."></textarea>
                            <span class="pf-char-cnt">{{ descLen }}/1000</span>
                        </div>
                        <div v-if="form.errors.description" class="pf-error">{{ form.errors.description }}</div>
                    </div>
                </div>

                <div class="pf-epanel" :class="{ active: tab==='ayarlar' }">
                    <div class="pf-field">
                        <label class="pf-label">Sıralama</label>
                        <input class="pf-input" type="number" v-model="form.sort_order" min="0" max="9999" placeholder="0">
                        <div class="pf-hint">Küçük değer öne gelir.</div>
                        <div v-if="form.errors.sort_order" class="pf-error">{{ form.errors.sort_order }}</div>
                    </div>
                    <div class="pf-toggle-list">
                        <label class="pf-trow pf-trow-border-none">
                            <div class="pf-trow-info">
                                <div class="pf-trow-title">Kategori Aktif</div>
                                <div class="pf-trow-desc">Pasif kategoriler sitede görünmez</div>
                            </div>
                            <input type="checkbox" v-model="form.is_active" class="pf-tog-input" data-testid="category-active">
                        </label>
                    </div>
                </div>

                <div class="pf-footer">
                    <span class="pf-save-info"><i class="bi bi-clock"></i> Son güncelleme: {{ c.updated_human }}</span>
                    <div class="d-flex gap-2">
                        <Link :href="c.show_url" class="pf-btn-reset">İptal</Link>
                        <button type="submit" class="pf-btn-save" :disabled="form.processing" data-testid="category-save"><i class="bi bi-floppy me-1"></i> Kaydet</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
