document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const name     = this.dataset.name;
        const children = parseInt(this.dataset.children || 0);
        const form     = this.closest('form');
        const extra    = children > 0 ? `<br><span style="color:#f87171;font-size:12px;">⚠️ ${children} alt kategori de silinecek!</span>` : '';
        Swal.fire({
            title: 'Emin misin?',
            html: `<strong>${name}</strong> kalıcı olarak silinecek.${extra}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil',
            cancelButtonText: 'Vazgeç',
            reverseButtons: true,
        }).then(r => { if (r.isConfirmed) window.ajaxDeleteForm ? window.ajaxDeleteForm(form) : form.submit(); });
    });
});
