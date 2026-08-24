'use strict';

const form  = document.getElementById('kt_sign_up_form');
const step1 = document.getElementById('step_1');
const step2 = document.getElementById('step_2');
const step3 = document.getElementById('step_3');

function showStep(n) {
    [step1, step2, step3].forEach((s, i) => {
        if (s) s.style.display = (i + 1 === n) ? '' : 'none';
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function getRole() {
    return document.querySelector('.role-radio:checked')?.value ?? 'buyer';
}

function getField(name) {
    return form.querySelector(`[name="${name}"]`);
}

function setError(el, msg) {
    el.classList.add('is-invalid');
    el.classList.remove('is-valid');
    const fb = el.closest('.fv-row')?.querySelector('.invalid-feedback');
    if (fb && msg) fb.textContent = msg;
}

function setValid(el) {
    el.classList.remove('is-invalid');
    el.classList.add('is-valid');
    const fb = el.closest('.fv-row')?.querySelector('.invalid-feedback');
    if (fb) fb.textContent = '';
}

function clearState(el) {
    el.classList.remove('is-invalid', 'is-valid');
}

const rules = {
    name(v) {
        if (!v.trim()) return 'Ad Soyad zorunludur.';
        if (v.trim().length < 2) return 'En az 2 karakter olmalı.';
        return true;
    },
    username(v) {
        if (!v.trim()) return 'Kullanıcı adı zorunludur.';
        if (v.trim().length < 3) return 'En az 3 karakter olmalı.';
        if (v.trim().length > 30) return 'En fazla 30 karakter olabilir.';
        if (!/^[a-z0-9_.]+$/.test(v.trim())) return 'Sadece harf, rakam, nokta ve alt çizgi kullanılabilir.';
        return true;
    },
    email(v) {
        if (!v.trim()) return 'E-posta zorunludur.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim())) return 'Geçerli bir e-posta girin.';
        return true;
    },
    phone(v) {
        if (!v.trim()) return 'Telefon zorunludur.';
        if (!/^[\d\s\+\-\(\)]{7,15}$/.test(v.trim())) return 'Geçerli bir telefon girin.';
        return true;
    },
    tax_number(v) {
        if (!v.trim()) return 'Vergi/TC numarası zorunludur.';
        if (!/^\d{10,11}$/.test(v.trim())) return '10-11 haneli rakam olmalı.';
        return true;
    },
    iban(v) {
        const clean = v.replace(/\s/g, '').toUpperCase();
        if (!clean) return 'IBAN zorunludur.';
        if (!/^TR\d{24}$/.test(clean)) return 'TR ile başlayan 26 karakterli IBAN girin.';
        return true;
    },
    password(v) {
        if (!v) return 'Şifre zorunludur.';
        if (v.length < 8) return 'En az 8 karakter olmalı.';
        if (!/[A-Z]/.test(v)) return 'En az bir büyük harf içermeli.';
        if (!/[a-z]/.test(v)) return 'En az bir küçük harf içermeli.';
        if (!/[\d\W]/.test(v)) return 'En az bir rakam veya sembol içermeli.';
        return true;
    },
};

function validateField(el) {
    const rule = rules[el.name];
    if (!rule) return true;
    const result = rule(el.value);
    if (result === true) { setValid(el); return true; }
    setError(el, result); return false;
}

form.querySelectorAll('input[name]').forEach(input => {
    input.addEventListener('input', function () {
        if (this.classList.contains('is-invalid')) clearState(this);

        if (this.name === 'username') {
            const pos = this.selectionStart;
            this.value = this.value.toLowerCase().replace(/[^a-z0-9_.]/g, '');
            try { this.setSelectionRange(pos, pos); } catch(e) {}
        }
        if (this.name === 'iban') {
            const pos = this.selectionStart;
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            try { this.setSelectionRange(pos, pos); } catch(e) {}
        }
        if (this.name === 'tax_number') {
            this.value = this.value.replace(/\D/g, '');
        }
        if (this.name === 'phone') {
            this.value = this.value.replace(/[^\d\s\+\-\(\)]/g, '');
        }
    });

    input.addEventListener('blur', function () {
        if (this.value && rules[this.name]) validateField(this);
    });
});


document.querySelectorAll('.role-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        this.closest('label').querySelector('.role-card').classList.add('selected');
    });
});
document.querySelectorAll('.role-radio').forEach(radio => {
    if (radio.checked) radio.closest('label').querySelector('.role-card').classList.add('selected');
});


