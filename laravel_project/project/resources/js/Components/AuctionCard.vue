<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useClock, formatCountdown } from '@/useClock';

const props = defineProps({
    auction: { type: Object, required: true },
    showLocation: { type: Boolean, default: true },
});

const now = useClock();
const timer = computed(() => props.auction.ends_at
    ? formatCountdown(props.auction.ends_at, now.value)
    : { text: props.auction.time_left, critical: false });

function onImgLoad(e) {
    e.target.classList.add('loaded');
    e.target.closest('.idx-card-img')?.classList.add('img-ready');
}
</script>

<template>
    <Link :href="auction.show_url" class="idx-card">
        <div class="idx-card-img">
            <img :src="auction.cover_url" :alt="auction.title" loading="eager" @load="onImgLoad" @error="onImgLoad">
            <div v-if="auction.is_active" class="idx-live-badge"><span class="dot"></span> CANLI</div>
            <div v-else class="idx-ended-badge">BİTTİ</div>
            <div class="idx-price-overlay">{{ auction.display_price }}</div>
        </div>
        <div class="idx-card-body">
            <div class="idx-card-title">{{ auction.title }}</div>
            <div class="idx-card-meta">
                <span v-if="auction.category_name"><i class="bi bi-tag"></i>{{ auction.category_name }}</span>
                <span><i class="bi bi-chat-square"></i>{{ auction.bid_count }} teklif</span>
                <span v-if="showLocation && auction.location"><i class="bi bi-geo-alt"></i>{{ auction.location }}</span>
            </div>
            <div class="idx-card-bottom">
                <div>
                    <div class="idx-bid-lbl">Güncel Teklif</div>
                    <div class="idx-bid-val">{{ auction.display_price }}</div>
                </div>
                <div>
                    <div class="idx-timer-lbl">Kalan</div>
                    <div class="idx-timer-val" :class="{ critical: timer.critical }">{{ timer.text }}</div>
                </div>
            </div>
        </div>
    </Link>
</template>
