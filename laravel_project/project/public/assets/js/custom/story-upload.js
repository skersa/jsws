/* Hikaye yükleme — AJAX submit + canlı önizleme */
function openStoryUpload() {
    const m = document.getElementById('storyUploadModal');
    if (m) {
        m.hidden = false;
        // hidden attribute'unu kaldırdıktan sonra .open ile transitionu tetikle
        requestAnimationFrame(() => m.classList.add('open'));
        document.body.style.overflow = 'hidden';
    }
}
function closeStoryUpload() {
    const m = document.getElementById('storyUploadModal');
    if (m) {
        m.classList.remove('open');
        // Transition bitene kadar bekle sonra hidden yap (~250ms CSS transition + buffer)
        setTimeout(() => { m.hidden = true; }, 260);
        document.body.style.overflow = '';
    }
    const form = document.getElementById('storyUploadForm');
    if (form) form.reset();
    const prev = document.getElementById('suPreview');
    if (prev) { prev.innerHTML = ''; prev.style.display = 'none'; }
    const ph = document.getElementById('suPlaceholder');
    if (ph) ph.style.display = '';
    const sub = document.getElementById('suSubmit');
    if (sub) sub.disabled = true;
}

(function () {
    const fi = document.getElementById('storyFileInput');
    if (!fi) return;

    fi.addEventListener('change', function () {
        const f = this.files[0];
        const prev = document.getElementById('suPreview');
        const ph = document.getElementById('suPlaceholder');
        const sub = document.getElementById('suSubmit');
        if (!f) return;
        const url = URL.createObjectURL(f);
        prev.innerHTML = f.type.startsWith('video')
            ? '<video src="' + url + '" muted autoplay loop playsinline></video>'
            : '<img src="' + url + '" alt="">';
        prev.style.display = 'block';
        ph.style.display = 'none';
        sub.disabled = false;
    });

    document.getElementById('storyUploadModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeStoryUpload();
    });

    const form = document.getElementById('storyUploadForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const sub = document.getElementById('suSubmit');
        const oldHtml = sub.innerHTML;
        sub.disabled = true;
        sub.innerHTML = '<i class="bi bi-hourglass-split"></i> Yükleniyor...';

        const fd = new FormData(form);
        const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            credentials: 'same-origin'
        })
        .then(function (res) {
            if (!res.ok) return res.json().then(function (err) { throw new Error(err.message || 'Yükleme başarısız'); });
            return res.json();
        })
        .then(function (data) {
            if (window.ajaxToast) window.ajaxToast('success', data.message || 'Hikayen paylaşıldı!');

            // Yeni hikaye verisini window.STORY_DATA'ya ekle
            if (data.story && data.user) {
                const uid = data.user.id;
                if (!window.STORY_DATA[uid]) {
                    window.STORY_DATA[uid] = {
                        name: data.user.name,
                        avatar: data.user.avatar,
                        isOwner: true,
                        items: []
                    };
                }
                window.STORY_DATA[uid].items.push({
                    id: data.story.id,
                    type: data.story.type,
                    url: data.story.url,
                    caption: data.story.caption
                });
                // Story-bar'a yeni item eklemek yerine sayfa reload yapmadan modal kapansın; kart görüntüsü zaten güncel olacak
                const bar = document.querySelector('.story-item[data-story-uid="' + uid + '"]');
                if (bar) {
                    // Zaten var — sadece güncelle (ring style değişebilir)
                    const ids = window.STORY_DATA[uid].items.map(function (i) { return i.id; });
                    bar.dataset.storyIds = JSON.stringify(ids);
                }
            }
            closeStoryUpload();
        })
        .catch(function (err) {
            if (window.ajaxToast) window.ajaxToast('error', err.message);
            else alert(err.message);
        })
        .finally(function () {
            sub.disabled = false;
            sub.innerHTML = oldHtml;
        });
    });
})();
