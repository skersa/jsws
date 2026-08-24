<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head } from '@inertiajs/vue3';
const props = defineProps({ privacyText: { type: String, default: null }, settings: Object, updatedAt: String });

const rights = [
    'Kişisel verilerinizin işlenip işlenmediğini öğrenme',
    'Verilerinize erişim ve kopyasını talep etme',
    'Hatalı verilerin düzeltilmesini isteme',
    'Verilerinizin silinmesini talep etme',
    'İşleme itiraz etme',
    "KVK Kurulu'na şikayette bulunma",
];
</script>

<template>
    <Head title="Gizlilik Politikası" />
    <div class="container py-6">
        <div class="mb-6">
            <h2 class="fw-bold text-muted mb-1">Gizlilik Politikası</h2>
            <span class="text-muted fs-6">Son güncelleme: <span class="fw-semibold">{{ updatedAt }}</span></span>
        </div>

        <div class="row g-5">
            <div class="col-lg-3 d-none d-lg-block">
                <div class="auction-card p-4 sticky-top" style="top: 100px;">
                    <div class="text-muted fs-7 fw-bold mb-3 text-uppercase">İçindekiler</div>
                    <nav class="d-flex flex-column gap-2">
                        <a href="#genel" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7 py-1">Genel Bilgi</a>
                        <a href="#toplanan" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7 py-1">Toplanan Veriler</a>
                        <a href="#kullanim" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7 py-1">Veri Kullanımı</a>
                        <a href="#cerezler" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7 py-1">Çerezler</a>
                        <a href="#paylasim" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7 py-1">Veri Paylaşımı</a>
                        <a href="#guvenlik" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7 py-1">Veri Güvenliği</a>
                        <a href="#haklariniz" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7 py-1">Haklarınız</a>
                        <a href="#iletisim" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7 py-1">İletişim</a>
                    </nav>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="auction-card p-5 p-md-7">
                    <div v-if="privacyText" v-html="privacyText"></div>
                    <template v-else>
                        <div id="genel" class="mb-7">
                            <p class="text-muted lh-lg">{{ settings.site_name }} olarak kullanıcılarımızın gizliliğine büyük önem veriyoruz. Bu politika, platformumuzu kullandığınızda hangi verileri topladığımızı, nasıl işlediğimizi ve haklarınızı açıklamaktadır. 6698 sayılı KVKK kapsamında veri sorumlusu sıfatıyla hareket etmekteyiz.</p>
                        </div>
                        <div class="separator separator-dashed mb-7"></div>
                        <div id="toplanan" class="mb-7">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-2 p-2 d-inline-flex"><i class="bi bi-database fs-4 text-primary"></i></div>
                                <h5 class="text-white fw-bold mb-0">1. Toplanan Veriler</h5>
                            </div>
                            <ul class="text-muted lh-lg ps-4 mb-0">
                                <li class="mb-2"><span class="text-white fw-semibold">Kimlik Bilgileri:</span> Ad, soyad, T.C. kimlik numarası (zorunlu işlemler için).</li>
                                <li class="mb-2"><span class="text-white fw-semibold">İletişim Bilgileri:</span> E-posta adresi, telefon numarası, fatura/teslimat adresi.</li>
                                <li class="mb-2"><span class="text-white fw-semibold">Hesap Bilgileri:</span> Kullanıcı adı, şifre (şifrelenmiş), profil fotoğrafı.</li>
                                <li class="mb-2"><span class="text-white fw-semibold">İşlem Bilgileri:</span> Teklif geçmişi, satın alma kayıtları, ödeme yöntemi.</li>
                                <li class="mb-0"><span class="text-white fw-semibold">Teknik Veriler:</span> IP adresi, tarayıcı tipi, işletim sistemi, oturum süreleri.</li>
                            </ul>
                        </div>
                        <div class="separator separator-dashed mb-7"></div>
                        <div id="kullanim" class="mb-7">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-2 p-2 d-inline-flex"><i class="bi bi-gear fs-4 text-primary"></i></div>
                                <h5 class="text-white fw-bold mb-0">2. Veri Kullanımı</h5>
                            </div>
                            <ul class="text-muted lh-lg ps-4 mb-0">
                                <li class="mb-2">Hesabınızı oluşturmak ve yönetmek</li>
                                <li class="mb-2">Açık artırma işlemlerini gerçekleştirmek ve doğrulamak</li>
                                <li class="mb-2">Ödeme süreçlerini tamamlamak</li>
                                <li class="mb-2">Müşteri desteği sağlamak</li>
                                <li class="mb-2">Platform güvenliğini sağlamak ve dolandırıcılığı önlemek</li>
                                <li class="mb-0">Yasal yükümlülükleri yerine getirmek</li>
                            </ul>
                        </div>
                        <div class="separator separator-dashed mb-7"></div>
                        <div id="cerezler" class="mb-7">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-2 p-2 d-inline-flex"><i class="bi bi-cookie fs-4 text-primary"></i></div>
                                <h5 class="text-white fw-bold mb-0">3. Çerezler</h5>
                            </div>
                            <ul class="text-muted lh-lg ps-4 mb-3">
                                <li class="mb-2"><span class="text-white fw-semibold">Zorunlu Çerezler:</span> Oturum ve güvenlik yönetimi için gereklidir.</li>
                                <li class="mb-2"><span class="text-white fw-semibold">Analitik Çerezler:</span> Platformun nasıl kullanıldığını anlamamıza yardımcı olur.</li>
                                <li class="mb-0"><span class="text-white fw-semibold">Tercih Çerezleri:</span> Dil ve tema gibi tercihlerinizi hatırlar.</li>
                            </ul>
                            <p class="text-muted lh-lg mb-0">Tarayıcı ayarlarınızdan çerezleri yönetebilir veya silebilirsiniz.</p>
                        </div>
                        <div class="separator separator-dashed mb-7"></div>
                        <div id="paylasim" class="mb-7">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-2 p-2 d-inline-flex"><i class="bi bi-share fs-4 text-primary"></i></div>
                                <h5 class="text-white fw-bold mb-0">4. Veri Paylaşımı</h5>
                            </div>
                            <p class="text-muted lh-lg mb-3">Kişisel verileriniz hiçbir koşulda üçüncü taraflara satılmaz. Yalnızca şu durumlarda paylaşılabilir:</p>
                            <ul class="text-muted lh-lg ps-4 mb-0">
                                <li class="mb-2">Ödeme altyapısı sağlayıcılarıyla (yalnızca işlem tamamlamak için)</li>
                                <li class="mb-2">Kargo ve teslimat firmaları ile (adres bilgisi)</li>
                                <li class="mb-2">Yasal zorunluluk halinde yetkili kamu kurumlarıyla</li>
                                <li class="mb-0">Açık rızanız olması durumunda iş ortaklarımızla</li>
                            </ul>
                        </div>
                        <div class="separator separator-dashed mb-7"></div>
                        <div id="guvenlik" class="mb-7">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-2 p-2 d-inline-flex"><i class="bi bi-shield-lock fs-4 text-primary"></i></div>
                                <h5 class="text-white fw-bold mb-0">5. Veri Güvenliği</h5>
                            </div>
                            <p class="text-muted lh-lg mb-0">Verilerinizi korumak için SSL/TLS şifrelemesi, güvenlik duvarları ve erişim kontrol mekanizmaları kullanıyoruz. Şifreleriniz bcrypt ile şifrelenerek saklanır. Bir güvenlik açığı fark etmeniz durumunda lütfen <span class="text-primary">{{ settings.contact_email }}</span> adresine bildirin.</p>
                        </div>
                        <div class="separator separator-dashed mb-7"></div>
                        <div id="haklariniz" class="mb-7">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-2 p-2 d-inline-flex"><i class="bi bi-person-check fs-4 text-primary"></i></div>
                                <h5 class="text-white fw-bold mb-0">6. KVKK Kapsamındaki Haklarınız</h5>
                            </div>
                            <p class="text-muted lh-lg mb-4">KVKK'nın 11. maddesi uyarınca aşağıdaki haklara sahipsiniz:</p>
                            <div class="row g-3">
                                <div v-for="right in rights" :key="right" class="col-sm-6">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check-circle text-primary mt-1 flex-shrink-0"></i>
                                        <span class="text-muted fs-7">{{ right }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="separator separator-dashed mb-7"></div>
                        <div id="iletisim">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-2 p-2 d-inline-flex"><i class="bi bi-envelope fs-4 text-primary"></i></div>
                                <h5 class="text-white fw-bold mb-0">7. İletişim</h5>
                            </div>
                            <p class="text-muted lh-lg mb-4">Gizlilik politikamız veya veri talepleriniz için:</p>
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-3"><i class="bi bi-envelope text-primary fs-5"></i><span class="text-muted">{{ settings.contact_email }}</span></div>
                                <div class="d-flex align-items-center gap-3"><i class="bi bi-globe text-primary fs-5"></i><span class="text-muted">{{ settings.site_url }}</span></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
