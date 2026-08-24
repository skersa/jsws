<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import FaqItem from '@/Components/FaqItem.vue';

const form = useForm({ subject: '', category: '', priority: 'medium', body: '' });
const charCount = computed(() => form.body.length);
function submit() { form.post(route('support.store')); }

const tips = [
    ['Doğru kategoriyi seçin', 'Talebinizin hızlı işleme alınması için en uygun kategoriyi seçtiğinizden emin olun. Yanlış kategori yanıt süresini uzatabilir.'],
    ['Açıklamanızı detaylı yazın', 'Ne zaman, nerede ve nasıl bir sorunla karşılaştığınızı belirtin. Hata mesajı varsa kopyalayarak yapıştırın.'],
    ['Öncelik seviyesi hakkında', 'Öncelik seviyesini gerçekçi tutun. Tüm talepler Yüksek öncelik seçilirse yanıt süreleri uzayabilir.'],
];
</script>

<template>
    <Head title="Yeni Destek Talebi" />
    <div class="container py-4 pf-narrow">
        <div class="admin-toolbar mb-3">
            <div>
                <div class="toolbar-title">Yeni Destek Talebi</div>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><Link :href="route('index')" class="pf-breadcrumb-link">Ana Sayfa</Link></li>
                    <li class="breadcrumb-item"><Link :href="route('support.index')" class="pf-breadcrumb-link">Destek</Link></li>
                    <li class="breadcrumb-item active">Yeni Talep</li>
                </ol></nav>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-head"><div class="admin-card-title"><i class="bi bi-info-circle"></i> Talep Oluşturmadan Önce</div></div>
            <div class="p-3">
                <FaqItem v-for="(t, i) in tips" :key="i" :question="t[0]" :answer="t[1]" />
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-head"><div class="admin-card-title"><i class="bi bi-plus-circle"></i> Talep Oluştur</div></div>
            <div class="p-4">
                <form @submit.prevent="submit">
                    <div class="pf-field">
                        <label class="pf-label">Konu <span class="pf-req">*</span></label>
                        <input type="text" v-model="form.subject" class="pf-input" :class="{ 'is-invalid': form.errors.subject }" placeholder="Talebinizi kısaca özetleyin" maxlength="150" data-testid="support-subject">
                        <div v-if="form.errors.subject" class="pf-error">{{ form.errors.subject }}</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="pf-label">Kategori <span class="pf-req">*</span></label>
                            <select v-model="form.category" class="pf-input" :class="{ 'is-invalid': form.errors.category }" data-testid="support-category">
                                <option value="">Seçin...</option>
                                <option value="general">Genel</option>
                                <option value="billing">Ödeme / Fatura</option>
                                <option value="auction">Müzayede</option>
                                <option value="technical">Teknik Sorun</option>
                                <option value="other">Diğer</option>
                            </select>
                            <div v-if="form.errors.category" class="pf-error">{{ form.errors.category }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="pf-label">Öncelik <span class="pf-req">*</span></label>
                            <select v-model="form.priority" class="pf-input" :class="{ 'is-invalid': form.errors.priority }" data-testid="support-priority">
                                <option value="low">Düşük</option>
                                <option value="medium">Orta</option>
                                <option value="high">Yüksek</option>
                            </select>
                            <div v-if="form.errors.priority" class="pf-error">{{ form.errors.priority }}</div>
                        </div>
                    </div>

                    <div class="pf-field">
                        <label class="pf-label">Açıklama <span class="pf-req">*</span></label>
                        <div class="support-textarea-wrap">
                            <textarea v-model="form.body" class="pf-input" :class="{ 'is-invalid': form.errors.body }" rows="7" placeholder="Sorununuzu detaylı şekilde açıklayın..." maxlength="3000" data-testid="support-body"></textarea>
                            <span class="pf-char-cnt"><span>{{ charCount }}</span>/3000</span>
                        </div>
                        <div v-if="form.errors.body" class="pf-error">{{ form.errors.body }}</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2" :disabled="form.processing" data-testid="support-submit">
                            <i class="bi bi-send"></i> Talebi Gönder
                        </button>
                        <Link :href="route('support.index')" class="btn btn-outline-primary">İptal</Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
