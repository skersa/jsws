/**
 * Admin > Kullanıcılar > Düzenle sayfası
 * — Sekme geçişi, şifre gücü, avatar önizleme, hero bilgileri canlı güncelleme
 */
(function () {
    'use strict';

    // Sayfa yapılandırması (data-* niteliklerinden)
    var root = document.getElementById('adminUserEditRoot');
    var config = {
        defaultName: (root && root.dataset.defaultName) || '',
        defaultEmail: (root && root.dataset.defaultEmail) || ''
    };

    /* ─────────────  Sekme geçişi  ───────────── */
    window.switchETab = function (key, btn) {
        document.querySelectorAll('.pf-etab').forEach(function (b) {
            b.classList.remove('active');
        });
        if (btn) btn.classList.add('active');
        document.querySelectorAll('.pf-epanel').forEach(function (p) {
            p.classList.remove('active');
        });
        var panel = document.getElementById('ep-' + key);
        if (panel) panel.classList.add('active');
    };

    /* ─────────────  Şifre göster/gizle  ───────────── */
    window.togglePw = function (id) {
        var inp = document.getElementById(id);
        var icon = document.getElementById(id + '-icon');
        if (!inp) return;
        inp.type = inp.type === 'password' ? 'text' : 'password';
        if (icon) icon.className = inp.type === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
    };

    /* ─────────────  Şifre gücü ölçer  ───────────── */
    window.passStrength = function (el) {
        var v = el.value;
        var s = [
            v.length >= 8,
            /[A-Z]/.test(v),
            /[0-9]/.test(v),
            /[^a-z0-9]/i.test(v)
        ].filter(Boolean).length;
        var col = ['', '#ef4444', '#f59e0b', '#10b981', '#155eef'];
        for (var i = 1; i <= 4; i++) {
            var bar = document.getElementById('pb' + i);
            if (bar) bar.style.background = i <= s ? col[s] : 'var(--border)';
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        var pw1 = document.getElementById('pw1');
        if (pw1) pw1.addEventListener('input', function () { window.passStrength(this); });

        /* Avatar önizleme */
        var avatar = document.getElementById('avatar');
        if (avatar) {
            avatar.addEventListener('change', function () {
                if (!this.files || !this.files[0]) return;
                var reader = new FileReader();
                reader.onload = function (e) {
                    ['heroAvatar', 'avatarPreviewSmall'].forEach(function (id) {
                        var el = document.getElementById(id);
                        if (el) el.src = e.target.result;
                    });
                };
                reader.readAsDataURL(this.files[0]);
            });
        }

        /* Hero bilgileri canlı senkron */
        var inputName = document.getElementById('inputName');
        if (inputName) {
            inputName.addEventListener('input', function () {
                var el = document.getElementById('heroName');
                if (el) el.textContent = this.value || config.defaultName;
            });
        }

        var inputEmail = document.getElementById('inputEmail');
        if (inputEmail) {
            inputEmail.addEventListener('input', function () {
                var el = document.getElementById('heroEmail');
                if (el) el.textContent = this.value || config.defaultEmail;
            });
        }

        /* Kaydet butonu — spam engelleme */
        var saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            var form = saveBtn.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    saveBtn.disabled = true;
                });
            }
        }
    });
})();
