<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Room, Track } from 'livekit-client';
import { fetchLiveKitToken } from '@/composables/useLiveKit';

const props = defineProps({
    auction: Object,
    bids: Array,
    routes: Object,
});

const page = usePage();
const csrf = () => page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.content || '';

const videoEl = ref(null);
const status = ref('idle');          // idle | connecting | live | error
const errorMsg = ref('');
const camOn = ref(false);
const micOn = ref(false);
const viewers = ref(0);
const bidList = ref([...(props.bids || [])]);

let room = null;
let previewStream = null;

function stopPreview() {
    if (previewStream) { previewStream.getTracks().forEach((t) => t.stop()); previewStream = null; }
    if (videoEl.value) videoEl.value.srcObject = null;
}

async function previewCamera() {
    if (status.value === 'live' || status.value === 'connecting') return;
    errorMsg.value = '';
    if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
        errorMsg.value = 'Kamera önizlemesi için güvenli bağlam (HTTPS) gerekli veya bu sekmede izin engelli. Yeni sekmede aç.';
        return;
    }
    try {
        previewStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        if (videoEl.value) { videoEl.value.srcObject = previewStream; videoEl.value.muted = true; }
        status.value = 'preview';
    } catch (e) {
        errorMsg.value = e.name === 'NotAllowedError'
            ? 'Kamera izni reddedildi. Adres çubuğundaki kamera simgesinden izin ver.'
            : 'Kamera önizlemesi açılamadı.';
    }
}

const copied = ref(false);
async function copyViewerLink() {
    const url = new URL(props.routes.view_public, window.location.origin).href;
    try {
        await navigator.clipboard.writeText(url);
        copied.value = true; setTimeout(() => { copied.value = false; }, 1800);
    } catch (e) {
        window.prompt('İzleyici linki:', url);
    }
}

async function goLive() {
    if (status.value === 'connecting' || status.value === 'live') return;
    errorMsg.value = '';

    // Ön kontrol: güvenli bağlam + medya API'si (önizleme iframe'inde kamera engelli olabilir)
    if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        errorMsg.value = 'Kamera erişimi için güvenli bağlam (HTTPS) gerekli veya bu sekmede izin engelli. Uygulamayı yeni bir sekmede açıp tekrar dene.';
        status.value = 'error';
        return;
    }

    status.value = 'connecting';
    // Önizleme akışı kamerayı tutuyorsa serbest bırak ve cihazın boşalması için kısa bekle
    // (aksi halde LiveKit kamerayı açarken "Could not start video source" alınır)
    stopPreview();
    await new Promise((r) => setTimeout(r, 350));

    try {
        const { server_url, participant_token } = await fetchLiveKitToken({
            auctionSlug: props.auction.slug, role: 'broadcaster', csrf: csrf(),
        });
        room = new Room({ adaptiveStream: true, dynacast: true });
        room.on('participantConnected', () => { viewers.value = Math.max(0, room.numParticipants - 1); });
        room.on('participantDisconnected', () => { viewers.value = Math.max(0, room.numParticipants - 1); });
        // Sadece KALICI kopmada idle'a düş (geçici reconnect'te yayını kapatma)
        room.on('disconnected', () => { status.value = 'idle'; camOn.value = false; micOn.value = false; });

        await room.connect(server_url, participant_token, { autoSubscribe: false });

        // Kamerayı LiveKit üzerinden TEK SEFERDE aç (çift açılış yok)
        let camOk = false, micOk = false, camErr = null;
        try { await room.localParticipant.setCameraEnabled(true); camOk = true; }
        catch (e) { camErr = e; }
        try { await room.localParticipant.setMicrophoneEnabled(true); micOk = true; }
        catch (e) { /* mikrofon opsiyonel */ }

        camOn.value = camOk; micOn.value = micOk;
        if (camOk) attachLocalPreview();

        if (!camOk && !micOk) {
            throw Object.assign(new Error(mapMediaError(camErr)), { handled: true });
        }

        // Backend'e "canlı" bilgisini bildir
        await fetch(props.routes.live_status, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ live: 1 }),
        });

        status.value = 'live';
        viewers.value = Math.max(0, room.numParticipants - 1);
        // Kamera açılamadı ama mikrofon açıldıysa uyar (yayın sesli devam eder)
        if (!camOk) errorMsg.value = 'Kamera açılamadı (' + mapMediaError(camErr) + ') — yayın şimdilik sesli. "Kamera Kapalı"ya basıp tekrar açmayı deneyebilirsin.';
    } catch (e) {
        if (e.code === 'not_configured') {
            errorMsg.value = 'Canlı yayın altyapısı (LiveKit) henüz yapılandırılmadı. Yönetici .env içine LIVEKIT_* anahtarlarını eklemeli.';
        } else {
            errorMsg.value = e.handled ? e.message : (e.message || 'Yayın başlatılamadı.');
        }
        status.value = 'error';
        await stopRoom(false);
    }
}

