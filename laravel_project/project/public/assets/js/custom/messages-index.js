(function () {
    const thread = document.getElementById('msg-thread');
    const form = document.getElementById('msg-form');
    const input = document.getElementById('msg-input');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const pollUrl = thread.dataset.poll;

    function lastId() {
        const items = thread.querySelectorAll('.msg-bubble');
        return items.length ? parseInt(items[items.length - 1].dataset.mid || '0', 10) : 0;
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function append(m) {
        if (thread.querySelector('.msg-bubble[data-mid="' + m.id + '"]')) return;
        const el = document.createElement('div');
        el.className = 'msg-bubble ' + (m.mine ? 'mine' : 'theirs');
        el.dataset.mid = m.id;
        el.innerHTML = '<div class="msg-bubble-body">' + esc(m.body) + '</div><div class="msg-bubble-time">' + m.time + '</div>';
        thread.appendChild(el);
        thread.scrollTop = thread.scrollHeight;
    }

    thread.scrollTop = thread.scrollHeight;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const body = input.value.trim();
        if (!body) return;
        input.value = '';
        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ body })
        })
        .then(r => r.json())
        .then(m => append(m))
        .catch(() => {});
    });

    function poll() {
        fetch(pollUrl + '?after=' + lastId(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => { (d.messages || []).forEach(append); })
            .catch(() => {});
    }
    setInterval(poll, 4000);
})();
