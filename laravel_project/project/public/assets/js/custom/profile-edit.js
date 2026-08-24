/**
 * Profil > Düzenle sayfası
 * — Profil bağlantısı paylaş, sekmeler, şifre gücü, avatar önizleme,
 *   kullanıcı adı sanitize, doğrulama hatalarını uygun sekmeye scroll etme
 *
 * Sayfa yapılandırması: #profileEditRoot data-* nitelikleri
 *   data-public-url   : Profil paylaşım URL'i
 *   data-drawer-open  : "1" ise sayfa yüklendiğinde drawer açılır
 *   data-drawer-tab   : "guvenlik" (opsiyonel) — açılınca gösterilecek tab
 *   data-error-fields : JSON string; doğrulama hatası olan alan adları
 */
(function () {
    'use strict';

    var FIELD_TAB_MAP = {
        name: 'genel', surname: 'genel', username: 'genel', phone: 'genel', bio: 'genel',
        email: 'guvenlik', confirmemailpassword: 'guvenlik', currentpassword: 'guvenlik',
        password: 'guvenlik', password_confirmation: 'guvenlik',
        delete_password: 'guvenlik',
        'social[instagram]': 'sosyal', 'social[twitter]': 'sosyal',
        'social[youtube]': 'sosyal', 'social[linkedin]': 'sosyal'
    };
    var TAB_ORDER = ['genel', 'guvenlik', 'gizlilik', 'sosyal'];

    /* ─── Profil bağlantısını panoya kopyala ─── */
    function copyProfileUrl(url) {
        var showToast = function () {
            Swal.fire({
                toast: true,
                scrollbarPadding: false,
                backdrop: false,
                position: 'top-end',
                icon: 'success',
                title: 'Profil bağlantısı kopyalandı!',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                heightAuto: false,
                didOpen: function () { document.body.classList.remove('swal2-height-auto'); }
            });
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(showToast).catch(function () {
                fallbackCopy(url, showToast);
            });
        } else {
            fallbackCopy(url, showToast);
        }
    }

    function fallbackCopy(text, cb) {
        var input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        try { document.execCommand('copy'); } catch (e) { /* noop */ }
        document.body.removeChild(input);
        if (cb) cb();
    }

    /* ─── Drawer aç/kapa ─── */
    window.toggleEdit = function () {
        var d = document.getElementById('editDrawer');
        var b = document.getElementById('editToggle');
        if (!d || !b) return;
        var open = d.classList.toggle('open');
        b.classList.toggle('active', open);
        b.innerHTML = open
            ? '<i class="bi bi-x-lg me-1"></i> Kapat'
            : '<i class="bi bi-pencil me-1"></i> Profili Düzenle';
        if (open) d.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    /* ─── Sekmeler ─── */
    window.switchETab = function (key, btn) {
        document.querySelectorAll('.pf-etab').forEach(function (b) { b.classList.remove('active'); });
        if (btn) btn.classList.add('active');
        document.querySelectorAll('.pf-epanel').forEach(function (p) { p.classList.remove('active'); });
        var panel = document.getElementById('ep-' + key);
        if (panel) panel.classList.add('active');
    };

    window.switchPTab = function (key, btn) {
        document.querySelectorAll('.pf-ptab').forEach(function (b) { b.classList.remove('active'); });
        if (btn) btn.classList.add('active');
        ['vitrin', 'degerlendirmeler', 'aktivite'].forEach(function (k) {
            var el = document.getElementById('pc-' + k);
            if (el) el.style.display = k === key ? '' : 'none';
        });
    };

    window.bioCount = function (el) {
        var c = document.getElementById('bio_counter');
        if (c) c.textContent = el.value.length + '/300';
    };

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
            var el2 = document.getElementById('pb' + i);
            if (el2) el2.style.background = i <= s ? col[s] : 'var(--border)';
        }
    };

    window.toggleInline = function (id) {
        var el = document.getElementById(id);
        if (el) el.classList.toggle('open');
    };

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.getElementById('profileEditRoot');
        var config = {
            publicUrl: root ? root.dataset.publicUrl : '',
            drawerOpen: root ? root.dataset.drawerOpen === '1' : false,
            drawerTab: root ? root.dataset.drawerTab : '',
            errorFields: []
        };

        try {
            if (root && root.dataset.errorFields) {
                config.errorFields = JSON.parse(root.dataset.errorFields);
            }
        } catch (e) {
            config.errorFields = [];
        }

        /* Paylaş butonu */
        var shareBtn = document.querySelector('[aria-label="Paylaş"]');
        if (shareBtn && config.publicUrl) {
            shareBtn.addEventListener('click', function () {
                copyProfileUrl(config.publicUrl);
            });
        }

        /* Avatar önizleme */
        var profileImage = document.getElementById('profile_image');
        if (profileImage) {
            profileImage.addEventListener('change', function () {
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

        /* Kullanıcı adı sanitize */
        var editUsername = document.getElementById('edit_username');
        if (editUsername) {
            editUsername.addEventListener('input', function () {
                var pos = this.selectionStart;
                this.value = this.value.toLowerCase().replace(/[^a-z0-9_.]/g, '');
                try { this.setSelectionRange(pos, pos); } catch (e) { /* noop */ }
            });
        }

        /* Kayıt sonrası drawer'ı yeniden aç */
        if (config.drawerOpen) {
            var drawer = document.getElementById('editDrawer');
            var toggleBtn = document.getElementById('editToggle');
            if (drawer) drawer.classList.add('open');
            if (toggleBtn) {
                toggleBtn.classList.add('active');
                toggleBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Kapat';
            }
            if (config.drawerTab === 'guvenlik') {
                window.switchETab('guvenlik', document.querySelector('.pf-etab:nth-child(2)'));
            }
        }

        /* Doğrulama hataları — doğru sekmeye scroll + focus */
        if (config.errorFields.length > 0) {
            var drawer2 = document.getElementById('editDrawer');
            var toggleBtn2 = document.getElementById('editToggle');
            if (drawer2) drawer2.classList.add('open');
            if (toggleBtn2) {
                toggleBtn2.classList.add('active');
                toggleBtn2.innerHTML = '<i class="bi bi-x-lg me-1"></i> Kapat';
            }

            var targetTab = null;
            for (var i = 0; i < TAB_ORDER.length; i++) {
                var tab = TAB_ORDER[i];
                if (config.errorFields.some(function (f) { return FIELD_TAB_MAP[f] === tab; })) {
                    targetTab = tab;
                    break;
                }
            }

            if (targetTab) {
                var tabBtn = document.querySelector('.pf-etab:nth-child(' + (TAB_ORDER.indexOf(targetTab) + 1) + ')');
                window.switchETab(targetTab, tabBtn);
            }

            var firstError = config.errorFields[0];
            var input = document.querySelector('[name="' + firstError + '"]');
            if (input) {
                setTimeout(function () {
                    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    input.focus({ preventScroll: true });
                    input.classList.add('pf-input-error-focus');
                    setTimeout(function () {
                        input.classList.remove('pf-input-error-focus');
                    }, 1200);
                }, 300);
            }
        }
    });
})();
