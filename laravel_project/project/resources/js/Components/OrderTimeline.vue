<script setup>
defineProps({ events: Array });
</script>

<template>
    <div class="ord-timeline" data-testid="order-timeline">
        <div v-for="ev in events" :key="ev.id" class="ord-tl-item">
            <div class="ord-tl-icon" :style="{ '--c': ev.color }">
                <i class="bi" :class="ev.icon"></i>
            </div>
            <div class="ord-tl-body">
                <div class="ord-tl-title">{{ ev.title }}</div>
                <div v-if="ev.description" class="ord-tl-desc">{{ ev.description }}</div>
                <div class="ord-tl-time">
                    {{ ev.time }}<template v-if="ev.actor"> • {{ ev.actor }}</template>
                </div>
            </div>
        </div>
        <div v-if="!events.length" class="pf-text-muted-sm">Henüz kayıt yok.</div>
    </div>
</template>
