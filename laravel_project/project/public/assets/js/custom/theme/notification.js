document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById('notifToggle');

    btn?.addEventListener('click', function () {
        fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        }).then(() => {
            this.querySelector('.notif-dot')?.remove();
        });
    });
});
