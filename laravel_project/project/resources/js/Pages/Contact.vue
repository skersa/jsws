<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({ settings: Object });
const page = usePage();
const contactSuccess = computed(() => page.props.flash?.contact_success);

const form = useForm({ name: '', email: '', subject: '', message: '' });

function submit() {
    form.post(route('contact.send'), { preserveScroll: true });
}

const socials = [
    { icon: 'bi-instagram', label: 'Instagram', href: '#' },
    { icon: 'bi-twitter-x', label: 'X', href: '#' },
    { icon: 'bi-facebook', label: 'Facebook', href: '#' },
    { icon: 'bi-youtube', label: 'YouTube', href: '#' },
];
</script>

<template>
    <Head title="İletişim" />
    <div class="container py-6">
        <div class="mb-6">
            <h2 class="fw-bold text-muted mb-1">İletişim</h2>
            <span class="text-muted fs-6">Sorularınız için aşağıdaki kanallardan bize ulaşabilirsiniz.</span>
        </div>

        <div class="row g-5">
            <div class="col-lg-7">
                <div class="auction-card p-5 h-100">
                    <h5 class="text-white fw-bold mb-1">Mesaj Gönderin</h5>
                    <p class="text-muted fs-7 mb-5">En geç 24 saat içinde yanıt veririz.</p>

                    <div v-if="contactSuccess" class="alert alert-success d-flex align-items-center p-4 mb-4">
                        <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                        <span>Mesajınız iletildi. En kısa sürede dönüş yapacağız.</span>
                    </div>

                    <form @submit.prevent="submit" novalidate>
                        <div class="row g-4">
                            <div class="col-sm-6">
                                <label class="form-label text-muted fw-semibold fs-7">Ad Soyad</label>
                                <input type="text" v-model="form.name" class="form-control form-control-solid" :class="{ 'is-invalid': form.errors.name }" placeholder="Adınız Soyadınız" data-testid="contact-name">
                                <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted fw-semibold fs-7">E-posta</label>
                                <input type="email" v-model="form.email" class="form-control form-control-solid" :class="{ 'is-invalid': form.errors.email }" placeholder="ornek@mail.com" data-testid="contact-email">
                                <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted fw-semibold fs-7">Konu</label>
                                <select v-model="form.subject" class="form-select form-select-solid" :class="{ 'is-invalid': form.errors.subject }" data-testid="contact-subject">
                                    <option value="" disabled>Konu seçin</option>
                                    <option value="genel">Genel Bilgi</option>
                                    <option value="teknik">Teknik Destek</option>
                                    <option value="odeme">Ödeme / Fatura</option>
                                    <option value="sikayet">Şikayet</option>
                                    <option value="isbirligi">İş Birliği</option>
                                </select>
                                <div v-if="form.errors.subject" class="invalid-feedback">{{ form.errors.subject }}</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted fw-semibold fs-7">Mesajınız</label>
                                <textarea v-model="form.message" rows="5" class="form-control form-control-solid" :class="{ 'is-invalid': form.errors.message }" placeholder="Mesajınızı buraya yazın..." data-testid="contact-message"></textarea>
                                <div v-if="form.errors.message" class="invalid-feedback">{{ form.errors.message }}</div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 fw-bold" :disabled="form.processing" data-testid="contact-submit">
                                    <i class="bi bi-send me-2"></i>Mesaj Gönder
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5 d-flex flex-column gap-3">
                <div class="auction-card p-4 d-flex align-items-start gap-4">
                    <div class="bg-primary bg-opacity-10 rounded-2 p-3 flex-shrink-0"><i class="bi bi-envelope fs-3 text-primary"></i></div>
                    <div>
                        <div class="text-muted fs-7 fw-semibold mb-1">E-posta</div>
                        <div class="text-white fw-bold fs-6">{{ settings.contact_email }}</div>
                        <div class="text-muted fs-7">Genel sorular</div>
                    </div>
                </div>
                <div class="auction-card p-4 d-flex align-items-start gap-4">
                    <div class="bg-primary bg-opacity-10 rounded-2 p-3 flex-shrink-0"><i class="bi bi-headset fs-3 text-primary"></i></div>
                    <div>
                        <div class="text-muted fs-7 fw-semibold mb-1">Destek</div>
                        <div class="text-white fw-bold fs-6">{{ settings.support_email }}</div>
                        <div class="text-muted fs-7">Teknik & sipariş desteği</div>
                    </div>
                </div>
                <div class="auction-card p-4 d-flex align-items-start gap-4">
                    <div class="bg-primary bg-opacity-10 rounded-2 p-3 flex-shrink-0"><i class="bi bi-globe fs-3 text-primary"></i></div>
                    <div>
                        <div class="text-muted fs-7 fw-semibold mb-1">Web Sitesi</div>
                        <div class="text-white fw-bold fs-6">{{ settings.site_url }}</div>
                        <div class="text-muted fs-7">{{ settings.site_name }}</div>
                    </div>
                </div>
                <div class="auction-card p-4">
                    <div class="text-muted fs-7 fw-semibold mb-3">Sosyal Medya</div>
                    <div class="d-flex gap-3 flex-wrap">
                        <a v-for="s in socials" :key="s.label" :href="s.href" target="_blank" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                            <i class="bi" :class="s.icon"></i>{{ s.label }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
