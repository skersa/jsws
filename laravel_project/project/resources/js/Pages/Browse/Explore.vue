<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuctionCard from '@/Components/AuctionCard.vue';
defineProps({ categories: Array, featuredAuctions: Array, newAuctions: Array, now: String });
</script>

<template>
    <Head title="Keşfet" />

    <div class="dsc-hero">
        <div class="dsc-hero-inner">
            <div class="dsc-hero-icon"><i class="bi bi-compass"></i></div>
            <h1 class="dsc-hero-title">Keşfet</h1>
            <p>Binlerce müzayede ilanı arasından ilginizi çekeni bulun</p>
            <span class="dsc-hero-chip"><i class="bi bi-clock"></i> {{ now }} itibarıyla</span>
        </div>
    </div>

    <div class="container-xxl py-4 px-3 px-md-4">
        <div class="idx-section-head mb-3">
            <div class="idx-section-title"><i class="bi bi-tags-fill"></i> Kategoriler</div>
        </div>

        <div class="row g-3 mb-5">
            <div v-for="cat in categories" :key="cat.slug" class="col-xl-2 col-lg-3 col-md-4 col-6">
                <Link :href="cat.browse_url" class="idx-card dsc-cat-card">
                    <div class="idx-card-img">
                        <img :src="cat.image_url" :alt="cat.name" loading="eager">
                        <div class="dsc-cat-overlay"></div>
                    </div>
                    <div class="dsc-cat-foot">
                        <div class="dsc-cat-name">{{ cat.name }}</div>
                        <div class="dsc-cat-count"><i class="bi bi-collection"></i> {{ cat.auctions_count }} ilan</div>
                    </div>
                </Link>
            </div>
            <div v-if="!categories.length" class="idx-empty">
                <i class="bi bi-tags"></i>
                <p>Henüz kategori eklenmemiş.</p>
            </div>
        </div>

        <template v-if="featuredAuctions.length">
            <div class="idx-section-head mb-3">
                <div class="idx-section-title"><i class="bi bi-star-fill"></i> Öne Çıkanlar</div>
            </div>
            <div class="row g-3 mb-5">
                <div v-for="auction in featuredAuctions" :key="auction.id" class="col-xl-3 col-lg-4 col-md-6">
                    <AuctionCard :auction="auction" />
                </div>
            </div>
        </template>

        <div class="idx-section-head mb-3">
            <div class="idx-section-title"><i class="bi bi-clock-history"></i> Yeni Eklenenler</div>
            <Link :href="route('browse.auctions', { sort: 'new' })" class="idx-see-all">Tümünü Gör <i class="bi bi-arrow-right"></i></Link>
        </div>

        <div class="row g-3">
            <div v-for="auction in newAuctions" :key="auction.id" class="col-xl-3 col-lg-4 col-md-6">
                <AuctionCard :auction="auction" />
            </div>
            <div v-if="!newAuctions.length" class="idx-empty">
                <i class="bi bi-inbox"></i>
                <p>Henüz ilan eklenmemiş.</p>
            </div>
        </div>
    </div>
</template>
