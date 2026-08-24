<script setup>
/*
 | Hikaye yükleme — public/assets/js/custom/story-upload.js'in SPA-güvenli Vue portu.
 | Aynı DOM class/id'leri (story-upload.css) korunur. Teleport ile body altına render edilir.
 | window.openStoryUpload/closeStoryUpload global fonksiyonlarını kaydeder (StoryBar.vue çağırır).
 | AJAX submit (X-Requested-With) — StoryController@store JSON döner; STORY_DATA güncellenir.
*/
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const open = ref(false);
const hidden = ref(true);
const file = ref(null);
const previewUrl = ref('');
const isVideo = ref(false);
const caption = ref('');
const uploading = ref(false);
const fileEl = ref(null);

const storeUrl = computed(() => (typeof window.route === 'function') ? window.route('stories.store') : '/stories');

function resetForm() {
    file.value = null;
    if (previewUrl.value) { try { URL.revokeObjectURL(previewUrl.value); } catch (e) {} }
    previewUrl.value = '';
    isVideo.value = false;
    caption.value = '';
    uploading.value = false;
    if (fileEl.value) fileEl.value.value = '';
}

function openUpload() {
    hidden.value = false;
    requestAnimationFrame(() => { open.value = true; });
    document.body.style.overflow = 'hidden';
}
function close() {
    open.value = false;
    setTimeout(() => { hidden.value = true; }, 260);
    document.body.style.overflow = '';
    resetForm();
}
function onFile(e) {
    const f = e.target.files[0];
    if (!f) return;
    if (previewUrl.value) { try { URL.revokeObjectURL(previewUrl.value); } catch (err) {} }
    previewUrl.value = URL.createObjectURL(f);
    isVideo.value = f.type.startsWith('video');
    file.value = f;
}
function submit() {
    if (!file.value || uploading.value) return;
    uploading.value = true;
    const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    const fd = new FormData();
    fd.append('media', file.value);
    fd.append('caption', caption.value || '');

    fetch(storeUrl.value, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
        credentials: 'same-origin',
    }).then((res) => {
        if (!res.ok) return res.json().then((err) => { throw new Error(err.message || 'Yükleme başarısız'); });
        return res.json();
    }).then((data) => {
        if (window.ajaxToast) window.ajaxToast('success', data.message || 'Hikayen paylaşıldı!');
        if (data.story && data.user) {
            const uid = data.user.id;
            window.STORY_DATA = window.STORY_DATA || {};
            if (!window.STORY_DATA[uid]) {
                window.STORY_DATA[uid] = { name: data.user.name, avatar: data.user.avatar, isOwner: true, items: [] };
            }
            window.STORY_DATA[uid].items.push({
                id: data.story.id, type: data.story.type, url: data.story.url, caption: data.story.caption,
            });
            const bar = document.querySelector('.story-item[data-story-uid="' + uid + '"]');
            if (bar) {
                const ids = window.STORY_DATA[uid].items.map((i) => i.id);
                bar.dataset.storyIds = JSON.stringify(ids);
            }
        }
        close();
    }).catch((err) => {
        if (window.ajaxToast) window.ajaxToast('error', err.message); else alert(err.message);
    }).finally(() => {
        uploading.value = false;
    });
}

onMounted(() => {
    window.openStoryUpload = openUpload;
    window.closeStoryUpload = close;
});
onBeforeUnmount(() => {
    document.body.style.overflow = '';
    if (previewUrl.value) { try { URL.revokeObjectURL(previewUrl.value); } catch (e) {} }
});
</script>

<template>
    <Teleport to="body">
        <div class="story-upload-overlay" :class="{ open }" id="storyUploadModal" data-testid="story-upload-modal"
             :hidden="hidden" @click.self="close">
            <div class="story-upload-box">
                <div class="su-head">
                    <span>Hikaye Paylaş</span>
                    <button type="button" @click="close" data-testid="story-upload-close"><i class="bi bi-x-lg"></i></button>
                </div>
                <form :action="storeUrl" method="POST" enctype="multipart/form-data" @submit.prevent="submit">
                    <label class="su-drop">
                        <input ref="fileEl" type="file" accept="image/*,video/*" hidden @change="onFile" data-testid="story-file-input">
                        <div v-show="!previewUrl" class="su-ph">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span>Görsel veya video seç</span>
                            <small>JPG, PNG, MP4 · max 20MB</small>
                        </div>
                        <div v-show="previewUrl" class="su-preview">
                            <video v-if="previewUrl && isVideo" :src="previewUrl" muted autoplay loop playsinline></video>
                            <img v-else-if="previewUrl" :src="previewUrl" alt="">
                        </div>
                    </label>
                    <input type="text" v-model="caption" maxlength="150" class="su-caption" placeholder="Açıklama (isteğe bağlı)">
                    <button type="submit" class="su-submit" :disabled="!file || uploading" data-testid="story-upload-submit">
                        <i class="bi" :class="uploading ? 'bi-hourglass-split' : 'bi-send'"></i>
                        {{ uploading ? ' Yükleniyor...' : ' Paylaş' }}
                    </button>
                </form>
            </div>
        </div>
    </Teleport>
</template>
