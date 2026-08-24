/**
 * Global AJAX silme yardımcısı
 * — Blade'lerdeki tüm DELETE formlarını (data-ajax-delete niteliğiyle işaretli olanları)
 *   sayfa yenilenmeden çalıştırır, SweetAlert2 onayı gösterir, başarıda satırı/kartı DOM'dan siler.
 *
 * Kullanım (Blade):
 *   <form action="{{ route('...destroy', $item) }}"
 *         method="POST"
 *         data-ajax-delete
 *         data-remove-target="#item-{{ $item->id }}"           {{-- silinecek element (opsiyonel) --}}
 *         data-confirm-title="Silmek istediğine emin misin?"    {{-- opsiyonel --}}
 *         data-confirm-text="Bu işlem geri alınamaz."           {{-- opsiyonel --}}
 *         data-success-msg="Silindi."                            {{-- opsiyonel --}}
 *         data-redirect="{{ route('...index') }}">              {{-- opsiyonel: silme sonrası yönlendir --}}
 *       @csrf @method('DELETE')
 *       <button type="submit">Sil</button>
 *   </form>
 *
 * Herhangi bir element data-ajax-remove ile de silinebilir:
 *   <button data-ajax-remove="/api/x/1" data-remove-target="#row-1">Kaldır</button>
 */
(function () {
    'use strict';

    function toast(icon, title) {
        if (typeof Swal === 'undefined') { alert(title); return; }
        Swal.fire({
            toast: true, position: 'top-end', icon: icon, title: title,
            showConfirmButton: false, timer: 2500, timerProgressBar: true,
            heightAuto: false,
            didOpen: function () { document.body.classList.remove('swal2-height-auto'); }
        });
    }

    function confirmDialog(opts) {
        return Swal.fire({
            title: opts.title || 'Emin misin?',
            text: opts.text || 'Bu işlem geri alınamaz.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: opts.confirm || 'Evet, sil',
            cancelButtonText: 'Vazgeç',
            reverseButtons: true,
            confirmButtonColor: '#ef4444',
            heightAuto: false,
            didOpen: function () { document.body.classList.remove('swal2-height-auto'); }
        });
    }

    function fadeOutAndRemove(el) {
        if (!el) return;
        el.style.transition = 'opacity .25s ease, transform .25s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateX(20px)';
        setTimeout(function () { el.remove(); }, 260);
    }

    function performDelete(form) {
        var url = form.action;
        var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var method = (form.querySelector('input[name="_method"]') || {}).value || 'DELETE';
        var target = form.dataset.removeTarget;
        var redirect = form.dataset.redirect;
        var successMsg = form.dataset.successMsg || 'Silindi.';

        var body = new FormData();
        body.append('_method', method);
        body.append('_token', token);

        // Bazı submit butonlarını devre dışı bırak
        var btn = form.querySelector('button[type=submit]');
        var originalBtnHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }

        return fetch(url, {
            method: 'POST',
            body: body,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            credentials: 'same-origin'
        })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json().catch(function () { return {}; });
        })
        .then(function (data) {
            toast('success', (data && data.message) || successMsg);

            if (redirect) {
                setTimeout(function () { window.location.href = redirect; }, 500);
                return;
            }

            if (target) {
                var el = document.querySelector(target);
                fadeOutAndRemove(el);
            } else {
                // Varsayılan: form'un en yakın "silinebilir" konteynerini bul (tr, li, .row, .card)
                var container = form.closest('tr, li, .row, .card, .list-item');
                if (container) fadeOutAndRemove(container);
            }
        })
        .catch(function (err) {
            toast('error', 'Silme başarısız: ' + err.message);
            if (btn) { btn.disabled = false; btn.innerHTML = originalBtnHtml; }
        });
    }

    // Global exports: mevcut custom JS dosyaları (form.submit() yerine) bunu çağırabilir
    window.ajaxDeleteForm = performDelete;
    window.ajaxToast = toast;

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches('form[data-ajax-delete]')) return;
        e.preventDefault();

        confirmDialog({
            title: form.dataset.confirmTitle,
            text: form.dataset.confirmText,
            confirm: form.dataset.confirmButton
        }).then(function (r) {
            if (r.isConfirmed) performDelete(form);
        });
    });
})();
