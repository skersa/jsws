document.querySelectorAll('.js-reject-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('rejectModalDesc').textContent =
            `"${this.dataset.title}" ilanı reddedilecek ve satıcıya bildirim gönderilecek.`;
        document.getElementById('rejectForm').action =
            `/admin/auctions/${this.dataset.id}/reject`;
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    });
});

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const name = this.dataset.name;
        const form = this.closest('form');
        Swal.fire({
            title: 'İlan silinsin mi?',
            html: `<strong>${name}</strong> kalıcı olarak kaldırılacak.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil',
            cancelButtonText: 'Vazgeç',
            reverseButtons: true,
        }).then(r => { if (r.isConfirmed) window.ajaxDeleteForm ? window.ajaxDeleteForm(form) : form.submit(); });
    });
});
