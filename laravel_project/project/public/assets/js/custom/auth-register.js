/**
 * Auth > Kayıt Ol sayfası
 * — Kullanıcı adı sanitize + doğrulama hatası sonrası doğru adımı açma
 *
 * Sayfa yapılandırması: #authRegisterRoot data-* nitelikleri
 *   data-has-errors    : "1" ise hata var
 *   data-error-step    : Açılacak adım (1|2|3)
 *   data-final-step-label : 3. adımda gösterilecek etiket (rol=seller ise "Adım 3/3", diğerinde "Adım 2/2")
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        /* Kullanıcı adı canlı sanitize: küçük harf + [a-z0-9_.] */
        var username = document.getElementById('username');
        if (username) {
            username.addEventListener('input', function () {
                var pos = this.selectionStart;
                this.value = this.value.toLowerCase().replace(/[^a-z0-9_.]/g, '');
                try { this.setSelectionRange(pos, pos); } catch (e) { /* noop */ }
            });
        }

        /* Doğrulama hatası sonrası hangi adım açılacak? */
        var root = document.getElementById('authRegisterRoot');
        if (!root) return;

        var hasErrors = root.dataset.hasErrors === '1';
        if (!hasErrors) return;

        var step = parseInt(root.dataset.errorStep, 10) || 1;
        var finalLabel = root.dataset.finalStepLabel || '';

        if (typeof window.showStep === 'function') {
            if (step === 3 && finalLabel) {
                var label = document.getElementById('step_label');
                if (label) label.textContent = finalLabel;
            }
            window.showStep(step);
        }
    });
})();
