<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import FaqItem from '@/Components/FaqItem.vue';

defineProps({ tickets: Object });
const page = usePage();
const flash = computed(() => page.props.flash || {});

const faqs = [
    ['Nasıl yeni bir destek talebi oluştururum?', '"Yeni Talep" butonuna tıklayarak konu, kategori ve açıklama alanlarını doldurup gönderebilirsiniz. Ekibimiz en kısa sürede yanıt verecektir.'],
    ['Talebimin önceliğini nasıl belirlemeliyim?', 'Ödeme ve fatura sorunları için Yüksek, teknik sorunlar için Orta, genel sorularınız için Düşük öncelik seçebilirsiniz.'],
    ['Kapalı bir talebi yeniden açabilir miyim?', 'Kapatılmış bir talebi yeniden açmak mümkün değildir. Aynı konuda yardıma ihtiyaç duyarsanız lütfen yeni bir talep oluşturun.'],
    ['Yanıt ne kadar sürede gelir?', 'Yüksek öncelikli talepler 2-4 saat içinde, diğer talepler ise 1 iş günü içinde yanıtlanmaktadır.'],
];
</script>

<template>
    <Head title="Destek Taleplerim" />
    <div class="container py-4">
        <div class="admin-toolbar mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="toolbar-title">Destek Taleplerim</div>
                    <nav><ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><Link :href="route('index')" class="pf-breadcrumb-link">Ana Sayfa</Link></li>
                        <li class="breadcrumb-item active">Destek</li>
                    </ol></nav>
                </div>
                <Link :href="route('support.create')" class="btn btn-primary d-flex align-items-center gap-2" data-testid="support-new-btn">
                    <i class="bi bi-plus-lg"></i> Yeni Talep
                </Link>
            </div>
        </div>

        <div v-if="flash.success" class="alert-au success mb-3">{{ flash.success }}</div>

        <div class="admin-card mb-3">
            <div class="admin-card-head"><div class="admin-card-title"><i class="bi bi-lightbulb"></i> Sık Sorulan Sorular</div></div>
            <div class="p-3">
                <FaqItem v-for="(f, i) in faqs" :key="i" :question="f[0]" :answer="f[1]" />
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-head">
                <div class="admin-card-title"><i class="bi bi-headset"></i> Talepler</div>
                <span class="a-badge info">{{ tickets.total }} talep</span>
            </div>

            <div v-if="!tickets.data.length" class="fl-empty">
                <div class="fl-empty-icon"><i class="bi bi-inbox"></i></div>
                <div class="fl-empty-title">Henüz destek talebiniz yok</div>
                <div class="fl-empty-sub">Bir sorunla karşılaştığınızda yeni talep oluşturabilirsiniz.</div>
            </div>
            <template v-else>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead><tr><th>#</th><th>Konu</th><th>Kategori</th><th>Öncelik</th><th>Durum</th><th>Son Güncelleme</th><th></th></tr></thead>
                        <tbody>
                            <tr v-for="t in tickets.data" :key="t.id">
                                <td class="text-muted fs-xs">#{{ t.id }}</td>
                                <td>
                                    <div class="admin-info-val">{{ t.subject }}</div>
                                    <div v-if="t.last_message" class="pf-hint mt-0">{{ t.last_message }}</div>
                                </td>
                                <td class="text-muted">{{ t.category }}</td>
                                <td><span class="a-badge" :class="t.priority_badge">{{ t.priority_label }}</span></td>
                                <td><span class="a-badge" :class="t.status_badge">{{ t.status_label }}</span></td>
                                <td class="text-muted">{{ t.updated_at }}</td>
                                <td><Link :href="t.show_url" class="btn btn-outline-primary btn-sm">Görüntüle</Link></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination v-if="tickets.has_pages" :links="tickets.links" />
            </template>
        </div>
    </div>
</template>
