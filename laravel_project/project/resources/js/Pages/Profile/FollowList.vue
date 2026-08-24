<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { reactive } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({ fl: Object });
const page = usePage();

// yerel takip durumu (AJAX ile güncellenir)
const state = reactive({});
props.fl.people.forEach((p, i) => { state[i] = { following: p.is_following, busy: false }; });

async function toggleFollow(i, url) {
    if (state[i].busy) return;
    state[i].busy = true;
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': props.fl.csrf,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        if (!data.error) state[i].following = data.following;
    } catch (e) { /* noop */ } finally {
        state[i].busy = false;
    }
}

function isAuth() {
    return !!page.props.auth.user;
}
</script>

<template>
    <Head :title="(fl.type === 'followers' ? fl.user.name + ' — Takipçiler' : fl.user.name + ' — Takip Edilenler')" />

    <div class="fl-root">
        <div class="fl-header">
            <Link :href="fl.urls.back" class="fl-back"><i class="bi bi-arrow-left"></i></Link>
            <div>
                <div class="fl-header-name">{{ fl.user.name }}</div>
                <div class="fl-header-sub">&#64;{{ fl.user.username }}</div>
            </div>
        </div>

        <div class="fl-tabs">
            <Link :href="fl.urls.followers" class="fl-tab" :class="{ active: fl.type === 'followers' }">
                Takipçiler
                <span class="fl-tab-count">{{ fl.follower_count }}</span>
            </Link>
            <Link :href="fl.urls.following" class="fl-tab" :class="{ active: fl.type === 'following' }">
                Takip Edilenler
                <span class="fl-tab-count">{{ fl.following_count }}</span>
            </Link>
        </div>

        <div class="fl-list">
            <template v-if="fl.people.length">
                <div v-for="(person, i) in fl.people" :key="i" class="fl-item">
                    <Link :href="person.profile_url" class="fl-item-left">
                        <div class="fl-avatar">
                            <img :src="person.avatar" :alt="person.name">
                        </div>
                        <div class="fl-info">
                            <div class="fl-name">{{ person.name }}</div>
                            <div class="fl-handle">&#64;{{ person.username }}</div>
                            <div v-if="person.bio" class="fl-bio">{{ person.bio }}</div>
                        </div>
                    </Link>

                    <button v-if="isAuth() && !person.is_self"
                            class="fl-follow-btn" :class="{ following: state[i].following }"
                            :disabled="state[i].busy"
                            @click="toggleFollow(i, person.follow_url)">
                        <template v-if="state[i].following">
                            <i class="bi bi-person-check-fill"></i><span>Takip Ediliyor</span>
                        </template>
                        <template v-else>
                            <i class="bi bi-person-plus"></i><span>Takip Et</span>
                        </template>
                    </button>
                </div>
            </template>
            <div v-else class="fl-empty">
                <div class="fl-empty-icon"><i class="bi bi-people"></i></div>
                <div class="fl-empty-title">
                    {{ fl.type === 'followers' ? 'Henüz takipçi yok' : 'Henüz kimse takip edilmiyor' }}
                </div>
                <div class="fl-empty-sub">
                    {{ fl.type === 'followers' ? 'Bu kullanıcıyı takip eden kimse yok.' : 'Bu kullanıcı henüz kimseyi takip etmiyor.' }}
                </div>
            </div>
        </div>

        <div v-if="fl.has_pages" class="fl-pagination">
            <nav>
                <ul class="pagination">
                    <li v-for="(link, li) in fl.links" :key="li"
                        class="page-item" :class="{ active: link.active, disabled: !link.url }">
                        <Link v-if="link.url" class="page-link" :href="link.url" v-html="link.label" preserve-scroll />
                        <span v-else class="page-link" v-html="link.label"></span>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>
