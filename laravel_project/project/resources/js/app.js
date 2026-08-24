import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'artirdim';

// Metronic (KeenThemes) bileşenlerini Inertia gezinmesinden sonra yeniden başlat
function initKT() {
    try { window.KTComponents && window.KTComponents.init(); } catch (e) {}
    try { window.KTMenu && window.KTMenu.createInstances(); } catch (e) {}
    try { window.KTDrawer && window.KTDrawer.createInstances(); } catch (e) {}
    try { window.KTSticky && window.KTSticky.createInstances(); } catch (e) {}
    try { window.KTScroll && window.KTScroll.createInstances(); } catch (e) {}
}
window.initKT = initKT;

createInertiaApp({
    title: (title) => (title ? `${appName} | ${title}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`];
        if (!page) throw new Error(`Inertia sayfa bulunamadı: ${name}`);
        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: { color: '#155eef' },
}).then(() => setTimeout(initKT, 30));

router.on('navigate', () => setTimeout(initKT, 60));

/**
 * Scroll yönetimi (SPA gezinme)
 * - Yeni sayfa (ileri gezinme)  → her zaman en üstten başla
 * - Tarayıcı geri/ileri (popstate) → önceki scroll pozisyonu korunur (Inertia geri yükler)
 * - preserveScroll:true olan istekler (filtre/teklif vb.) → dokunma
 * Desktop + mobil için aynı davranış.
 */
if ('scrollRestoration' in window.history) {
    window.history.scrollRestoration = 'manual';
}

// Bu Metronic layout'ta GERÇEK kaydırılan element <body id="kt_app_body">'dir
// (html+body'de `overflow: hidden auto`). window/documentElement hep 0 kalır,
// bu yüzden Inertia'nın dahili scroll geri-yükleme mekanizması bu app'te çalışmaz.
// Pozisyonu kendimiz body üzerinden, URL bazlı bir harita ile yönetiyoruz.

const scrollMap = {};
let popState = false;
let ticking = false;
let suppressSave = false; // programatik scroll'lar (reset/restore) kaydedilmesin

function currentScroll() {
    return document.body.scrollTop || document.scrollingElement?.scrollTop || window.scrollY || 0;
}

// Kullanıcı kaydırdıkça mevcut sayfanın pozisyonunu canlı olarak sakla.
// body kaydırıldığı için scroll olayını capture fazında document üzerinden yakalıyoruz.
document.addEventListener('scroll', () => {
    if (suppressSave || ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
        scrollMap[window.location.href] = currentScroll();
        ticking = false;
    });
}, true);

function hardScrollTop() {
    suppressSave = true;
    try { window.scrollTo(0, 0); } catch (e) {}
    try { if (document.scrollingElement) document.scrollingElement.scrollTop = 0; } catch (e) {}
    try { document.documentElement.scrollTop = 0; } catch (e) {}
    try { document.body.scrollTop = 0; } catch (e) {}
    ['kt_app_root', 'kt_app_page', 'kt_app_wrapper', 'kt_app_main', 'kt_app_content', 'kt_app_content_container']
        .forEach((id) => { const el = document.getElementById(id); if (el) { try { el.scrollTop = 0; } catch (e) {} } });
    setTimeout(() => { suppressSave = false; }, 120);
}

// Geri/ileri: kaydedilen pozisyonu, içerik yüklenene kadar ISRARLA (2sn) geri yükle.
// Inertia geri gezinmede sayfayı yeniden render ettiği için içerik geç büyüyebilir;
// bu yüzden hedefe ulaşana veya süre dolana kadar her 50ms'de bir uygularız.
let restoreTimer = null;
function restoreScroll(target) {
    if (restoreTimer) { clearInterval(restoreTimer); restoreTimer = null; }
    if (!target || target <= 0) return;
    suppressSave = true;
    const start = Date.now();
    restoreTimer = setInterval(() => {
        document.body.scrollTop = target;
        const reached = document.body.scrollTop >= target - 4;
        if (reached || Date.now() - start > 2000) {
            clearInterval(restoreTimer);
            restoreTimer = null;
            setTimeout(() => { suppressSave = false; }, 100);
        }
    }, 50);
}

// popstate → geri/ileri gezinme. location.href bu noktada zaten güncellenmiştir.
window.addEventListener('popstate', () => {
    popState = true;
    suppressSave = true; // takas sırasındaki clamp değeri kaydı ezmesin
    const target = scrollMap[window.location.href] || 0;
    restoreScroll(target);
    setTimeout(() => { popState = false; }, 2200); // güvenlik: takılı kalmasın
});

// Navigasyon BAŞLAR BAŞLAMAZ kaydetmeyi dondur: Inertia DOM'u takas ederken yeni
// (kısa) sayfa body scroll'u otomatik "clamp" olur ve bu değer yanlışlıkla ayrılan
// sayfanın pozisyonu olarak kaydedilirdi. 'before' ile bunu engelliyoruz.
router.on('before', () => { suppressSave = true; });

router.on('finish', (event) => {
    const visit = event && event.detail && event.detail.visit;
    // Geri/ileri → restore zaten popstate'te çalışıyor, dokunma
    if (popState) {
        return;
    }
    // Filtre/teklif gibi preserveScroll → pozisyonu koru, sadece kaydı tekrar aç
    if (visit && visit.preserveScroll) {
        setTimeout(() => { suppressSave = false; }, 120);
        return;
    }
    // Yeni sayfa (ileri gezinme) → en üste dön (Vue render + geç layout'u da ez)
    hardScrollTop();
    requestAnimationFrame(hardScrollTop);
    setTimeout(hardScrollTop, 60);
});
