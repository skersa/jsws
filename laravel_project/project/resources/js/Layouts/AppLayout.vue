<script setup>
import { computed, ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import StoryViewer from '@/Components/StoryViewer.vue';
import StoryUpload from '@/Components/StoryUpload.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const headerNotifs = computed(() => page.props.headerNotifications || []);
const flash = computed(() => page.props.flash || {});

const currentPath = computed(() => {
    try { return new URL(page.url, 'http://x').pathname; } catch (e) { return page.url; }
});
function isActive(pattern) {
    const p = currentPath.value;
    if (pattern === '/') return p === '/';
    return p === pattern || p.startsWith(pattern);
}

function fmtBalance(v) {
    return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v || 0);
}

function logout() {
    router.post(route('logout'));
}

/* ---- Tema geçişi ---- */
const isDark = ref(true);
function applyTheme(mode) {
    const d = document.documentElement;
    d.classList.remove('light-mode', 'dark-mode');
    d.classList.add(mode + '-mode');
    d.setAttribute('data-bs-theme', mode === 'dark' ? 'dark' : 'light');
    if (document.body) {
        document.body.classList.remove('light-mode', 'dark-mode');
        document.body.classList.add(mode + '-mode');
    }
    isDark.value = mode === 'dark';
}
function toggleTheme() {
    const next = isDark.value ? 'light' : 'dark';
    document.documentElement.classList.add('theme-switching');
    applyTheme(next);
    localStorage.setItem('theme', next);
    requestAnimationFrame(() => requestAnimationFrame(() =>
        document.documentElement.classList.remove('theme-switching')));
}

/* ---- Canlı arama ---- */
const searchQuery = ref('');
const searchResults = ref([]);
const recentSearches = ref([]);
const showResults = ref(false);
const RECENT_KEY = 'mhdr_recent_searches';
const MAX_RECENT = 6;
let debounceTimer = null;
const queryCache = {};

