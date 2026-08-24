<script setup>
/*
 | Hikaye görüntüleyici (Instagram/WhatsApp tarzı):
 |  - Üstte segment segment ilerleyen animasyonlu progress bar
 |  - Otomatik ilerleme; son hikayeden sonra bir sonraki KULLANICIYA geçer
 |  - Görsel tam yüklenmeden gösterilmez (yarım render yok) + sonraki görseli ön-yükler
 |  - window.STORY_DATA veri kaynağı, window.STORY_ORDER kullanıcı sırası
 |  - Profil sayfası .story-source DOM'undan da veri okur (fallback)
*/
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { csrfHeaders } from '@/csrf';

const IMG_DURATION = 5000;

const open = ref(false);
const curUser = ref(null);
const curIndex = ref(0);
const ready = ref(false);
const paused = ref(false);
const mediaDuration = ref(IMG_DURATION);
const videoRef = ref(null);
let timer = null;
let remaining = 0;
let timerStart = 0;

const u = computed(() => (curUser.value != null && window.STORY_DATA) ? window.STORY_DATA[curUser.value] : null);
const item = computed(() => (u.value && u.value.items) ? u.value.items[curIndex.value] : null);
const items = computed(() => (u.value && u.value.items) ? u.value.items : []);

/* ── Kullanıcı sırası (kullanıcılar arası geçiş) ── */
function order() {
    const ord = Array.isArray(window.STORY_ORDER) ? window.STORY_ORDER.slice() : [];
    if (curUser.value != null && !ord.includes(curUser.value)) ord.push(curUser.value);
    return ord;
}

/* ── Görülen hikaye halkası (Instagram: soluk) ── */
const SEEN_KEY = 'artirdim_seen_stories';
function getSeen() { try { return new Set(JSON.parse(localStorage.getItem(SEEN_KEY) || '[]')); } catch (e) { return new Set(); } }
function saveSeen(set) { localStorage.setItem(SEEN_KEY, JSON.stringify([...set])); }
function paintRing(el, seen) {
    const ring = el.querySelector('.story-ring');
    if (seen) { el.classList.add('seen'); if (ring && el.dataset.ringSeen) ring.setAttribute('style', el.dataset.ringSeen); }
    else { el.classList.remove('seen'); if (ring && el.dataset.ringUnseen) ring.setAttribute('style', el.dataset.ringUnseen); }
}
function applySeenStates() {
    const seen = getSeen();
    document.querySelectorAll('.story-item[data-story-ids]').forEach((el) => {
        if (el.classList.contains('story-add')) return;
        let ids = [];
        try { ids = JSON.parse(el.dataset.storyIds || '[]'); } catch (e) {}
        const allSeen = ids.length > 0 && ids.every((id) => seen.has(id));
        paintRing(el, allSeen);
    });
}
function markUserSeen(uid) {
    const usr = window.STORY_DATA?.[uid];
    if (!usr || !usr.items) return;
    const seen = getSeen();
    usr.items.forEach((it) => seen.add(it.id));
    saveSeen(seen);
    const el = document.querySelector('.story-item[data-story-uid="' + uid + '"]');
    if (el) paintRing(el, true);
}

/* ── Profil .story-source fallback ── */
function hydrateFromSources(uid) {
    if (window.STORY_DATA && window.STORY_DATA[uid]) return;
    const el = document.querySelector('.story-source[data-user-id="' + uid + '"]');
    if (!el) return;
    try {
        const payload = JSON.parse(el.dataset.storyPayload || 'null');
        if (payload) { window.STORY_DATA = window.STORY_DATA || {}; window.STORY_DATA[uid] = payload; }
    } catch (e) {}
}
function refreshStorySources() {
    document.querySelectorAll('.story-source[data-user-id]').forEach((el) => {
        const uid = el.dataset.userId;
        try {
            const payload = JSON.parse(el.dataset.storyPayload || 'null');
            if (payload) { window.STORY_DATA = window.STORY_DATA || {}; window.STORY_DATA[uid] = payload; }
        } catch (e) {}
    });
}

