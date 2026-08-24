function switchPTab(key, btn) {
    document.querySelectorAll('.pf-ptab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ['bilgiler', 'altlar', 'islemler'].forEach(k => {
        const el = document.getElementById('pc-' + k);
        if (el) el.classList.toggle('pf-tab-content-hidden', k !== key);
    });
}

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const name     = this.dataset.name;
        const children = parseInt(this.dataset.children || 0);
        const form     = this.closest('form');
        const extra    = children > 0
            ? `<br><span class="pf-text-danger pf-font-size-13">⚠️ ${children} alt kategori de silinecek!</span>`
            : '';
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
