<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
defineProps({ items: Object });
</script>

<template>
    <Head title="Favorilerim" />
    <div class="dash-wrap py-4">
        <div class="admin-toolbar dash-hero">
            <div>
                <div class="toolbar-title">Favorilerim</div>
                <div class="dash-hero-sub">Beğenip takibe aldığın ilanlar burada listelenir.</div>
            </div>
        </div>

        <div class="admin-card" data-testid="favorites-card">
            <div v-if="!items.data.length" class="pf-empty">
                <div class="pf-empty-icon"><i class="bi bi-heart"></i></div>
                <div class="pf-empty-title">Favori listen boş</div>
                <div class="pf-empty-sub">Beğendiğin ilanları favorilere ekle.</div>
                <Link :href="route('browse.auctions')" class="btn-admin-pri dash-mt">Müzayedeleri Keşfet</Link>
            </div>
            <template v-else>
                <div class="dash-fav-grid">
                    <Link v-for="w in items.data" :key="w.show_url" class="dash-fav-card" :href="w.show_url">
                        <img :src="w.cover_url" :alt="w.title">
                        <div class="dash-fav-body">
                            <div class="dash-fav-title">{{ w.title }}</div>
                            <div class="dash-fav-price">{{ w.display_price }}</div>
                        </div>
                    </Link>
                </div>
                <Pagination v-if="items.has_pages" :links="items.links" />
            </template>
        </div>
    </div>
</template>