/* ── Zamanlama & ön-yükleme ── */
function clearTimer() { clearTimeout(timer); timer = null; }
function startTimer() {
    clearTimer();
    paused.value = false;
    if (item.value && item.value.type !== 'video') {
        remaining = mediaDuration.value;
        timerStart = Date.now();
        timer = setTimeout(() => next(), remaining);
    }
}
/* ── Basılı-tut duraklat / bırak devam et (Instagram/WhatsApp) ── */
function pauseTimer() {
    if (paused.value) return;
    paused.value = true;
    if (timer) { clearTimeout(timer); timer = null; remaining -= (Date.now() - timerStart); }
    const v = videoRef.value;
    if (v && item.value && item.value.type === 'video') { try { v.pause(); } catch (e) {} }
}
function resumeTimer() {
    if (!paused.value) return;
    paused.value = false;
    if (item.value && item.value.type !== 'video' && ready.value) {
        if (remaining <= 0) remaining = 400;
        timerStart = Date.now();
        timer = setTimeout(() => next(), remaining);
    }
    const v = videoRef.value;
    if (v && item.value && item.value.type === 'video') { try { v.play(); } catch (e) {} }
}
function onHoldStart() { if (ready.value) pauseTimer(); }
function onHoldEnd() { resumeTimer(); }
function preloadNext() {
    const usr = u.value; if (!usr) return;
    const nx = usr.items[curIndex.value + 1];
    if (nx && nx.type !== 'video') { const im = new Image(); im.src = nx.url; }
}
function onMediaReady() {
    ready.value = true;
    mediaDuration.value = IMG_DURATION;
    if (open.value) startTimer();
    preloadNext();
}
function onVideoReady(e) {
    ready.value = true;
    const d = e?.target?.duration;
    mediaDuration.value = (d && isFinite(d)) ? d * 1000 : IMG_DURATION;
    clearTimer(); // video kendi 'ended' olayıyla ilerler
    preloadNext();
}

/* ── Aç/kapat & gezinme ── */
function gotoUser(uid, index) {
    hydrateFromSources(uid);
    const usr = window.STORY_DATA?.[uid];
    if (!usr || !usr.items || !usr.items.length) return false;
    ready.value = false;
    curUser.value = uid;
    curIndex.value = Math.min(Math.max(index, 0), usr.items.length - 1);
    markUserSeen(uid);
    return true;
}
function openViewer(uid) {
    hydrateFromSources(uid);
    const usr = window.STORY_DATA?.[uid];
    if (!usr || !usr.items || !usr.items.length) return;
    open.value = true;
    document.body.style.overflow = 'hidden';
    gotoUser(uid, 0);
}
function close() {
    open.value = false;
    paused.value = false;
    document.body.style.overflow = '';
    clearTimer();
}
function next() {
    if (!open.value) return;
    const usr = u.value; if (!usr) return;
    if (curIndex.value < usr.items.length - 1) { ready.value = false; curIndex.value++; return; }
    // kullanıcının son hikayesi → sonraki kullanıcı
    const ord = order();
    const pos = ord.indexOf(curUser.value);
    for (let i = pos + 1; i < ord.length; i++) {
        if (gotoUser(ord[i], 0)) return;
    }
    close();
}
function prev() {
    if (curIndex.value > 0) { ready.value = false; curIndex.value--; return; }
    const ord = order();
    const pos = ord.indexOf(curUser.value);
    for (let i = pos - 1; i >= 0; i--) {
        const usr = window.STORY_DATA?.[ord[i]];
        if (usr && usr.items && usr.items.length && gotoUser(ord[i], usr.items.length - 1)) return;
    }
}

function goProfile() {
    const url = u.value && u.value.profile_url;
    if (!url) return;
    close();
    router.visit(url);
}

function deleteCurrent() {
    const usr = u.value; if (!usr) return;
    const it = usr.items[curIndex.value];
    if (!it || !it.id) return;
    pauseTimer();
    const doDelete = () => {
        const fd = new FormData();
        fd.append('_method', 'DELETE');
        return fetch('/stories/' + it.id, {
            method: 'POST', body: fd,
            headers: csrfHeaders(),
            credentials: 'same-origin',
        }).then((res) => {
            if (!res.ok) return res.json().then((e) => { throw new Error(e.message || 'Silme başarısız'); });
            return res.json().catch(() => ({}));
        }).then(() => {
            usr.items.splice(curIndex.value, 1);
            if (usr.items.length === 0) {
                const bar = document.querySelector('.story-item[data-story-uid="' + curUser.value + '"]');
                if (bar) bar.remove();
                if (window.STORY_DATA) delete window.STORY_DATA[curUser.value];
                close();
            } else if (curIndex.value >= usr.items.length) {
                curIndex.value = usr.items.length - 1;
            }
            if (window.ajaxToast) window.ajaxToast('success', 'Hikaye silindi');
        }).catch((err) => {
            if (window.ajaxToast) window.ajaxToast('error', err.message); else alert(err.message);
        });
    };
    if (window.Swal) {
        window.Swal.fire({
            title: 'Hikayeyi silmek istediğine emin misin?', icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Evet, sil', cancelButtonText: 'Vazgeç', reverseButtons: true,
            confirmButtonColor: '#ef4444', heightAuto: false,
            didOpen: () => document.body.classList.remove('swal2-height-auto'),
        }).then((r) => { if (r.isConfirmed) doDelete(); else resumeTimer(); });
    } else if (confirm('Bu hikayeyi silmek istediğine emin misin?')) doDelete();
    else resumeTimer();
}

