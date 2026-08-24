<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';

const props = defineProps({ ticket: Object });
const page = usePage();
const flash = computed(() => page.props.flash || {});

const messages = ref([...props.ticket.messages]);
const msgCount = computed(() => messages.value.length);
const status = ref(props.ticket.status);

const body = ref('');
const replyError = ref('');
const sending = ref(false);
const msgListRef = ref(null);

function avatarSrc(m) {
    return m.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(m.user || '') + '&size=34&background=155eef&color=fff';
}

function changeStatus() {
    router.patch(props.ticket.status_url, { status: status.value }, { preserveScroll: true });
}

function submitReply() {
    const text = body.value.trim();
    if (!text) return;
    replyError.value = '';
    sending.value = true;
    const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    fetch(props.ticket.reply_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify({ body: text }),
    })
        .then((res) => { if (!res.ok) return res.json().then((e) => { throw e; }); return res.json(); })
        .then((res) => {
            messages.value.push({
                id: res.message.id, body: res.message.body, is_admin: true,
                user: res.message.user, time: res.message.time, avatar: res.message.avatar,
            });
            body.value = '';
            nextTick(() => { const el = msgListRef.value; if (el) el.scrollTop = el.scrollHeight; });
        })
        .catch((e) => {
            replyError.value = (e && e.errors && e.errors.body && e.errors.body[0]) || 'Bir hata oluştu, tekrar deneyin.';
        })
        .finally(() => { sending.value = false; });
}
</script>

<template>
    <Head :title="`Talep #${ticket.id}`" />
    <div class="container-fluid py-3 pf-narrow-xl">
        <div class="admin-toolbar mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="toolbar-title">Talep #{{ ticket.id }} — {{ ticket.subject_short }}</div>
                    <nav><ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><Link :href="ticket.index_url" class="pf-breadcrumb-link">Destek</Link></li>
                        <li class="breadcrumb-item active">#{{ ticket.id }}</li>
                    </ol></nav>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="a-badge" :class="ticket.priority_badge">{{ ticket.priority_label }}</span>
                    <span class="a-badge" :class="ticket.status_badge">{{ ticket.status_label }}</span>
                    <select v-model="status" @change="changeStatus" class="admin-filter-select" data-testid="support-status-select">
                        <option value="open">Açık</option>
                        <option value="in_progress">İşlemde</option>
                        <option value="closed">Kapalı</option>
                    </select>
                </div>
            </div>
        </div>

        <div v-if="flash.success" class="alert-au success mb-3">{{ flash.success }}</div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="admin-card mb-3">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-chat-dots"></i> Konuşma</div>
                        <span class="a-badge info" id="msg-count">{{ msgCount }} mesaj</span>
                    </div>
                    <div class="p-4" id="msg-list" ref="msgListRef">
                        <div v-for="m in messages" :key="m.id" class="msg-bubble" :class="{ admin: m.is_admin }">
                            <img class="msg-avatar" :src="avatarSrc(m)" :alt="m.user">
                            <div class="msg-body">
                                <div class="msg-text">{{ m.body }}</div>
                                <div class="msg-meta">
                                    {{ m.is_admin ? '🛡 Destek Ekibi (' + m.user + ')' : m.user }} · {{ m.time }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!ticket.is_closed" class="admin-card">
                    <div class="admin-card-head">
                        <div class="admin-card-title"><i class="bi bi-reply"></i> Yanıtla</div>
                    </div>
                    <div class="p-4">
                        <form @submit.prevent="submitReply">
                            <textarea v-model="body" id="reply-body" class="pf-input mb-3" rows="6"
                                      placeholder="Kullanıcıya yanıtınızı yazın..." maxlength="3000" data-testid="reply-body"></textarea>
                            <div v-if="replyError" class="pf-error mb-2">{{ replyError }}</div>
                            <button type="submit" class="btn-admin-pri" :disabled="sending" data-testid="reply-btn">
                                <i class="bi" :class="sending ? 'bi-hourglass-split' : 'bi-send'"></i> {{ sending ? 'Gönderiliyor...' : 'Yanıt Gönder' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="admin-card mb-3">
                    <div class="admin-card-head"><div class="admin-card-title"><i class="bi bi-person"></i> Kullanıcı</div></div>
                    <div class="p-1">
                        <div class="admin-info-row px-3">
                            <div class="admin-info-icon"><i class="bi bi-person"></i></div>
                            <div><div class="admin-info-lbl">Ad Soyad</div><div class="admin-info-val">{{ ticket.user.name }}</div></div>
                        </div>
                        <div class="admin-info-row px-3">
                            <div class="admin-info-icon"><i class="bi bi-envelope"></i></div>
                            <div><div class="admin-info-lbl">E-posta</div><div class="admin-info-val">{{ ticket.user.email }}</div></div>
                        </div>
                        <div class="admin-info-row px-3">
                            <div class="admin-info-icon"><i class="bi bi-calendar3"></i></div>
                            <div><div class="admin-info-lbl">Üyelik</div><div class="admin-info-val">{{ ticket.user.created_at }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-head"><div class="admin-card-title"><i class="bi bi-info-circle"></i> Talep Detayı</div></div>
                    <div class="p-1">
                        <div class="admin-info-row px-3">
                            <div class="admin-info-icon"><i class="bi bi-hash"></i></div>
                            <div><div class="admin-info-lbl">Talep No</div><div class="admin-info-val">#{{ ticket.id }}</div></div>
                        </div>
                        <div class="admin-info-row px-3">
                            <div class="admin-info-icon"><i class="bi bi-tag"></i></div>
                            <div><div class="admin-info-lbl">Kategori</div><div class="admin-info-val">{{ ticket.category }}</div></div>
                        </div>
                        <div class="admin-info-row px-3">
                            <div class="admin-info-icon"><i class="bi bi-flag"></i></div>
                            <div><div class="admin-info-lbl">Öncelik</div><div class="admin-info-val"><span class="a-badge" :class="ticket.priority_badge">{{ ticket.priority_label }}</span></div></div>
                        </div>
                        <div class="admin-info-row px-3">
                            <div class="admin-info-icon"><i class="bi bi-calendar-plus"></i></div>
                            <div><div class="admin-info-lbl">Açıldı</div><div class="admin-info-val">{{ ticket.created_at }}</div></div>
                        </div>
                        <div class="admin-info-row px-3">
                            <div class="admin-info-icon"><i class="bi bi-clock-history"></i></div>
                            <div><div class="admin-info-lbl">Son Güncelleme</div><div class="admin-info-val">{{ ticket.updated_human }}</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