// Tarayıcı medya hatalarını anlaşılır Türkçe mesaja çevir
function mapMediaError(e) {
    if (!e) return 'Bilinmeyen hata';
    const n = e.name || '';
    if (n === 'NotReadableError' || /could not start video source/i.test(e.message || '')) {
        return 'Kamera başka bir uygulama/sekme tarafından kullanılıyor. Kamerayı kullanan diğer uygulamaları ve sekmeleri (Zoom, Meet, başka yayın vb.) kapatıp tekrar dene.';
    }
    if (n === 'NotAllowedError' || n === 'SecurityError') return 'Kamera/mikrofon izni reddedildi. Adres çubuğundaki kamera simgesinden izin ver.';
    if (n === 'NotFoundError' || n === 'OverconstrainedError') return 'Cihazda kamera/mikrofon bulunamadı.';
    return e.message || n || 'Kamera açılamadı';
}

function attachLocalPreview() {
    if (!room || !videoEl.value) return;
    const pub = room.localParticipant.getTrackPublication(Track.Source.Camera);
    if (pub?.track) pub.track.attach(videoEl.value);
}

async function toggleCam() {
    if (!room) return;
    const next = !camOn.value;
    try {
        await room.localParticipant.setCameraEnabled(next);
        camOn.value = next;
        if (next) nextTick(attachLocalPreview);
        if (next) errorMsg.value = '';
    } catch (e) {
        errorMsg.value = mapMediaError(e);
    }
}
async function toggleMic() {
    if (!room) return;
    const next = !micOn.value;
    try {
        await room.localParticipant.setMicrophoneEnabled(next);
        micOn.value = next;
    } catch (e) {
        errorMsg.value = mapMediaError(e);
    }
}

async function stopRoom(notify = true) {
    try { room?.disconnect(); } catch (e) {}
    room = null; camOn.value = false; micOn.value = false;
    if (notify) {
        await fetch(props.routes.end, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        }).catch(() => {});
    }
    status.value = 'idle';
}

async function endBroadcast() {
    if (!confirm('Yayını sonlandırmak istediğine emin misin?')) return;
    await stopRoom(true);
}

async function sellTo(bid) {
    if (!confirm(`${bid.name} — ${formatPrice(bid.amount)} teklifine satış yapılsın mı?`)) return;
    try {
        const res = await fetch(props.routes.sell, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ bid_id: bid.id }),
        });
        const j = await res.json();
        if (res.ok && j.success) {
            await stopRoom(true);
            alert(`Satış tamam! Kazanan: ${j.winner_name} — Sipariş: ${j.order_number}`);
            router.visit(props.routes.view_public);
        } else {
            alert(j.message || 'Satış yapılamadı.');
        }
    } catch (e) { alert('Satış sırasında hata oluştu.'); }
}

function formatPrice(v) {
    return new Intl.NumberFormat('tr-TR').format(Math.round(v || 0)) + ' ₺';
}

/* ---------------- CHAT (polling) ---------------- */
const chat = reactive({ messages: [], input: '', lastId: 0, error: '' });
const chatBox = ref(null);
let chatTimer = null;

async function pollChat() {
    try {
        const res = await fetch(`${props.routes.chat_poll}?after=${chat.lastId}`, { credentials: 'include' });
        const j = await res.json();
        if (j.messages?.length) {
            chat.messages.push(...j.messages);
            chat.lastId = j.messages[j.messages.length - 1].id;
            // Bellek koruması: en fazla 200 mesaj tut
            if (chat.messages.length > 200) chat.messages.splice(0, chat.messages.length - 200);
            await nextTick();
            if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight;
        }
    } catch (e) { /* sessiz geç */ }
}

