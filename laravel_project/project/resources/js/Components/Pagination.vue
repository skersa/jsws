<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ links: { type: Array, default: () => [] } });
</script>

<template>
    <div class="mt-4 d-flex justify-content-center">
        <ul class="pagination">
            <li v-for="(link, i) in links" :key="i" class="page-item" :class="{ active: link.active, disabled: !link.url }">
                <Link v-if="link.url" class="page-link" :href="link.url" v-html="link.label" preserve-scroll />
                <span v-else class="page-link" v-html="link.label" />
            </li>
        </ul>
    </div>
</template>
