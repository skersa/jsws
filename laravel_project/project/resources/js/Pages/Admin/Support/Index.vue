<script>
import AppLayout from '@/Layouts/AppLayout.vue';
export default { layout: AppLayout };
</script>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({ counts: Object, filters: Object, tickets: Object });

const q = ref(props.filters.q || '');
const status = ref(props.filters.status || '');
const priority = ref(props.filters.priority || '');
watch(() => props.filters, (f) => { q.value = f.q || ''; status.value = f.status || ''; priority.value = f.priority || ''; });

const stats = computed(() => [
    ['Toplam', props.counts.all, 'bi-headset'],
    ['Açık', props.counts.open, 'bi-circle'],
    ['İşlemde', props.counts.in_progress, 'bi-arrow-repeat'],
    ['Kapalı', props.counts.closed, 'bi-check-circle'],
]);
const hasFilters = computed(() => !!(props.filters.q || props.filters.status || props.filters.priority));

function submitFilter() {
    const p = {};
    if (q.value) p.q = q.value;
    if (status.value) p.status = status.value;
    if (priority.value) p.priority = priority.value;
    router.get(route('admin.support.index'), p, { preserveState: true, preserveScroll: true });
}
</script>

<template>
    <Head title="Destek Yönetimi" />
    <div class="container-fluid py-3">
        <div class="pf-toolbar mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="pf-toolbar-title mb-1">Destek Yönetimi</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 pf-breadcrumb-list">
                            <li class="breadcrumb-item"><Link :href="route('admin.dashboard')" class="pf-breadcrumb-link">Admin</Link></li>
                            <li class="breadcrumb-item active pf-breadcrumb-active">Destek</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div v-for="s in stats" :key="s[0]" class="col-6 col-md-3">
                <div class="admin-stat">
                    <div class="admin-stat-icon bg-primary-soft"><i class="bi text-primary" :class="s[2]"></i></div>
                    <div>
                        <div class="admin-stat-num">{{ s[1] }}</div>
                        <div class="admin-stat-lbl">{{ s[0] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-3 p-3">
            <form @submit.prevent="submitFilter" class="d-flex align-items-center gap-2 flex-wrap">
                <div class="admin-input-wrap flex-grow-1" style="min-width:180px;">
                    <i class="bi bi-search admin-input-icon"></i>
                    <input type="text" v-model="q" class="admin-filter-input w-100" placeholder="Konu ara..." data-testid="support-search">
                </div>
                <select v-model="status" class="admin-filter-select" @change="submitFilter" data-testid="support-status">
                    <option value="">Tüm Durumlar</option>
                    <option value="open">Açık</option>
                    <option value="in_progress">İşlemde</option>
                    <option value="closed">Kapalı</option>
                </select>
                <select v-model="priority" class="admin-filter-select" @change="submitFilter" data-testid="support-priority">
                    <option value="">Tüm Öncelikler</option>
                    <option value="high">Yüksek</option>
                    <option value="medium">Orta</option>
                    <option value="low">Düşük</option>
                </select>
                <button type="submit" class="btn-admin-pri"><i class="bi bi-search"></i></button>
                <Link v-if="hasFilters" :href="route('admin.support.index')" class="btn-admin-sec"><i class="bi bi-x"></i></Link>
            </form>
        </div>

        <div class="admin-card">
            <div class="admin-card-head">
                <div class="admin-card-title"><i class="bi bi-list-ul"></i> Tüm Talepler</div>
                <span class="a-badge info">{{ tickets.total }}</span>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Kullanıcı</th><th>Konu</th><th>Öncelik</th><th>Durum</th><th>Güncelleme</th><th></th></tr>
                    </thead>
                    <tbody>
                        <template v-if="tickets.data.length">
                            <tr v-for="t in tickets.data" :key="t.id">
                                <td class="text-muted">#{{ t.id }}</td>
                                <td>
                                    <div class="admin-info-val">{{ t.user_name }}</div>
                                    <div class="pf-hint">{{ t.user_email }}</div>
                                </td>
                                <td>
                                    <div class="admin-info-val">{{ t.subject }}</div>
                                    <div class="pf-hint">{{ t.messages_count }} mesaj</div>
                                </td>
                                <td><span class="a-badge" :class="t.priority_badge">{{ t.priority_label }}</span></td>
                                <td><span class="a-badge" :class="t.status_badge">{{ t.status_label }}</span></td>
                                <td>
                                    <div class="pf-hint">{{ t.updated_human }}</div>
                                    <div v-if="t.awaiting" class="support-awaiting">● Yanıt bekliyor</div>
                                </td>
                                <td><Link :href="t.show_url" class="btn btn-outline-primary btn-sm">Görüntüle</Link></td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="7" class="text-muted text-center py-5">Talep bulunamadı.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="tickets.has_pages" class="fl-pagination border-top">
                <Link v-for="(l, i) in tickets.links" :key="i" :href="l.url || '#'"
                      class="pf-pagination-item" :class="{ active: l.active, disabled: !l.url }" v-html="l.label" />
            </div>
        </div>
    </div>
</template>
