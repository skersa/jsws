/* profile-show.js — Blade + Inertia/Vue uyumlu.
 * Global handler fonksiyonları (inline onclick) korunur; tüm binding/başlatma
 * kodu window.__profileShowInit içine alınır ki Inertia SPA geçişlerinde
 * (yeniden mount) tekrar çağrılabilsin. Davranış birebir aynıdır. */

function fallbackCopy(text, cb) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.top = '-9999px';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.focus(); ta.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
    if (cb) cb();
}

function pfToast(msg) {
    let host = document.getElementById('pf-toast-host');
    if (!host) {
        host = document.createElement('div');
        host.id = 'pf-toast-host';
        document.body.appendChild(host);
    }
    const t = document.createElement('div');
    t.className = 'pf-toast';
    t.innerHTML = '<i class="bi bi-check-circle-fill"></i><span></span>';
    t.querySelector('span').textContent = msg;
    host.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2500);
}

function toggleEdit() {
    const d = document.getElementById('editDrawer');
    const b = document.getElementById('editToggle');
    const open = d.classList.toggle('open');
    b.classList.toggle('active', open);
    b.innerHTML = open ? '<i class="bi bi-x-lg me-1"></i> Kapat' : '<i class="bi bi-pencil me-1"></i> Profili Düzenle';
    if (open) d.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function switchETab(key, btn) {
    document.querySelectorAll('.pf-etab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.pf-epanel').forEach(p => p.classList.remove('active'));
    document.getElementById('ep-' + key).classList.add('active');
}

function switchPTab(key, btn) {
    document.querySelectorAll('.pf-ptab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ['vitrin', 'degerlendirmeler', 'aktivite'].forEach(k => {
        const el = document.getElementById('pc-' + k);
        if (el) el.style.display = k === key ? '' : 'none';
    });
}

function bioCount(el) {
    const c = document.getElementById('bio_counter');
    if (c) c.textContent = el.value.length + '/300';
}

function passStrength(el) {
    const v = el.value;
    const s = [v.length >= 8, /[A-Z]/.test(v), /[0-9]/.test(v), /[^a-z0-9]/i.test(v)].filter(Boolean).length;
    const col = ['', '#ef4444', '#f59e0b', '#10b981', '#155eef'];
    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById('pb' + i);
        if (bar) bar.style.background = i <= s ? col[s] : 'var(--border)';
    }
}

function toggleInline(id) {
    document.getElementById(id)?.classList.toggle('open');
}

function openDeleteModal() {
    const f = document.getElementById('delete-account-form');
    const pw = f?.querySelector('input[name="delete_password"]');
    if (pw && !pw.value.trim()) {
        pw.classList.add('is-invalid');
        pw.focus();
        return;
    }
    document.getElementById('deleteModal')?.classList.add('open');
}
function closeDeleteModal() {
    document.getElementById('deleteModal')?.classList.remove('open');
}
function submitDeleteAccount() {
    document.getElementById('delete-account-form')?.submit();
}

