<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FaqItem from '@/Components/FaqItem.vue';

const props = defineProps({ ticket: Object });

const replyBody = ref('');
const replyError = ref('');
const sending = ref(false);

function csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }

function sendReply() {
    if (!replyBody.value.trim()) { replyError.value = 'Yanıt boş olamaz.'; return; }
    replyError.value = '';
    sending.value = true;
    fetch(props.ticket.reply_url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ body: replyBody.value }),
    }).then(r => r.json().catch(() => ({}))).then(() => {
        replyBody.value = '';
        router.reload({ only: ['ticket'] });
    }).catch(() => { replyError.value = 'Gönderilemedi, tekrar deneyin.'; })
      .finally(() => { sending.value = false; });
}

function closeTicket() {
    if (confirm('Talebi kapatmak istediğinize emin misiniz?')) {
        router.post(props.ticket.close_url);
    }
}

const faqs = [
    ['Talebime ne zaman yanıt gelecek?', 'Yüksek öncelikli talepler 2-4 saat içinde, diğerleri 1 iş günü içinde yanıtlanır. Yanıt geldiğinde e-posta ile bilgilendirilirsiniz.'],
    ['Yanıtımı göremiyorum, ne yapmalıyım?', 'Sayfayı yenileyin. Sorun devam ederse spam/junk klasörünüzü kontrol edin. Hesap e-postanızın doğru olduğundan emin olun.'],
    ['Talebimi yanlış kategoriye mi açtım?', 'Yanlış kategori yanıt süresini yavaşlatabilir. Talebi kapatıp doğru kategori ile yeni bir talep açmanızı öneririz.'],
    ['Ödeme sorunum için ne yapmalıyım?', '"Ödeme / Fatura" kategorisinde yüksek öncelikli bir talep oluşturun. Fatura veya işlem numaranızı açıklamaya eklemeniz süreci hızlandırır.'],
];
</script>

<template>
    <Head :title="`Talep #${ticket.id}`" />
    <div class="container py-4 pf-narrow-lg">
        <div class="admin-toolbar mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="toolbar-title">{{ ticket.subject }}</div>
                    <nav><ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><Link :href="route('index')" class="pf-breadcrumb-link">Ana Sayfa</Link></li>
                        <li class="breadcrumb-item"><Link :href="route('support.index')" class="pf-breadcrumb-link">Destek</Link></li>
                        <li class="breadcrumb-item active">#{{ ticket.id }}</li>
                    </ol></nav>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="a-badge" :class="ticket.priority_badge">{{ ticket.priority_label }}</span>
                    <span class="a-badge" :class="ticket.status_badge">{{ ticket.status_label }}</span>
                </div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-head">
                <div class="admin-card-title"><i class="bi bi-chat-dots"></i> Konuşma</div>
                <span class="a-badge info">{{ ticket.messages.length }} mesaj</span>
            </div>
            <div class="p-4">
                <div v-for="msg in ticket.messages" :key="msg.id" class="msg-bubble" :class="{ admin: msg.is_admin }">
                    <img class="msg-avatar" :src="msg.avatar" :alt="msg.author">
                    <div class="msg-body">
                        <div class="msg-text">{{ msg.body }}</div>
                        <div class="msg-meta">{{ msg.author }} · {{ msg.time }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="ticket.is_open" class="admin-card mb-3">
            <div class="admin-card-head"><div class="admin-card-title"><i class="bi bi-reply"></i> Yanıtla</div></div>
            <div class="p-4">
                <form @submit.prevent="sendReply">
                    <textarea v-model="replyBody" class="pf-input mb-3" rows="5" placeholder="Yanıtınızı yazın..." maxlength="3000" data-testid="support-reply-body"></textarea>
                    <div v-if="replyError" class="pf-error mb-2">{{ replyError }}</div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2" :disabled="sending" data-testid="support-reply-submit">
                            <i class="bi bi-send"></i> Gönder
                        </button>
                        <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2" @click="closeTicket" data-testid="support-close-btn">
                            <i class="bi bi-x-circle"></i> Talebi Kapat
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div v-else class="alert-au danger d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-lock"></i>
            Bu talep kapatılmış. Yeni sorun için
            <Link :href="route('support.create')" class="fw-bold">yeni talep açın</Link>.
        </div>

        <div class="admin-card">
            <div class="admin-card-head"><div class="admin-card-title"><i class="bi bi-book"></i> Yardım Makaleleri</div></div>
            <div class="p-3">
                <FaqItem v-for="(f, i) in faqs" :key="i" :question="f[0]" :answer="f[1]" />
            </div>
        </div>
    </div>
</template>