async function sendChat() {
    const text = chat.input.trim();
    if (!text) return;
    chat.input = ''; chat.error = '';
    try {
        const res = await fetch(props.routes.chat_store, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ message: text }),
        });
        const j = await res.json();
        if (!res.ok) { chat.error = j.message || 'Mesaj gönderilemedi.'; return; }
        // Kendi mesajımızı hemen göster (poll toOthers olduğu için)
        if (j.id && !chat.messages.some((m) => m.id === j.id)) {
            chat.messages.push(j); chat.lastId = Math.max(chat.lastId, j.id);
            await nextTick(); if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight;
        }
    } catch (e) { chat.error = 'Bağlantı hatası.'; }
}

onMounted(() => {
    try { window.scrollTo(0, 0); } catch (e) {}
    pollChat();
    chatTimer = setInterval(pollChat, 3000);
});

onBeforeUnmount(() => {
    if (chatTimer) clearInterval(chatTimer);
    stopPreview();
    stopRoom(false);
});
</script>

<template>
    <Head :title="`Canlı Yayın — ${auction.title}`" />

    <div class="bc-root" data-testid="seller-broadcast-page">
        <!-- Üst bar -->
        <div class="bc-topbar">
            <div>
                <h1 class="bc-title"><i class="bi bi-broadcast"></i> Canlı Yayın</h1>
                <div class="bc-sub">{{ auction.title }} · #{{ auction.id }}</div>
            </div>
            <div class="bc-top-actions">
                <span v-if="status === 'live'" class="bc-live-badge" data-testid="broadcast-live-badge">
                    <span class="bc-dot"></span> CANLI
                </span>
                <span class="bc-viewers"><i class="bi bi-eye"></i> {{ viewers }}</span>
                <Link :href="routes.view_public" class="bc-ghost-btn"><i class="bi bi-box-arrow-up-right"></i> İzleyici Görünümü</Link>
            </div>
        </div>

        <div class="bc-grid">
            <!-- SOL: Video + kontroller -->
            <div class="bc-main">
                <div class="bc-video-wrap">
                    <video ref="videoEl" class="bc-video" autoplay playsinline muted data-testid="broadcast-video"></video>
                    <div v-if="status !== 'live' && status !== 'preview'" class="bc-video-overlay">
                        <i class="bi bi-camera-video-off"></i>
                        <p v-if="status === 'connecting'">Bağlanıyor…</p>
                        <template v-else>
                            <p class="bc-ov-title">Yayına hazır mısın?</p>
                            <p class="bc-ov-hint">Önce "Kamerayı Önizle" ile kendini kontrol et, sonra "Yayını Başlat"a bas.</p>
                        </template>
                    </div>
                    <span v-if="status === 'preview'" class="bc-preview-tag"><i class="bi bi-eye"></i> Önizleme (yayında değil)</span>
                </div>

                <p v-if="errorMsg" class="bc-error" data-testid="broadcast-error">{{ errorMsg }}</p>

                <div class="bc-controls">
                    <template v-if="status !== 'live'">
                        <button class="bc-btn bc-btn-ghost2" @click="previewCamera"
                                :disabled="status === 'connecting'" data-testid="broadcast-preview">
                            <i class="bi bi-camera-video"></i> Kamerayı Önizle
                        </button>
                        <button class="bc-btn bc-btn-primary" @click="goLive"
                                :disabled="status === 'connecting'" data-testid="broadcast-go-live">
                            <i class="bi bi-broadcast"></i> {{ status === 'connecting' ? 'Bağlanıyor…' : 'Yayını Başlat' }}
                        </button>
                        <button class="bc-btn bc-btn-ghost2" @click="copyViewerLink" data-testid="broadcast-copy-link">
                            <i class="bi" :class="copied ? 'bi-check2' : 'bi-link-45deg'"></i> {{ copied ? 'Kopyalandı!' : 'İzleyici Linki' }}
                        </button>
                    </template>
                    <template v-else>
                        <button class="bc-btn" :class="camOn ? 'bc-btn-on' : 'bc-btn-off'" @click="toggleCam" data-testid="broadcast-toggle-cam">
                            <i class="bi" :class="camOn ? 'bi-camera-video' : 'bi-camera-video-off'"></i>
                            <span>{{ camOn ? 'Kamera Açık' : 'Kamera Kapalı' }}</span>
                        </button>
                        <button class="bc-btn" :class="micOn ? 'bc-btn-on' : 'bc-btn-off'" @click="toggleMic" data-testid="broadcast-toggle-mic">
                            <i class="bi" :class="micOn ? 'bi-mic' : 'bi-mic-mute'"></i>
                            <span>{{ micOn ? 'Mikrofon Açık' : 'Mikrofon Kapalı' }}</span>
                        </button>
                        <button class="bc-btn bc-btn-ghost2" @click="copyViewerLink" data-testid="broadcast-copy-link">
                            <i class="bi" :class="copied ? 'bi-check2' : 'bi-link-45deg'"></i> {{ copied ? 'Kopyalandı!' : 'Link' }}
                        </button>
                        <button class="bc-btn bc-btn-danger" @click="endBroadcast" data-testid="broadcast-end">
                            <i class="bi bi-stop-circle"></i> Yayını Bitir
                        </button>
                    </template>
                </div>

                <!-- Teklifler + satış -->
                <div class="bc-panel">
                    <div class="bc-panel-title"><i class="bi bi-hammer"></i> Teklifler ({{ auction.bid_count }})</div>
                    <div class="bc-bids" data-testid="broadcast-bids">
                        <div v-if="!bidList.length" class="bc-empty">Henüz teklif yok.</div>
                        <div v-for="(b, i) in bidList" :key="b.id" class="bc-bid" :class="{ 'bc-bid-top': i === 0 }">
                            <div class="bc-bid-info">
                                <span class="bc-bid-name">{{ b.name }}</span>
                                <span class="bc-bid-time">{{ b.time }}</span>
                            </div>
                            <div class="bc-bid-right">
                                <span class="bc-bid-amount">{{ formatPrice(b.amount) }}</span>
                                <button class="bc-sell-btn" @click="sellTo(b)" data-testid="broadcast-sell-btn">Sat</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SAĞ: Canlı sohbet -->
            <div class="bc-chat" data-testid="broadcast-chat">
                <div class="bc-chat-head"><i class="bi bi-chat-dots"></i> Canlı Sohbet</div>
                <div ref="chatBox" class="bc-chat-body" data-testid="broadcast-chat-messages">
                    <div v-if="!chat.messages.length" class="bc-empty">Henüz mesaj yok. İlk mesajı sen yaz!</div>
                    <div v-for="m in chat.messages" :key="m.id" class="bc-msg" :class="{ 'bc-msg-seller': m.is_seller }">
                        <span class="bc-msg-user">{{ m.user_name }}<i v-if="m.is_seller" class="bi bi-patch-check-fill"></i>:</span>
                        <span class="bc-msg-text">{{ m.message }}</span>
                    </div>
                </div>
                <form class="bc-chat-form" @submit.prevent="sendChat">
                    <input v-model="chat.input" type="text" maxlength="300" placeholder="Mesaj yaz…"
                           data-testid="broadcast-chat-input" />
                    <button type="submit" data-testid="broadcast-chat-send"><i class="bi bi-send"></i></button>
                </form>
                <div v-if="chat.error" class="bc-chat-error">{{ chat.error }}</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.bc-root { max-width: 1400px; margin: 0 auto; padding: 16px 12px 40px; }