window.__profileShowInit = function () {
    'use strict';

    /* Yıldız puanlama */
    (function () {
        const wrap = document.getElementById('rvStars');
        if (!wrap) return;
        const inp = document.getElementById('rvRating');
        wrap.querySelectorAll('i').forEach(st => {
            st.addEventListener('click', () => {
                const v = +st.dataset.val;
                inp.value = v;
                wrap.querySelectorAll('i').forEach(x => {
                    const on = +x.dataset.val <= v;
                    x.classList.toggle('bi-star-fill', on);
                    x.classList.toggle('bi-star', !on);
                });
            });
        });
    })();

    /* ?tab=degerlendirmeler ile gelindiyse ilgili sekmeyi aç */
    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'degerlendirmeler') {
        const b = [...document.querySelectorAll('.pf-ptab')].find(x => x.textContent.includes('Değerlendirmeler'));
        if (b) { switchPTab('degerlendirmeler', b); b.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    }

    /* Silme modalı — arkaplan tıklama */
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });
    }
    /* Escape — tek sefer bağla (SPA'da yığılmasın) */
    if (!window.__pfKeydownBound) {
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDeleteModal();
        });
        window.__pfKeydownBound = true;
    }

    /* Profil fotoğrafı önizleme */
    const pImg = document.getElementById('profile_image');
    if (pImg) {
        pImg.addEventListener('change', function () {
            if (!this.files?.[0]) return;
            const reader = new FileReader();
            reader.onload = e => {
                ['heroAvatar', 'avatarPreviewSmall'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.src = e.target.result;
                });
            };
            reader.readAsDataURL(this.files[0]);
        });
    }

    /* Kullanıcı adı filtreleme */
    const uInput = document.getElementById('edit_username');
    if (uInput) {
        uInput.addEventListener('input', function () {
            const pos = this.selectionStart;
            this.value = this.value.toLowerCase().replace(/[^a-z0-9_.]/g, '');
            try { this.setSelectionRange(pos, pos); } catch (e) {}
        });
    }

    /* Takip et / bırak (AJAX) */
    const followBtn = document.getElementById('follow-btn');
    if (followBtn) {
        followBtn.addEventListener('click', function () {
            this.disabled = true;
            fetch(this.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.error) return;
                    const btn = document.getElementById('follow-btn');
                    if (data.following) {
                        btn.innerHTML = '<i class="bi bi-person-check-fill me-1"></i><span>Takibi Bırak</span>';
                        btn.classList.add('pf-btn-following');
                    } else {
                        btn.innerHTML = '<i class="bi bi-person-plus me-1"></i><span>Takip Et</span>';
                        btn.classList.remove('pf-btn-following');
                    }
                    const countEl = document.getElementById('follower-count');
                    if (countEl) countEl.textContent = data.follower_count;
                })
                .finally(() => { document.getElementById('follow-btn').disabled = false; });
        });
    }

    /* Paylaş butonu + drawer aç + doğrulama hatası tab/scroll */
    const FIELD_TAB_MAP = {
        name: 'genel', surname: 'genel', username: 'genel', phone: 'genel', bio: 'genel',
        email: 'guvenlik', confirmemailpassword: 'guvenlik', currentpassword: 'guvenlik',
        password: 'guvenlik', password_confirmation: 'guvenlik',
        delete_password: 'guvenlik',
        'social[instagram]': 'sosyal', 'social[twitter]': 'sosyal',
        'social[youtube]': 'sosyal', 'social[linkedin]': 'sosyal'
    };
    const TAB_ORDER = ['genel', 'guvenlik', 'gizlilik', 'sosyal'];

    const root = document.getElementById('profileShowRoot');
    if (!root) return;

    const publicUrl = root.dataset.publicUrl || '';
    const drawerOpen = root.dataset.drawerOpen === '1';
    const drawerTab = root.dataset.drawerTab || '';
    const drawerInline = root.dataset.drawerInline || '';
    let errorFields = [];
    try {
        if (root.dataset.errorFields) errorFields = JSON.parse(root.dataset.errorFields);
    } catch (e) { errorFields = []; }

    const shareBtn = document.querySelector('[aria-label="Paylaş"]');
    if (shareBtn && publicUrl) {
        shareBtn.addEventListener('click', function () {
            const done = function () {
                if (typeof pfToast === 'function') pfToast('Profil bağlantısı kopyalandı!');
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(publicUrl).then(done).catch(function () {
                    fallbackCopy(publicUrl, done);
                });
            } else {
                fallbackCopy(publicUrl, done);
            }
        });
    }

    if (drawerOpen) {
        const d = document.getElementById('editDrawer');
        const b = document.getElementById('editToggle');
        if (d) d.classList.add('open');
        if (b) {
            b.classList.add('active');
            b.innerHTML = '<i class="bi bi-x-lg me-1"></i> Kapat';
        }
        if (drawerTab === 'guvenlik' && typeof switchETab === 'function') {
            switchETab('guvenlik', document.querySelector('.pf-etab:nth-child(2)'));
        }
        /* Başarı mesajı ilgili inline formda gizli kalmasın: aç */
        if (drawerInline) {
            const inlineEl = document.getElementById(drawerInline);
            if (inlineEl && !inlineEl.classList.contains('open')) inlineEl.classList.add('open');
        }
    }

    if (errorFields.length > 0) {
        const drawer = document.getElementById('editDrawer');
        const btn = document.getElementById('editToggle');
        if (drawer) drawer.classList.add('open');
        if (btn) {
            btn.classList.add('active');
            btn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Kapat';
        }

        let targetTab = null;
        for (let i = 0; i < TAB_ORDER.length; i++) {
            const t = TAB_ORDER[i];
            if (errorFields.some(f => FIELD_TAB_MAP[f] === t)) { targetTab = t; break; }
        }

        if (targetTab && typeof switchETab === 'function') {
            switchETab(targetTab, document.querySelector('.pf-etab:nth-child(' + (TAB_ORDER.indexOf(targetTab) + 1) + ')'));
        }

        /* Güvenlik sekmesindeki hata ilgili katlanmış inline formda kalmasın: aç */
        var INLINE_MAP = {
            email: 'email-form', confirmemailpassword: 'email-form',
            currentpassword: 'pass-form', password: 'pass-form', password_confirmation: 'pass-form',
            delete_password: 'delete-form'
        };
        for (var j = 0; j < errorFields.length; j++) {
            var inlineId = INLINE_MAP[errorFields[j]];
            if (inlineId) {
                var inlineForm = document.getElementById(inlineId);
                if (inlineForm && !inlineForm.classList.contains('open')) inlineForm.classList.add('open');
                break;
            }
        }

        const firstError = errorFields[0];
        const input = document.querySelector('[name="' + firstError + '"]');
        if (input) {
            setTimeout(function () {
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                input.focus({ preventScroll: true });
                input.classList.add('pf-input-error-focus');
                setTimeout(function () { input.classList.remove('pf-input-error-focus'); }, 1200);
            }, 300);
        }
    }
};

/* Blade yolu: DOM hazır olduğunda; Inertia yolu: Show.vue onMounted çağırır */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.__profileShowInit);
} else {
    window.__profileShowInit();
}
