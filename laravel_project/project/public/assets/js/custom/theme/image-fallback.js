/**
 * Kırık/eksik görseller için otomatik placeholder değiştirici.
 * Global çalışır; layouts/app.blade.php içinden yüklenir.
 *
 * Placeholder yolu <html> etiketindeki data-image-fallback niteliğinden okunur.
 * Bir görseli hariç tutmak için: <img data-no-fallback ...>
 */
(function () {
    var PLACEHOLDER =
        document.documentElement.getAttribute('data-image-fallback') ||
        '/assets/media/placeholder.svg';

    function swap(img) {
        if (!img || img.dataset.noFallback !== undefined) return;
        if (img.getAttribute('src') === PLACEHOLDER) return;
        if (img.dataset.fallbackApplied) return;
        img.dataset.fallbackApplied = '1';
        img.src = PLACEHOLDER;
    }

    document.addEventListener(
        'error',
        function (e) {
            var el = e.target;
            if (el && el.tagName === 'IMG') swap(el);
        },
        true
    );

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('img').forEach(function (img) {
            if (img.complete && img.naturalWidth === 0) swap(img);
        });
    });
})();
