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
        }).then(r => { if (r.isConfirmed) window.ajaxDeleteForm ? window.ajaxDeleteForm(form) : form.submit(); });
    });
});

document.querySelectorAll('.verify-form button').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const name     = this.dataset.name;
        const action   = this.dataset.action;
        const form     = this.closest('form');
        const isVerify = action === 'verify';
        Swal.fire({
            title: isVerify ? 'Hesabı doğrula?' : 'Doğrulamayı kaldır?',
            html: `<strong>${name}</strong> ${isVerify ? 'doğrulanmış olarak işaretlenecek.' : 'doğrulaması kaldırılacak.'}`,
            icon: isVerify ? 'success' : 'warning',
            showCancelButton: true,
            confirmButtonText: isVerify ? 'Doğrula' : 'Kaldır',
            cancelButtonText: 'Vazgeç',
            reverseButtons: true,
        }).then(r => { if (r.isConfirmed) window.ajaxDeleteForm ? window.ajaxDeleteForm(form) : form.submit(); });
    });
});
