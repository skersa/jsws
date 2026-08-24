document.querySelectorAll('.idx-card-img img').forEach(img => {
    const wrap = img.closest('.idx-card-img');
    const reveal = () => {
        img.classList.add('loaded');
        wrap.classList.add('img-ready');
    };
    if (img.complete && img.naturalWidth > 0) {
        reveal();
    } else {
        img.addEventListener('load', reveal);
        img.addEventListener('error', reveal);
    }
});

let currentSort = window.idxCurrentSort || 'bids';

function setSort(val) {
    currentSort = val;
    document.querySelectorAll('.idx-sort-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('sort-' + val)?.classList.add('active');
    applyFilters();
}

function applyFilters() {
    const params = new URLSearchParams();
    const q   = document.getElementById('search-input')?.value.trim();
    const cat = document.getElementById('cat-select')?.value;
    const st  = document.getElementById('status-select')?.value;
    if (q)   params.set('q', q);
    if (cat) params.set('category', cat);
    if (st)  params.set('status', st);
    if (currentSort && currentSort !== 'bids') params.set('sort', currentSort);
    window.location.href = '?' + params.toString();
}

let searchTimer;
document.getElementById('search-input')?.addEventListener('input', function () {
    clearTimeout(searchTimer);
    const q = this.value.toLowerCase().trim();

    if (!q) {
        searchTimer = setTimeout(() => applyFilters(), 600);
        return;
    }

    let visible = 0;
    document.querySelectorAll('.auction-item').forEach(el => {
        const match = el.dataset.title.includes(q);
        el.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('result-count').textContent = visible;
    document.getElementById('no-results').style.display = visible === 0 ? 'block' : 'none';
});

function updateTimers() {
    const now = Math.floor(Date.now() / 1000);
    document.querySelectorAll('[data-ends]').forEach(el => {
        const diff = parseInt(el.dataset.ends) - now;
        if (diff <= 0) {
            el.textContent = 'Bitti';
            el.classList.add('critical');
            return;
        }
        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const s = diff % 60;
        el.textContent = h > 0
            ? `${h}s ${String(m).padStart(2, '0')}d`
            : `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        if (diff < 1800) el.classList.add('critical');
    });
}

setInterval(updateTimers, 1000);
updateTimers();
