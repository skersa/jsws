<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuctionCard from '@/Components/AuctionCard.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({ auctions: Object, categories: Array, filters: Object, now: String });

const searchTerm = ref(props.filters.q || '');
const selectedCat = ref(props.filters.category || '');
const selectedStatus = ref(props.filters.status || '');
const currentSort = ref(props.filters.sort || 'bids');
let searchTimer = null;

const visible = computed(() => {
    const q = searchTerm.value.toLowerCase().trim();
    if (!q) return props.auctions.data;
    return props.auctions.data.filter(a => a.title.toLowerCase().includes(q));
});

function applyFilters() {
    const params = {};
    if (searchTerm.value.trim()) params.q = searchTerm.value.trim();
    if (selectedCat.value) params.category = selectedCat.value;
    if (selectedStatus.value) params.status = selectedStatus.value;
    if (currentSort.value && currentSort.value !== 'bids') params.sort = currentSort.value;
    router.get(route('browse.auctions'), params, { preserveScroll: true });
}
function setSort(v) { currentSort.value = v; applyFilters(); }
function onSearchInput() {
    clearTimeout(searchTimer);
    if (!searchTerm.value.trim()) searchTimer = setTimeout(applyFilters, 600);
}
</script>

<template>
    <Head title="Müzayedeler" />
    <div class="container py-4">
        <div class="idx-filterbar">
            <div class="idx-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="search-input" placeholder="Artırma ara..." autocomplete="off"
                       v-model="searchTerm" @input="onSearchInput" data-testid="browse-search-input">
            </div>
            <div class="idx-selects-row">
                <select v-if="categories.length" class="idx-select" v-model="selectedCat" @change="applyFilters">
                    <option value="">Tüm Kategoriler</option>
                    <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">{{ cat.name }} ({{ cat.auctions_count }})</option>
                </select>
                <select class="idx-select" v-model="selectedStatus" @change="applyFilters">
                    <option value="">Tüm Durumlar</option>
                    <option value="active">Aktif</option>
                    <option value="ended">Bitti</option>
                </select>
            </div>
            <div class="idx-filter-divider"></div>
            <div class="idx-sort-btns">
                <button class="idx-sort-btn" :class="{ active: currentSort === 'bids' }" @click="setSort('bids')"><i class="bi bi-fire"></i> Popüler</button>
                <button class="idx-sort-btn" :class="{ active: currentSort === 'ending' }" @click="setSort('ending')"><i class="bi bi-clock"></i> Bitmek Üzere</button>
                <button class="idx-sort-btn" :class="{ active: currentSort === 'new' }" @click="setSort('new')"><i class="bi bi-stars"></i> Yeni</button>
                <button class="idx-sort-btn" :class="{ active: currentSort === 'price' }" @click="setSort('price')"><i class="bi bi-sort-down"></i> Fiyat</button>
            </div>
            <div class="idx-filter-count"><span id="result-count">{{ auctions.total }}</span> sonuç</div>
        </div>

        <div class="idx-section-head">
            <div class="idx-section-title"><i class="bi bi-grid"></i> Müzayedeler</div>
            <div class="idx-section-date">{{ now }} itibarıyla</div>
        </div>

        <div class="row g-3" id="auction-grid">
            <div v-for="auction in visible" :key="auction.id" class="col-xl-3 col-lg-4 col-md-6 auction-item">
                <AuctionCard :auction="auction" />
            </div>
            <div v-if="!visible.length" class="idx-empty">
                <i class="bi bi-inbox"></i>
                <p>Aramanızla eşleşen artırma bulunamadı.</p>
            </div>
        </div>

        <Pagination v-if="auctions.has_pages" :links="auctions.links" />
    </div>
</template>