function onKey(e) {
    if (!open.value) return;
    if (e.key === 'ArrowRight') next();
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'Escape') close();
}

/* item değişince zamanlayıcıyı sıfırla (yükleme bitince yeniden başlar) */
watch(item, () => { clearTimer(); });

onMounted(() => {
    window.openStoryViewer = openViewer;
    window.closeStoryViewer = close;
    window.storyNext = next;
    window.storyPrev = prev;
    window.deleteCurrentStory = deleteCurrent;
    window.__refreshStorySources = refreshStorySources;
    window.addEventListener('keydown', onKey);
    window.addEventListener('pageshow', applySeenStates);
    refreshStorySources();
    applySeenStates();
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    window.removeEventListener('pageshow', applySeenStates);
    clearTimer();
    document.body.style.overflow = '';
});

defineExpose({ applySeenStates });
</script>

<template>
    <Teleport to="body">
        <div class="story-viewer" :class="{ open }" id="storyViewer" data-testid="story-viewer">
            <div class="story-viewer-backdrop" @click="close"></div>
            <div class="story-viewer-stage">
                <div class="story-progress" id="storyProgress">
                    <span v-for="(it, i) in items" :key="i" class="sp-seg">
                        <i v-if="i < curIndex" class="sp-fill sp-fill-done"></i>
                        <i v-else-if="i === curIndex && ready" class="sp-fill sp-fill-active"
                           :key="curUser + '-' + curIndex"
                           :style="{ animationDuration: mediaDuration + 'ms', animationPlayState: paused ? 'paused' : 'running' }"></i>
                    </span>
                </div>
                <div class="story-viewer-head">
                    <div class="story-viewer-user" :style="{ cursor: (u && u.profile_url) ? 'pointer' : 'default' }"
                         @click="goProfile" data-testid="story-user-profile-link">
                        <img :src="u ? u.avatar : ''" alt="">
                        <span>{{ u ? u.name : '' }}</span>
                    </div>
                    <div class="story-viewer-actions">
                        <button v-if="u && u.isOwner" class="story-viewer-del" @click="deleteCurrent"
                                data-testid="story-delete" title="Hikayeyi sil"><i class="bi bi-trash"></i></button>
                        <button class="story-viewer-close" @click="close" data-testid="story-close"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
                <div class="story-viewer-media" id="svMedia"
                     @pointerdown="onHoldStart" @pointerup="onHoldEnd" @pointerleave="onHoldEnd" @pointercancel="onHoldEnd">
                    <div v-show="!ready" class="story-media-skeleton" data-testid="story-skeleton">
                        <div class="story-media-spinner"></div>
                    </div>
                    <template v-if="item">
                        <video v-if="item.type === 'video'" ref="videoRef" :key="'v-' + curUser + '-' + curIndex" :src="item.url"
                               autoplay playsinline controls v-show="ready"
                               @loadeddata="onVideoReady" @ended="next" @error="onVideoReady"></video>
                        <img v-else :key="'i-' + curUser + '-' + curIndex" :src="item.url" alt="" v-show="ready"
                             @load="onMediaReady" @error="onMediaReady">
                    </template>
                </div>
                <div v-show="paused" class="story-paused-hint"><i class="bi bi-pause-fill"></i></div>
                <div class="story-viewer-caption">{{ item ? (item.caption || '') : '' }}</div>
                <button class="story-nav story-prev" @click="prev"><i class="bi bi-chevron-left"></i></button>
                <button class="story-nav story-next" @click="next"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
    </Teleport>
</template>