document.getElementById('btn_next_1')?.addEventListener('click', function () {
    const fields = ['name', 'username', 'email', 'phone'];
    let ok = true, first = null;

    fields.forEach(name => {
        const el = getField(name);
        if (!el) return;
        if (!validateField(el)) { ok = false; if (!first) first = el; }
    });

    if (!ok) { first?.focus(); return; }

    if (getRole() === 'seller') {
        showStep(2);
    } else {
        document.getElementById('step_label').textContent = 'Adım 2 / 2';
        showStep(3);
    }
});


document.getElementById('btn_back_2')?.addEventListener('click', () => showStep(1));

document.getElementById('btn_next_2')?.addEventListener('click', function () {
    let ok = true, first = null;

    ['tax_number', 'iban'].forEach(name => {
        const el = getField(name);
        if (!el) return;
        if (!validateField(el)) { ok = false; if (!first) first = el; }
    });

    const doc = getField('id_document');
    if (doc) {
        if (!doc.files?.length) {
            doc.classList.add('is-invalid');
            const fb = doc.closest('.fv-row')?.querySelector('.invalid-feedback');
            if (fb) fb.textContent = 'Kimlik belgesi zorunludur.';
            if (!first) first = doc;
            ok = false;
        } else {
            const file = doc.files[0];
            const allowed = ['image/jpeg','image/png','application/pdf'];
            if (file.size > 5 * 1024 * 1024) {
                doc.classList.add('is-invalid');
                const fb = doc.closest('.fv-row')?.querySelector('.invalid-feedback');
                if (fb) fb.textContent = 'Dosya 5MB\'dan büyük olamaz.';
                if (!first) first = doc;
                ok = false;
            } else if (!allowed.includes(file.type)) {
                doc.classList.add('is-invalid');
                const fb = doc.closest('.fv-row')?.querySelector('.invalid-feedback');
                if (fb) fb.textContent = 'Sadece JPG, PNG veya PDF kabul edilir.';
                if (!first) first = doc;
                ok = false;
            } else {
                doc.classList.remove('is-invalid');
                doc.classList.add('is-valid');
            }
        }
    }

    if (!ok) { first?.focus(); return; }

    document.getElementById('step_label').textContent = 'Adım 3 / 3';
    showStep(3);
});

// Adım 3
document.getElementById('btn_back_3')?.addEventListener('click', function () {
    showStep(getRole() === 'seller' ? 2 : 1);
});


document.querySelectorAll('.eye-toggle').forEach(btn => {
    btn.addEventListener('click', function () {
        const inp = document.getElementById(this.dataset.target);
        if (!inp) return;
        inp.type = inp.type === 'password' ? 'text' : 'password';
        this.querySelector('.eye-off').classList.toggle('d-none');
        this.querySelector('.eye-on').classList.toggle('d-none');
    });
});


function getStrength(pass) {
    let s = 0;
    if (pass.length >= 8)          s++;
    if (pass.length >= 12)         s++;
    if (/[A-Z]/.test(pass))        s++;
    if (/[a-z]/.test(pass))        s++;
    if (/\d/.test(pass))           s++;
    if (/[^A-Za-z0-9]/.test(pass)) s++;
    return s;
}

