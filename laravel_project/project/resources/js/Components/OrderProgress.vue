<script setup>
defineProps({ steps: Array, cancelled: Boolean, statusColor: String, statusIcon: String, statusLabel: String });
</script>

<template>
    <div class="ord-progress" :class="{ 'ord-progress-alert': cancelled }" data-testid="order-progress">
        <template v-for="(s, i) in steps" :key="i">
            <div class="ord-step" :class="{ done: s.done, active: s.active && !cancelled }">
                <div class="ord-step-dot">
                    <i v-if="s.done" class="bi bi-check-lg"></i>
                    <span v-else>{{ i + 1 }}</span>
                </div>
                <div class="ord-step-label">{{ s.label }}</div>
            </div>
            <div v-if="i < steps.length - 1" class="ord-step-line" :class="{ done: s.done }"></div>
        </template>
    </div>
    <div v-if="cancelled" class="ord-status-alert" :style="{ '--c': statusColor }">
        <i class="bi" :class="statusIcon"></i>
        <span>{{ statusLabel }}</span>
    </div>
</template>