function loadRecent() {
    try { recentSearches.value = JSON.parse(localStorage.getItem(RECENT_KEY)) || []; }
    catch { recentSearches.value = []; }
}
function saveRecent(q) {
    let list = recentSearches.value.filter(x => x !== q);
    list.unshift(q);
    list = list.slice(0, MAX_RECENT);
    recentSearches.value = list;
    localStorage.setItem(RECENT_KEY, JSON.stringify(list));
}
function clearRecent() {
    localStorage.removeItem(RECENT_KEY);
    recentSearches.value = [];
    showResults.value = false;
}
function onFocus() {
    if (!searchQuery.value.trim()) { loadRecent(); showResults.value = recentSearches.value.length > 0; }
}
function onInput() {
    clearTimeout(debounceTimer);
    const q = searchQuery.value.trim();
    if (!q) { loadRecent(); searchResults.value = []; showResults.value = recentSearches.value.length > 0; return; }
    if (queryCache[q]) { searchResults.value = queryCache[q]; showResults.value = true; return; }
    debounceTimer = setTimeout(() => triggerSearch(q), 120);
}
function triggerSearch(q) {
    fetch(`/live-search?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => { queryCache[q] = data; searchResults.value = data; showResults.value = true; })
        .catch(() => {});
}
function pickRecent(q) {
    searchQuery.value = q;
    triggerSearch(q);
}
function onResultClick(item) {
    saveRecent(item.title);
    showResults.value = false;
    router.visit(item.url);
}
function onClickOutside(e) {
    const wrap = document.querySelector('.mhdr-search-wrap');
    if (wrap && !wrap.contains(e.target)) showResults.value = false;
}

/* ---- Bildirim: dropdown açılınca hepsini okundu işaretle ---- */
const notifCleared = ref(false);
function markNotifsRead() {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
    }).then(() => { notifCleared.value = true; }).catch(() => {});
}
const hasUnread = computed(() => !notifCleared.value && (user.value?.unread_notifications || 0) > 0);

/* ---- Mobilde Inertia gezinmesinde sidebar drawer'ını kapat ---- */
function closeMobileSidebar() {
    const el = document.getElementById('kt_app_sidebar');
    if (!el) return;
    try {
        const inst = window.KTDrawer && window.KTDrawer.getInstance(el);
        if (inst) inst.hide();
    } catch (e) { /* noop */ }
}
let removeNavListener = null;
let removeCsrfListener = null;

function syncCsrfMeta() {
    const t = page.props.csrf_token;
    if (!t) return;
    let meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) {
        meta = document.createElement('meta');
        meta.setAttribute('name', 'csrf-token');
        document.head.appendChild(meta);
    }
    meta.setAttribute('content', t);
    if (window.axios) window.axios.defaults.headers.common['X-CSRF-TOKEN'] = t;
}

onMounted(() => {
    applyTheme(localStorage.getItem('theme') || 'dark');
    // Login/Kayıt (AuthLayout) sayfasından ana uygulamaya geçince kalan
    // 'auth-page' gövde sınıfını temizle — aksi halde overflow:hidden kalır,
    // sayfa kaydırılamaz ve tema bozuk görünür.
    document.body.classList.remove('auth-page');
    document.addEventListener('click', onClickOutside);
    removeNavListener = router.on('start', () => closeMobileSidebar());
    // Her Inertia gezintisinden sonra CSRF token'ını taze tut (SPA'da meta bayatlıyordu → 419)
    syncCsrfMeta();
    removeCsrfListener = router.on('success', () => syncCsrfMeta());
    nextTick(() => window.initKT && window.initKT());
});

onUnmounted(() => {
    document.removeEventListener('click', onClickOutside);
    if (removeNavListener) removeNavListener();
    if (removeCsrfListener) removeCsrfListener();
});
</script>

<template>
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

            <!-- HEADER -->
            <div id="kt_app_header" class="app-header modern-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}">
                <div class="app-container container-xxl d-flex align-items-center justify-content-between" style="gap:8px; padding-left:12px; padding-right:12px;">

                    <div class="d-flex align-items-center d-lg-none">
                        <div class="btn modern-icon" id="kt_app_sidebar_mobile_toggle">
                            <i class="bi bi-list fs-3"></i>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mhdr-search-wrap position-relative">
                        <div class="search-box position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="mhdr-input" class="form-control search-input" name="q"
                                   placeholder="Müzayede, ilan veya kullanıcı ara..." autocomplete="off"
                                   v-model="searchQuery" @focus="onFocus" @input="onInput" data-testid="header-search-input">
                            <div id="search-results" class="mhdr-search-results" :class="{ 'd-none': !showResults }">
                                <template v-if="searchQuery.trim() && searchResults.length">
                                    <a v-for="item in searchResults" :key="item.url" :href="item.url"
                                       class="search-result-item" @click.prevent="onResultClick(item)">
                                        <div class="search-result-avatar"><img :src="item.avatar" :alt="item.title"></div>
                                        <div class="search-result-info">
                                            <span class="search-result-title">{{ item.title }}</span>
                                            <span class="search-result-badge">{{ item.username }}</span>
                                        </div>
                                        <div class="search-result-arrow"><i class="bi bi-chevron-right"></i></div>
                                    </a>
                                </template>
                                <div v-else-if="searchQuery.trim() && !searchResults.length" class="search-no-result">Sonuç bulunamadı.</div>
                                <template v-else-if="recentSearches.length">
                                    <div class="search-recent-header">Son Aramalar</div>
                                    <div v-for="q in recentSearches" :key="q" class="search-recent-item" @mousedown.prevent="pickRecent(q)">
                                        <i class="bi bi-clock-history search-recent-icon"></i>
                                        <span style="flex:1">{{ q }}</span>
                                        <i class="bi bi-chevron-right" style="font-size:10px;opacity:0.3"></i>
                                    </div>
                                    <div class="search-recent-clear" @mousedown.prevent="clearRecent">Temizle</div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center" style="gap:4px; flex-shrink:0;">
                        <button class="btn modern-icon" id="themeToggle" @click="toggleTheme" data-testid="theme-toggle">
                            <i class="bi fs-5" :class="isDark ? 'bi-moon' : 'bi-sun'"></i>
                        </button>

                        <template v-if="user">
                            <template v-if="user.is_seller">
                                <Link :href="route('seller.auctions.create')" class="btn btn-primary btn-sm modern-btn d-none d-lg-inline-flex align-items-center">
                                    <i class="bi bi-plus-lg me-1 d-flex"></i><span>İlan Ver</span>
                                </Link>
                                <Link :href="route('seller.auctions.create')" class="btn modern-icon d-flex d-lg-none" style="color:#155eef">
                                    <i class="bi bi-plus-lg fs-5"></i>
                                </Link>
                            </template>

                            <div v-if="!user.is_admin" class="d-flex align-items-center gap-2 me-1">
                                <Link :href="route('general.balance.index')" class="balance-pill d-none d-md-flex align-items-center">
                                    <i class="bi bi-wallet2 me-1"></i>
                                    <span>{{ fmtBalance(user.balance) }} ₺</span>
                                </Link>
                                <Link :href="route('general.balance.index')" class="btn modern-icon d-flex d-md-none" title="Bakiye Yükle">
                                    <i class="bi bi-wallet2 fs-5"></i>
                                </Link>
                            </div>

                            <div class="dropdown">
                                <button class="btn modern-icon position-relative" data-bs-toggle="dropdown" id="notifToggle" @click="markNotifsRead" data-testid="header-notif-toggle">
                                    <i class="bi bi-bell fs-5"></i>
                                    <span v-if="hasUnread" class="notif-dot"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end modern-dropdown" style="width:340px; padding:0;">
                                    <div class="user-box fw-semibold d-flex align-items-center justify-content-between">
                                        <span>Bildirimler</span>
                                        <Link :href="route('notifications.index')" class="fs-8 text-muted text-decoration-none">Tümünü gör</Link>
                                    </div>
                                    <div v-if="!headerNotifs.length" class="text-center text-muted py-4" style="font-size:12px;">
                                        <i class="bi bi-bell-slash d-block mb-2 opacity-50 fs-5"></i>Yeni bildirim yok
                                    </div>
                                    <template v-else>
                                        <div style="max-height:340px; overflow-y:auto;">
                                            <Link v-for="n in headerNotifs" :key="n.id" :href="n.link && n.link !== '#' ? n.link : route('notifications.index')"
                                               class="dropdown-item d-flex align-items-center gap-2 py-2 px-3"
                                               :style="{ whiteSpace: 'normal', background: n.unread ? '#155eef0a' : 'transparent' }">
                                                <div style="position:relative; width:34px; height:34px; flex-shrink:0;">
                                                    <img v-if="n.avatar_img" :src="n.avatar_img" style="width:34px;height:34px;border-radius:50%;object-fit:cover;" alt="">
                                                    <div v-else-if="n.avatar_char" style="width:34px;height:34px;border-radius:50%;background:#155eef;color:#fff;font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center;">{{ n.avatar_char }}</div>
                                                    <div v-else :style="{ width:'34px',height:'34px',borderRadius:'50%',background:n.color+'22',color:n.color,fontSize:'14px',display:'flex',alignItems:'center',justifyContent:'center' }"><i class="bi" :class="n.icon"></i></div>
                                                    <div :style="{ position:'absolute',bottom:'-2px',right:'-3px',width:'16px',height:'16px',borderRadius:'50%',background:n.color,border:'2px solid var(--search-bg)',display:'flex',alignItems:'center',justifyContent:'center' }">
                                                        <i class="bi" :class="n.icon" style="font-size:7px;color:#fff;"></i>
                                                    </div>
                                                </div>
                                                <div style="flex:1;min-width:0;">
                                                    <div :style="{ fontSize:'12.5px', fontWeight: n.unread ? 600 : 400, color:'var(--search-text-main)', lineHeight:1.35 }">{{ n.message }}</div>
                                                    <div style="font-size:11px;color:var(--search-text-muted);margin-top:2px;">{{ n.time }}</div>
                                                </div>
                                                <div v-if="n.unread" style="width:7px;height:7px;border-radius:50%;background:#155eef;flex-shrink:0;"></div>
                                            </Link>
                                        </div>
                                        <div class="px-3 py-2 border-top" style="border-color:var(--search-border)!important;">
                                            <Link :href="route('notifications.index')" class="d-block text-center text-decoration-none" style="font-size:12px;color:var(--search-text-muted);">Tüm bildirimleri gör</Link>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="dropdown">
                                <a class="d-flex align-items-center justify-content-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:36px; height:36px; border-radius:50%; overflow:hidden; cursor:pointer; flex-shrink:0;">
                                    <img v-if="user.avatar" :src="user.avatar" :alt="user.name" style="width:36px; height:36px; object-fit:cover;">
                                    <div v-else class="bg-primary text-white fw-bold d-flex align-items-center justify-content-center w-100 h-100">{{ user.avatar_char }}</div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end modern-dropdown">
                                    <div class="user-box">
                                        <div class="fw-bold">{{ user.name }}</div>
                                        <div class="text-muted fs-8">{{ user.email }}</div>
                                    </div>
                                    <Link class="dropdown-item" :href="route('profile.edit')">Profil</Link>
                                    <Link class="dropdown-item" :href="route('seller.auctions.index')">İlanlarım</Link>
                                    <Link class="dropdown-item" href="/my-bids">Tekliflerim</Link>
                                    <button class="dropdown-item" @click="logout" data-testid="header-logout">Çıkış Yap</button>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="mhdr-divider d-none d-sm-block"></div>
                            <Link href="/login" class="btn btn-light btn-sm">Giriş</Link>
                            <Link href="/register" class="btn btn-primary btn-sm">Kayıt</Link>
                        </template>
                    </div>
                </div>
            </div>
            <!-- /HEADER -->

            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

                <!-- SIDEBAR -->
                <div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="280px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

                    <div class="px-4 py-3 d-flex align-items-center">
                        <Link :href="route('index')" class="d-flex align-items-center gap-2 text-decoration-none">
                            <img :src="'/assets/media/logos/logo-dark.svg'" class="logo-dark" height="52">
                            <img :src="'/assets/media/logos/logo-light.svg'" class="logo-light" height="52">
                        </Link>
                    </div>

                    <div class="px-3 py-3" style="height:calc(100vh - 160px); overflow-y:auto;">
                        <Link href="/" class="sidebar-link" :class="{ active: isActive('/') }"><i class="bi bi-house"></i>Ana Sayfa</Link>
                        <Link :href="route('browse.auctions')" class="sidebar-link" :class="{ active: isActive('/browse/auctions') }"><i class="bi bi-tag"></i>Müzayedeler</Link>
                        <Link :href="route('browse.live')" class="sidebar-link" :class="{ active: isActive('/browse/live') }"><i class="bi bi-broadcast"></i>Canlı Açık Artırma</Link>
                        <Link :href="route('browse.explore')" class="sidebar-link" :class="{ active: isActive('/browse/explore') }"><i class="bi bi-compass"></i>Keşfet</Link>

                        <template v-if="user">
                            <Link :href="route('messages.index')" class="sidebar-link" :class="{ active: isActive('/messages') }" data-testid="sidebar-messages">
                                <i class="bi bi-chat-dots"></i>Mesajlar
                                <span v-if="user.unread_messages > 0" class="sidebar-badge">{{ user.unread_messages }}</span>
                            </Link>

                            <template v-if="user.is_admin">
                                <div class="sidebar-title">Admin Paneli</div>
                                <Link :href="route('admin.dashboard')" class="sidebar-link" :class="{ active: isActive('/admin/dashboard') }"><i class="bi bi-speedometer2"></i>Dashboard</Link>
                                <Link :href="route('admin.users.index')" class="sidebar-link" :class="{ active: isActive('/admin/users') }"><i class="bi bi-people"></i>Kullanıcılar</Link>
                                <Link :href="route('admin.categories.index')" class="sidebar-link" :class="{ active: isActive('/admin/categories') }"><i class="bi bi-box"></i>Kategoriler</Link>
                                <Link :href="route('admin.auctions.index')" class="sidebar-link" :class="{ active: isActive('/admin/auctions') }"><i class="bi bi-hammer"></i>Müzayede Yönetimi</Link>
                                <Link :href="route('admin.orders.index')" class="sidebar-link" :class="{ active: isActive('/admin/orders') }" data-testid="sidebar-admin-orders"><i class="bi bi-box-seam"></i>Siparişler &amp; Anlaşmazlık</Link>
                            </template>

                            <template v-if="user.is_seller">
                                <div class="sidebar-title">Satıcı Paneli</div>
                                <Link :href="route('seller.dashboard')" class="sidebar-link" :class="{ active: isActive('/seller/dashboard') }"><i class="bi bi-grid"></i>Panel</Link>
                                <Link :href="route('seller.auctions.index')" class="sidebar-link" :class="{ active: isActive('/seller/auctions') }"><i class="bi bi-hammer"></i>İlanlarım</Link>
                                <a :href="route('seller.dashboard') + '#canliya-basla'" class="sidebar-link sidebar-link-broadcast" data-testid="sidebar-seller-broadcast">
                                    <i class="bi bi-broadcast"></i>Canlı Yayın
                                    <span v-if="user.seller_live_badge > 0" class="sidebar-live-badge">{{ user.seller_live_badge }}</span>
                                </a>
                                <Link :href="route('seller.sales.index')" class="sidebar-link" :class="{ active: isActive('/seller/sales') }" data-testid="sidebar-seller-sales"><i class="bi bi-box-seam"></i>Satışlarım</Link>
                            </template>

                            <template v-if="user.is_buyer">
                                <div class="sidebar-title">Hesabım</div>
                                <Link href="/dashboard" class="sidebar-link"><i class="bi bi-grid"></i>Dashboard</Link>
                                <Link href="/my-bids" class="sidebar-link"><i class="bi bi-graph-up"></i>Tekliflerim</Link>
                                <Link :href="route('orders.index')" class="sidebar-link" :class="{ active: isActive('/orders') }" data-testid="sidebar-buyer-orders"><i class="bi bi-box-seam"></i>Siparişlerim</Link>
                                <Link href="/favorites" class="sidebar-link"><i class="bi bi-heart"></i>Favoriler</Link>
                                <Link href="/notifications" class="sidebar-link"><i class="bi bi-bell"></i>Bildirimler</Link>
                            </template>

                            <Link v-if="user.is_admin" :href="route('admin.support.index')" class="sidebar-link" :class="{ active: isActive('/admin/support') }"><i class="bi bi-headset"></i>Destek</Link>
                            <Link v-else :href="route('support.index')" class="sidebar-link" :class="{ active: isActive('/support') }"><i class="bi bi-headset"></i>Destek</Link>

                            <div class="sidebar-title">Profil</div>
                            <Link :href="route('profile.edit')" class="sidebar-link" :class="{ active: currentPath === '/profile' }"><i class="bi bi-person"></i>Profilim</Link>
                            <Link v-if="user.is_admin" :href="route('admin.settings.index')" class="sidebar-link" :class="{ active: isActive('/admin/settings') }"><i class="bi bi-gear"></i>Ayarlar</Link>
                        </template>
                    </div>

                    <div v-if="user" class="px-6 py-3 sidebar-user dropdown">
                        <div class="d-flex align-items-center gap-3" data-bs-toggle="dropdown" style="cursor:pointer;">
                            <div class="symbol symbol-35px">
                                <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="rounded object-fit-cover">
                                <div v-else class="symbol-label bg-primary text-white fw-bold">{{ user.avatar_char }}</div>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="text-white fs-7 fw-semibold text-truncate">{{ user.name }}</div>
                                <div class="text-muted fs-8 text-truncate">{{ user.email }}</div>
                            </div>
                            <i class="bi bi-chevron-down text-muted"></i>
                        </div>
                        <ul class="dropdown-menu sidebar-dropdown dropdown-menu-end shadow">
                            <li class="sidebar-dropdown-header">
                                <div class="name">{{ user.name }}</div>
                                <div class="email">{{ user.email }}</div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><Link class="dropdown-item" :href="route('profile.edit')"><i class="bi bi-person"></i>Profilim</Link></li>
                            <li v-if="user.is_seller"><Link class="dropdown-item" :href="route('seller.profile.edit')"><i class="bi bi-gear"></i>Ayarlar</Link></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button class="dropdown-item text-danger" @click="logout"><i class="bi bi-box-arrow-right"></i>Çıkış Yap</button></li>
                        </ul>
                    </div>
                </div>
                <!-- /SIDEBAR -->

                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-xxl">
                                <slot />
                            </div>
                        </div>
                    </div>

                    <div id="kt_app_footer" class="app-footer">
                        <div class="app-container container-xxl d-flex flex-column flex-md-row align-items-center justify-content-between py-4">
                            <div class="d-flex align-items-center text-gray-500 fw-semibold fs-7">
                                <span>©{{ new Date().getFullYear() }}</span>
                                <a href="https://artirdim.com" target="_blank" class="mx-2 text-gray-800 text-hover-primary fw-bold fs-7 text-decoration-none">Artirdim.com</a>
                                <span class="text-muted">Tüm hakları saklıdır.</span>
                            </div>
                            <div class="d-flex align-items-center gap-4 mt-3 mt-md-0">
                                <Link :href="route('corporate')" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7">Hakkımızda</Link>
                                <Link :href="route('contact')" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7">İletişim</Link>
                                <Link :href="route('privacy')" class="text-muted text-hover-primary text-decoration-none fw-semibold fs-7">Gizlilik Politikası</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <StoryViewer v-if="user" />
    <StoryUpload v-if="user && user.is_seller" />
</template>
