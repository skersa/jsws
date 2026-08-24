<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuctionCard from '@/Components/AuctionCard.vue';
defineProps({ liveAuctions: Array, now: String });
</script>

<template>
    <Head title="Canlı Açık Artırma" />
    <div class="container py-4">
        <div class="au-page-head">
            <div class="idx-section-title"><i class="bi bi-broadcast"></i> Canlı Açık Artırma</div>
            <div class="idx-section-date">{{ now }} itibarıyla</div>
        </div>

        <div class="row g-3">
            <div v-for="auction in liveAuctions" :key="auction.id" class="col-xl-3 col-lg-4 col-md-6">
                <AuctionCard :auction="auction" />
            </div>
            <div v-if="!liveAuctions.length" class="idx-empty">
                <i class="bi bi-broadcast"></i>
                <p>Şu an canlı yayında olan bir açık artırma yok.</p>
            </div>
        </div>
    </div>
</template>
