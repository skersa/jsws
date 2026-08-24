<script setup>
import { onMounted } from 'vue';

const props = defineProps({
    stories: { type: Array, default: () => [] },
    canUpload: { type: Boolean, default: false },
    currentUserId: { type: Number, default: null },
});

function openViewer(uid) {
    if (typeof window.openStoryViewer === 'function') window.openStoryViewer(uid);
}
function openUpload() {
    if (typeof window.openStoryUpload === 'function') window.openStoryUpload();
}

onMounted(() => {
    // Blade'deki story-data.js yerine: payload'ları doğrudan global objeye yükle
    window.STORY_DATA = window.STORY_DATA || {};
    props.stories.forEach((s) => { window.STORY_DATA[s.id] = s.payload; });
    // Kullanıcı sırası (kullanıcılar arası geçiş için)
    window.STORY_ORDER = props.stories.map((s) => s.id);
    // story-viewer.js seen-state boyamasını yeniden tetikle (Vue DOM'u sonradan render eder)
    setTimeout(() => window.dispatchEvent(new Event('pageshow')), 50);
});
</script>

<template>
    <div v-if="stories.length || canUpload" class="story-bar" data-testid="story-bar">
        <div class="story-strip">
            <div v-if="canUpload" class="story-item story-add" data-testid="story-add" @click="openUpload">
                <div class="story-ring story-ring-add">
                    <div class="story-add-inner"><i class="bi bi-plus-lg"></i></div>
                </div>
                <span class="story-name">Hikaye Ekle</span>
            </div>

            <div v-for="su in stories" :key="su.id" class="story-item"
                 :data-testid="`story-user-${su.id}`"
                 :data-story-uid="su.id"
                 :data-story-ids="JSON.stringify(su.story_ids)"
                 :data-ring-unseen="su.ring_unseen"
                 :data-ring-seen="su.ring_seen"
                 @click="openViewer(su.id)">
                <div class="story-ring" :style="su.ring_style">
                    <img :src="su.avatar" :alt="su.name">
                </div>
                <span class="story-name">{{ su.id === currentUserId ? 'Hikayen' : su.name_short }}</span>
            </div>
        </div>
    </div>
</template>
