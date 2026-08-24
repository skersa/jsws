const searchInput  = document.getElementById('mhdr-input');
    const resultsBox   = document.getElementById('search-results');
    const RECENT_KEY   = 'mhdr_recent_searches';
    const MAX_RECENT   = 6;

    let debounceTimeout;
    const queryCache = {};

    const urlParams = new URLSearchParams(window.location.search);
    const qParam    = urlParams.get('q');
    if (qParam) searchInput.value = qParam;

    function getRecent() {
        try { return JSON.parse(localStorage.getItem(RECENT_KEY)) || []; }
        catch { return []; }
    }

    function saveRecent(query) {
        let list = getRecent().filter(q => q !== query);
        list.unshift(query);
        list = list.slice(0, MAX_RECENT);
        localStorage.setItem(RECENT_KEY, JSON.stringify(list));
    }

    function clearRecent() {
        localStorage.removeItem(RECENT_KEY);
    }

    function showRecentSearches() {
        const list = getRecent();
        if (!list.length) return;

        resultsBox.innerHTML = '';
        const header = document.createElement('div');
        header.className = 'search-recent-header';
        header.textContent = 'Son Aramalar';
        resultsBox.appendChild(header);

        list.forEach(q => {
            const item = document.createElement('div');
            item.className = 'search-recent-item';
            item.innerHTML = `
                <i class="bi bi-clock-history search-recent-icon"></i>
                <span style="flex:1">${escapeHtml(q)}</span>
                <i class="bi bi-chevron-right" style="font-size:10px;opacity:0.3"></i>
            `;
            item.addEventListener('mousedown', (e) => {
                e.preventDefault();
                searchInput.value = q;
                resultsBox.classList.add('d-none');
                triggerSearch(q);
            });
            resultsBox.appendChild(item);
        });

        const clearBtn = document.createElement('div');
        clearBtn.className = 'search-recent-clear';
        clearBtn.textContent = 'Temizle';
        clearBtn.addEventListener('mousedown', (e) => {
            e.preventDefault();
            clearRecent();
            resultsBox.classList.add('d-none');
        });
        resultsBox.appendChild(clearBtn);
        resultsBox.classList.remove('d-none');
    }

    function escapeHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    searchInput.addEventListener('focus', function () {
        if (!this.value.trim()) showRecentSearches();
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimeout);
        const query = this.value.trim();

        if (!query) {
            showRecentSearches();
            return;
        }

        if (queryCache[query]) {
            renderResults(queryCache[query]);
            return;
        }

        debounceTimeout = setTimeout(() => triggerSearch(query), 80);
    });

    function triggerSearch(query) {
        if (queryCache[query]) {
            renderResults(queryCache[query]);
            return;
        }

        fetch(`/live-search?q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(data => {
                queryCache[query] = data;
                renderResults(data);
            })
            .catch(err => console.error('Arama hatası:', err));
    }

    function renderResults(data) {
        resultsBox.innerHTML = '';

        if (!data.length) {
            resultsBox.innerHTML = '<div class="search-no-result">Sonuç bulunamadı.</div>';
            resultsBox.classList.remove('d-none');
            return;
        }

        data.forEach(item => {
            const a = document.createElement('a');
            a.href  = item.url;
            a.className = 'search-result-item';
            a.innerHTML = `
                <div class="search-result-avatar">
                    <img src="${item.avatar}" alt="${item.title}">
                </div>
                <div class="search-result-info">
                    <span class="search-result-title">${item.title}</span>
                    <span class="search-result-badge">${item.username}</span>
                </div>
                <div class="search-result-arrow"><i class="bi bi-chevron-right"></i></div>
            `;

            a.addEventListener('click', () => {
                saveRecent(item.title);
            });

            resultsBox.appendChild(a);
        });

        resultsBox.classList.remove('d-none');
    }

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.classList.add('d-none');
        }
    });
