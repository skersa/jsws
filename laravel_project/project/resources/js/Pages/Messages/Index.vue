<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, reactive, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { csrfHeaders } from '@/csrf';

const page = usePage();

const props = defineProps({
    conversations: Array,
    active: Object,
    messages: Array,
    index_url: String,
});

const thread = ref(null);
const input = ref('');
const items = reactive([...(props.messages || [])]);
let pollTimer = null;

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

function lastId() {
    return items.length ? items[items.length - 1].id : 0;
}

function scrollBottom() {
    nextTick(() => {
        if (thread.value) thread.value.scrollTop = thread.value.scrollHeight;
    });
}

function push(m) {
    if (items.some((x) => x.id === m.id)) return;
    items.push(m);
    scrollBottom();
}

function send() {
    const body = input.value.trim();
    if (!body || !props.active) return;
    input.value = '';
    fetch(props.active.store_url, {
        method: 'POST',
        headers: csrfHeaders({ 'Content-Type': 'application/json' }, page.props.csrf_token),
        credentials: 'same-origin',
        body: JSON.stringify({ body }),
    })
        .then((r) => r.json())
        .then((m) => push(m))
        .catch(() => {});
}

function poll() {
    if (!props.active) return;
    fetch(props.active.poll_url + '?after=' + lastId(), { headers: { Accept: 'application/json' } })
        .then((r) => r.json())
        .then((d) => (d.messages || []).forEach(push))
        .catch(() => {});
}

onMounted(() => {
    if (props.active) {
        scrollBottom();
        pollTimer = setInterval(poll, 4000);
    }
});

onBeforeUnmount(() => {
    if (pollTimer) clearInterval(pollTimer);
});
</script>

<template>
    <Head title="Mesajlar" />
    <div class="msg-page py-4">
        <div class="msg-layout au-card">

            <aside class="msg-list" :class="{ 'has-active': active }">
                <div class="msg-list-head">
                    <i class="bi bi-chat-dots"></i> Mesajlar
                </div>
                <div class="msg-list-scroll">
                    <template v-if="conversations.length">
                        <Link
                            v-for="c in conversations"
                            :key="c.id"
                            :href="c.url"
                            class="msg-conv"
                            :class="{ active: c.is_active }"
                            :data-testid="'conversation-item-' + c.id"
                        >
                            <img class="msg-avatar" :src="c.peer_avatar" :alt="c.peer_name">
                            <div class="msg-conv-info">
                                <div class="msg-conv-name">{{ c.peer_name }}</div>
                                <div class="msg-conv-last">{{ c.last_body }}</div>
                            </div>
                            <span v-if="c.unread > 0" class="msg-unread">{{ c.unread }}</span>
                        </Link>
                    </template>
                    <div v-else class="msg-empty-list">
                        <i class="bi bi-inbox"></i>
                        <p>Henüz mesajın yok.</p>
                    </div>
                </div>
            </aside>

            <section class="msg-chat" :class="{ 'is-empty': !active }">
                <template v-if="active">
                    <div class="msg-chat-head">
                        <Link :href="index_url" class="msg-back" data-testid="messages-back"><i class="bi bi-arrow-left"></i></Link>
                        <img class="msg-avatar" :src="active.peer_avatar" :alt="active.peer_name">
                        <div>
                            <Link :href="active.profile_url" class="msg-chat-name">{{ active.peer_name }}</Link>
                            <div class="msg-chat-sub">{{ '@' + active.peer_username }}</div>
                        </div>
                    </div>

                    <div class="msg-thread" ref="thread">
                        <div
                            v-for="m in items"
                            :key="m.id"
                            class="msg-bubble"
                            :class="m.mine ? 'mine' : 'theirs'"
                            :data-mid="m.id"
                        >
                            <div class="msg-bubble-body">{{ m.body }}</div>
                            <div class="msg-bubble-time">{{ m.time }}</div>
                        </div>
                    </div>

                    <form class="msg-compose" data-testid="message-form" @submit.prevent="send">
                        <input
                            type="text"
                            v-model="input"
                            autocomplete="off"
                            placeholder="Bir mesaj yaz..."
                            maxlength="2000"
                            data-testid="message-input"
                            required
                        >
                        <button type="submit" class="msg-send" data-testid="message-send">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </template>

                <div v-else class="msg-placeholder">
                    <div class="msg-placeholder-icon"><i class="bi bi-chat-square-text"></i></div>
                    <div class="msg-placeholder-title">Bir sohbet seç</div>
                    <div class="msg-placeholder-sub">Soldan bir konuşma seçerek mesajlaşmaya başla.</div>
                </div>
            </section>

        </div>
    </div>
</template>
