function switchImg(el, src) {
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.thumb-img').forEach(t => {
        t.style.opacity = '.6';
        t.style.borderColor = 'transparent';
    });
    el.style.opacity = '1';
    el.style.borderColor = 'var(--primary)';
}

document.querySelectorAll('.js-reject-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('rejectModalDesc').textContent =
            `"${this.dataset.title}" ilanı reddedilecek ve satıcıya bildirim gönderilecek.`;
        document.getElementById('rejectForm').action =
            `/admin/auctions/${this.dataset.id}/reject`;
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    });
});

document.querySelectorAll('.js-delete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const form = this.closest('.js-delete-form');
        Swal.fire({
            title: 'İlan silinsin mi?',
            html: `<strong>${this.dataset.title}</strong> kalıcı olarak kaldırılacak.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil',
            cancelButtonText: 'Vazgeç',
            reverseButtons: true,
        }).then(r => { if (r.isConfirmed) window.ajaxDeleteForm ? window.ajaxDeleteForm(form) : form.submit(); });
    });
});
