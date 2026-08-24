function switchPTab(key, btn) {
    document.querySelectorAll('.pf-ptab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ['bilgiler', 'ilanlar', 'teklifler', 'islemler'].forEach(k => {
        const el = document.getElementById('pc-' + k);
        if (el) el.style.display = k === key ? 'block' : 'none';
    });
}

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const name = this.dataset.name;
        const form = this.closest('form');
        Swal.fire({
            title: 'Emin misin?',
            html: `<strong>${name}</strong> kalıcı olarak silinecek.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil',
            cancelButtonText: 'Vazgeç',
            reverseButtons: true,
            heightAuto: false,
        }).then(r => { if (r.isConfirmed) window.ajaxDeleteForm ? window.ajaxDeleteForm(form) : form.submit(); });
    });
});

document.querySelectorAll('.verify-form button').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const form     = this.closest('form');
        const isVerify = this.dataset.action === 'verify';
        Swal.fire({
            title: isVerify ? 'Hesabı doğrula?' : 'Doğrulamayı kaldır?',
            icon: isVerify ? 'success' : 'warning',
            showCancelButton: true,
            confirmButtonText: isVerify ? 'Doğrula' : 'Kaldır',
            cancelButtonText: 'Vazgeç',
            reverseButtons: true,
        }).then(r => { if (r.isConfirmed) window.ajaxDeleteForm ? window.ajaxDeleteForm(form) : form.submit(); });
    });
});
