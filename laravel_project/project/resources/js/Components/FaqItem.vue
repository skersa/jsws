<script setup>
import { ref } from 'vue';
const open = ref(false);
defineProps({ question: String, answer: String });
</script>

<template>
    <div class="support-faq-item">
        <button class="support-faq-btn" type="button" @click="open = !open">
            <span>{{ question }}</span>
            <i class="bi bi-chevron-down support-faq-icon" :style="{ transform: open ? 'rotate(180deg)' : 'none', transition: 'transform .2s' }"></i>
        </button>
        <div class="support-faq-body" :style="{ display: open ? 'block' : 'none' }">
            <p class="pf-hint mb-0">{{ answer }}</p>
        </div>
    </div>
</template>
