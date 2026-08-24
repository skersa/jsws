/**
 * Admin > Ayarlar sayfası
 * — Sekme geçişi, WYSIWYG editor, karakter sayacı, logo önizleme,
 *   test e-postası gönderme, cache temizleme onayı
 */
(function () {
    'use strict';

    /* ─────────  Sekme geçişi  ───────── */
    window.switchSTab = function (key, btn) {
        document.querySelectorAll('.pf-ptab').forEach(function (b) {
            b.classList.remove('active');
        });
        if (btn) btn.classList.add('active');
        document.querySelectorAll('.s-panel').forEach(function (p) {
            p.classList.toggle('s-active', p.id === 'sc-' + key);
        });
    };

    /* ─────────  Zengin metin editor helper'ları  ───────── */
    window.execCmd = function (cmd, val) {
        document.execCommand(cmd, false, val || null);
    };

    window.syncEditor = function (editorId, hiddenId) {
        var editor = document.getElementById(editorId);
        var hidden = document.getElementById(hiddenId);
        if (editor && hidden) hidden.value = editor.innerHTML;
    };

    /* ─────────  Karakter sayacı  ───────── */
    window.charCount = function (id, cntId, max) {
        var input = document.getElementById(id);
        var counter = document.getElementById(cntId);
        if (input && counter) counter.textContent = input.value.length + '/' + max;
    };

    /* ─────────  Aktif form gönderimi  ───────── */
    window.submitActiveForm = function () {
        var panel = document.querySelector('.s-panel.s-active');
        if (!panel) return;
        var form = panel.querySelector('form');
        if (!form) return;
        var editor = panel.querySelector('[contenteditable]');
        var hidden = panel.querySelector('input[type=hidden][name$="_text"]');
        if (editor && hidden) hidden.value = editor.innerHTML;
        form.submit();
    };

    /* ─────────  Test e-postası gönderimi  ───────── */
    window.testMail = function () {
        var root = document.getElementById('adminSettingsRoot');
        var testMailUrl = root ? root.dataset.testMailUrl : '';
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var token = csrf ? csrf.getAttribute('content') : '';

        Swal.fire({
            title: 'Test E-postası',
            input: 'email',
            inputPlaceholder: 'Gönderilecek e-posta adresi',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Gönder',
            cancelButtonText: 'Vazgeç',
            heightAuto: false,
            didOpen: function () { document.body.classList.remove('swal2-height-auto'); }
        }).then(function (r) {
            if (!r.isConfirmed) return;
            fetch(testMailUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ email: r.value })
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Gönderildi!' : 'Hata!',
                        text: data.message,
                        heightAuto: false,
                        didOpen: function () { document.body.classList.remove('swal2-height-auto'); }
                    });
                });
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        /* Logo önizleme */
        var siteLogo = document.getElementById('site_logo');
        if (siteLogo) {
            siteLogo.addEventListener('change', function () {
                if (!this.files || !this.files[0]) return;
                var r = new FileReader();
                r.onload = function (e) {
                    var preview = document.getElementById('logoPreview');
                    if (preview) {
                        preview.innerHTML =
                            '<img src="' + e.target.result +
                            '" alt="Logo" style="width:100%;height:100%;object-fit:contain;">';
                    }
                };
                r.readAsDataURL(this.files[0]);
            });
        }

        /* Cache temizleme — onay penceresi */
        document.querySelectorAll('.danger-cache-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var form = this.closest('form');
                Swal.fire({
                    title: 'Emin misin?',
                    text: 'Tüm önbellek verileri silinecek.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, temizle',
                    cancelButtonText: 'Vazgeç',
                    reverseButtons: true,
                    heightAuto: false,
                    didOpen: function () { document.body.classList.remove('swal2-height-auto'); }
                }).then(function (r) {
                    if (r.isConfirmed && form) form.submit();
                });
            });
        });
    });
})();