.bc-topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
.bc-title { font-size: 20px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px; }
.bc-sub { font-size: 13px; color: var(--muted, #94a3b8); }
.bc-top-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.bc-live-badge { display: inline-flex; align-items: center; gap: 6px; background: #ef4444; color: #fff; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 999px; }
.bc-dot { width: 8px; height: 8px; border-radius: 50%; background: #fff; animation: bcpulse 1s infinite; }
@keyframes bcpulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }
.bc-viewers { display: inline-flex; align-items: center; gap: 5px; background: rgba(128,128,128,.15); padding: 4px 10px; border-radius: 999px; font-size: 13px; font-weight: 600; }
.bc-ghost-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border, #2a2a3a); color: var(--text, #e5e7eb); font-size: 13px; text-decoration: none; }
.bc-ghost-btn:hover { background: rgba(128,128,128,.12); }

.bc-grid { display: grid; grid-template-columns: 1fr 340px; gap: 16px; align-items: start; }
.bc-main { min-width: 0; }
.bc-video-wrap { position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 14px; overflow: hidden; }
.bc-video { width: 100%; height: 100%; object-fit: cover; background: #000; }
.bc-video-overlay { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #6b7280; }
.bc-video-overlay i { font-size: 44px; }
.bc-error { margin: 10px 0 0; color: #ef4444; font-size: 13px; }

.bc-controls { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
.bc-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 10px; border: none; font-weight: 700; font-size: 14px; cursor: pointer; color: #fff; background: #334155; min-height: 48px; }
.bc-btn:disabled { opacity: .6; cursor: default; }
.bc-btn-primary { background: #155eef; }
.bc-btn-on { background: #16a34a; }
.bc-btn-off { background: #64748b; }
.bc-btn-danger { background: #ef4444; }
.bc-btn-ghost2 { background: transparent; border: 1px solid var(--border, #2a2a3a); color: var(--text, #e5e7eb); }
.bc-btn-ghost2:hover { background: rgba(128,128,128,.12); }
.bc-ov-title { font-size: 16px; font-weight: 700; color: #cbd5e1; margin: 4px 0 0; }
.bc-ov-hint { font-size: 12px; color: #64748b; margin: 2px 0 0; max-width: 320px; text-align: center; }
.bc-preview-tag { position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,.6); color: #fbbf24; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; display: inline-flex; align-items: center; gap: 5px; }

.bc-panel { margin-top: 16px; border: 1px solid var(--border, #2a2a3a); border-radius: 12px; overflow: hidden; }
.bc-panel-title { padding: 12px 14px; font-weight: 700; font-size: 14px; border-bottom: 1px solid var(--border, #2a2a3a); display: flex; align-items: center; gap: 8px; }
.bc-bids { max-height: 280px; overflow-y: auto; }
.bc-bid { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-bottom: 1px solid rgba(128,128,128,.1); }
.bc-bid-top { background: rgba(21,94,239,.08); }
.bc-bid-info { display: flex; flex-direction: column; }
.bc-bid-name { font-weight: 600; font-size: 14px; }
.bc-bid-time { font-size: 11px; color: var(--muted, #94a3b8); }
.bc-bid-right { display: flex; align-items: center; gap: 10px; }
.bc-bid-amount { font-weight: 800; color: #22c55e; }
.bc-sell-btn { background: #155eef; color: #fff; border: none; border-radius: 8px; padding: 6px 14px; font-weight: 700; font-size: 13px; cursor: pointer; min-height: 36px; }
.bc-empty { padding: 18px; text-align: center; color: var(--muted, #94a3b8); font-size: 13px; }

.bc-chat { border: 1px solid var(--border, #2a2a3a); border-radius: 12px; display: flex; flex-direction: column; height: 560px; position: sticky; top: 80px; }
.bc-chat-head { padding: 12px 14px; font-weight: 700; font-size: 14px; border-bottom: 1px solid var(--border, #2a2a3a); display: flex; align-items: center; gap: 8px; }
.bc-chat-body { flex: 1; overflow-y: auto; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; }
.bc-msg { font-size: 13px; line-height: 1.4; word-break: break-word; }
.bc-msg-user { font-weight: 700; margin-right: 4px; }
.bc-msg-seller .bc-msg-user { color: #155eef; }
.bc-msg-user i { color: #155eef; margin-left: 2px; font-size: 11px; }
.bc-chat-form { display: flex; gap: 8px; padding: 10px 12px; border-top: 1px solid var(--border, #2a2a3a); }
.bc-chat-form input { flex: 1; background: rgba(128,128,128,.1); border: 1px solid var(--border, #2a2a3a); border-radius: 8px; padding: 10px 12px; color: var(--text, #e5e7eb); font-size: 14px; min-height: 44px; }
.bc-chat-form button { background: #155eef; color: #fff; border: none; border-radius: 8px; width: 46px; cursor: pointer; }
.bc-chat-error { padding: 0 12px 10px; color: #ef4444; font-size: 12px; }

/* MOBİL */
@media (max-width: 900px) {
    .bc-grid { grid-template-columns: 1fr; }
    .bc-chat { height: 420px; position: static; }
    .bc-btn { flex: 1; justify-content: center; }
}
</style>
