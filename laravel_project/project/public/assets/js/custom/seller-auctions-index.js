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
