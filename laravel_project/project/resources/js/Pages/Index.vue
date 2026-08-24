<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuctionCard from '@/Components/AuctionCard.vue';
import StoryBar from '@/Components/StoryBar.vue';

const props = defineProps({
    activeAuctions: Array,
    recentAuctions: Array,
    categories: Array,
    stories: Array,
    canUploadStory: Boolean,
    currentUserId: Number,
    filters: Object,
    now: String,
});

const searchTerm = ref(props.filters.q || '');
const selectedCat = ref(props.filters.category || '');
const selectedStatus = ref(props.filters.status || '');
const currentSort = ref(props.filters.sort || 'bids');

let searchTimer = null;

const visibleAuctions = computed(() => {
    const q = searchTerm.value.toLowerCase().trim();
    if (!q) return props.activeAuctions;
    return props.activeAuctions.filter(a => a.title.toLowerCase().includes(q));
});

function applyFilters() {
    const params = {};
    const q = searchTerm.value.trim();
    if (q) params.q = q;
    if (selectedCat.value) params.category = selectedCat.value;
    if (selectedStatus.value) params.status = selectedStatus.value;
    if (currentSort.value && currentSort.value !== 'bids') params.sort = currentSort.value;
    router.get('/', params, { preserveScroll: true });
}

function setSort(val) {
    currentSort.value = val;
    applyFilters();
}

function onSearchInput() {
    clearTimeout(searchTimer);
    if (!searchTerm.value.trim()) {
        searchTimer = setTimeout(() => applyFilters(), 600);
    }
}

const sectionTitle = computed(() => {
    if (selectedStatus.value === 'ended') return 'Biten Artırmalar';
    if (currentSort.value === 'ending') return 'Bitmek Üzere';
    if (currentSort.value === 'new') return 'Yeni Eklenenler';
    return 'Aktif Artırmalar';
});

const hasFilters = computed(() => !!(props.filters.q || props.filters.category || props.filters.status || props.filters.sort && props.filters.sort !== 'bids'));
</script>

<template>
    <Head title="Ana Sayfa" />
    <div class="container py-4">

        <StoryBar :stories="stories" :can-upload="canUploadStory" :current-user-id="currentUserId" />

        <div class="idx-filterbar">
            <div class="idx-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="search-input" placeholder="Artırma ara..." autocomplete="off"
                       v-model="searchTerm" @input="onSearchInput" data-testid="index-search-input">
            </div>

            <div class="idx-selects-row">
                <select v-if="categories.length" class="idx-select" id="cat-select" v-model="selectedCat" @change="applyFilters">
                    <option value="">Tüm Kategoriler</option>
                    <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">{{ cat.name }} ({{ cat.auctions_count }})</option>
                </select>

                <select class="idx-select" id="status-select" v-model="selectedStatus" @change="applyFilters">
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

            <div class="idx-filter-count">
                <span id="result-count">{{ visibleAuctions.length }}</span> sonuç
            </div>
        </div>

        <div class="idx-section-head">
            <div class="idx-section-title"><i class="bi bi-activity"></i> {{ sectionTitle }}</div>
            <div class="idx-section-date">{{ now }} itibarıyla</div>
        </div>

        <div class="row g-3" id="auction-grid">
            <div v-for="auction in visibleAuctions" :key="auction.id" class="col-xl-3 col-lg-4 col-md-6 auction-item">
                <AuctionCard :auction="auction" />
            </div>

            <div v-if="!activeAuctions.length" class="idx-empty">
                <i class="bi bi-inbox"></i>
                <p>Şu an gösterilecek artırma yok.</p>
            </div>

            <div v-else-if="!visibleAuctions.length" id="no-results" class="idx-noresult-visible">
                <i class="bi bi-search"></i>
                <p>Aramanızla eşleşen artırma bulunamadı.</p>
            </div>
        </div>

        <template v-if="!hasFilters && recentAuctions.length">
            <div class="idx-section-head mt-5">
                <div class="idx-section-title"><i class="bi bi-clock-history"></i> Son Eklenenler</div>
                <Link :href="route('index', { sort: 'new' })" class="idx-see-all">Tümünü Gör <i class="bi bi-arrow-right"></i></Link>
            </div>
            <div class="row g-3 mb-4">
                <div v-for="auction in recentAuctions" :key="auction.id" class="col-xl-3 col-lg-4 col-md-6">
                    <AuctionCard :auction="auction" :show-location="false" />
                </div>
            </div>
        </template>
    </div>
</template>