const strengthLevels = [
    { pct: 0,   color: '',        label: '' },
    { pct: 16,  color: '#f87171', label: 'Çok zayıf' },
    { pct: 33,  color: '#f97316', label: 'Zayıf' },
    { pct: 50,  color: '#fbbf24', label: 'Orta' },
    { pct: 66,  color: '#a3e635', label: 'İyi' },
    { pct: 83,  color: '#34d399', label: 'Güçlü' },
    { pct: 100, color: '#10b981', label: 'Çok güçlü' },
];

document.getElementById('password')?.addEventListener('input', function () {
    const score = getStrength(this.value);
    const lvl   = strengthLevels[Math.min(score, 6)];
    const bar   = document.getElementById('password_strength_bar');
    const txt   = document.getElementById('password_strength_text');

    if (bar) { bar.style.width = lvl.pct + '%'; bar.style.background = lvl.color; }
    if (txt) { txt.textContent = lvl.label; txt.style.color = lvl.color; }

    const conf = document.getElementById('password_confirmation');
    if (conf?.value) checkMatch();
});

function checkMatch() {
    const pass = document.getElementById('password');
    const conf = document.getElementById('password_confirmation');
    const err  = document.getElementById('password_mismatch_error');
    if (!conf.value) return true;

    if (pass.value !== conf.value) {
        conf.classList.add('is-invalid');
        conf.classList.remove('is-valid');
        if (err) err.style.display = '';
        return false;
    }
    conf.classList.remove('is-invalid');
    conf.classList.add('is-valid');
    if (err) err.style.display = 'none';
    return true;
}

document.getElementById('password_confirmation')?.addEventListener('input', checkMatch);


form.addEventListener('submit', function (e) {
    const pass     = document.getElementById('password');
    const terms    = document.getElementById('terms_check');
    const termsErr = document.getElementById('terms_error');
    let ok = true;

    const passResult = rules.password(pass.value);
    if (passResult !== true) { setError(pass, passResult); ok = false; }
    else setValid(pass);

    if (!checkMatch()) ok = false;

    if (!terms?.checked) {
        if (termsErr) termsErr.style.display = '';
        ok = false;
    } else {
        if (termsErr) termsErr.style.display = 'none';
    }

    if (!ok) { e.preventDefault(); return; }

    const btn = document.getElementById('kt_sign_up_submit');
    btn?.setAttribute('data-kt-indicator', 'on');
    if (btn) btn.disabled = true;
});


form.querySelectorAll('input').forEach(input => {
    input.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const visible = [step1, step2, step3].findIndex(s => s && s.style.display !== 'none') + 1;
        const map = { 1: 'btn_next_1', 2: 'btn_next_2' };
        const btnId = map[visible];
        if (btnId) document.getElementById(btnId)?.click();
    });
});

document.querySelectorAll('.role-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.role-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-light-primary');
            card.classList.add('border-secondary', 'bg-transparent');
        });
        document.querySelectorAll('.role-icon-wrap').forEach(wrap => {
            wrap.classList.remove('bg-primary');
            wrap.classList.add('bg-secondary');
        });
        document.querySelectorAll('.role-icon-wrap svg').forEach(svg => {
            svg.classList.remove('text-white');
            svg.classList.add('text-muted');
        });
        document.querySelectorAll('.role-label').forEach(label => {
            label.classList.remove('text-primary');
            label.classList.add('text-muted');
        });

        const card = this.closest('label').querySelector('.role-card');
        card.classList.remove('border-secondary', 'bg-transparent');
        card.classList.add('border-primary', 'bg-light-primary');

        const wrap = card.querySelector('.role-icon-wrap');
        wrap.classList.remove('bg-secondary');
        wrap.classList.add('bg-primary');

        const svg = card.querySelector('.role-icon-wrap svg');
        svg.classList.remove('text-muted');
        svg.classList.add('text-white');

        const label = card.querySelector('.role-label');
        label.classList.remove('text-muted');
        label.classList.add('text-primary');
    });
});
