<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
defineProps({ notifications: Object });
import Pagination from '@/Components/Pagination.vue';

function readAll() {
    fetch(route('notifications.readAll'), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
    }).then(() => router.reload());
}
</script>

<template>
    <Head title="Bildirimler" />
    <div class="container py-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 class="text-muted">Bildirimler</h5>
            <button v-if="notifications.data.length" type="button" class="pf-btn-reset" style="font-size:12px;" @click="readAll" data-testid="notif-read-all">
                <i class="bi bi-check2-all me-1"></i> Tümünü okundu say
            </button>
        </div>

        <div v-if="!notifications.data.length" class="text-center text-muted py-10">
            <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-50"></i>
            Henüz bildiriminiz yok.
        </div>

        <template v-else>
            <div class="d-flex flex-column gap-2">
                <a v-for="n in notifications.data" :key="n.id" :href="n.link" class="notif-card" :class="{ unread: n.unread }">
                    <div class="notif-avatar-wrap">
                        <img v-if="n.avatar_img" :src="n.avatar_img" class="notif-avatar-img" alt="">
                        <div v-else-if="n.avatar_char" class="notif-avatar-letter" style="background:#155eef;">{{ n.avatar_char }}</div>
                        <div v-else class="notif-avatar-letter" :style="{ background: n.icon_bg, color: n.color }"><i class="bi" :class="n.icon"></i></div>
                        <div class="notif-type-badge" :style="{ background: n.color }"><i class="bi" :class="n.icon"></i></div>
                    </div>
                    <div class="notif-body">
                        <span class="notif-text" :class="{ 'fw-semibold': n.unread }">{{ n.message }}</span>
                        <span v-if="n.reason" class="notif-reason">{{ n.reason }}</span>
                        <span class="notif-time">{{ n.time }}</span>
                    </div>
                    <div v-if="n.unread" class="notif-unread-dot"></div>
                </a>
            </div>
            <Pagination v-if="notifications.has_pages" :links="notifications.links" />
        </template>
    </div>
</template>
