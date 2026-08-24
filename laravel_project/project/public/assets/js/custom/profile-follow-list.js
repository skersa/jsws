document.querySelectorAll('.fl-follow-btn').forEach(btn => {
    btn.addEventListener('click', function () {
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
            if (data.following) {
                this.innerHTML = '<i class="bi bi-person-check-fill"></i><span>Takip Ediliyor</span>';
                this.classList.add('following');
            } else {
                this.innerHTML = '<i class="bi bi-person-plus"></i><span>Takip Et</span>';
                this.classList.remove('following');
            }
        })
        .finally(() => { this.disabled = false; });
    });
});
