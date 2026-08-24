window.STORY_DATA = window.STORY_DATA || {};
    (function () {
        let curUser = null, curIndex = 0, timer = null;
        const viewer = document.getElementById('storyViewer');

        window.openStoryViewer = function (uid) {
            const u = window.STORY_DATA[uid];
            if (!u || !u.items || !u.items.length) return;
            curUser = uid; curIndex = 0;
            viewer.classList.add('open');
            document.body.style.overflow = 'hidden';
            markUserSeen(uid);
            render();
        };
        window.closeStoryViewer = function () {
            viewer.classList.remove('open');
            document.body.style.overflow = '';
            clearTimeout(timer);
            document.getElementById('svMedia').innerHTML = '';
        };
        window.storyNext = function () {
            const u = window.STORY_DATA[curUser];
            if (curIndex < u.items.length - 1) { curIndex++; render(); }
            else closeStoryViewer();
        };
        window.storyPrev = function () {
            if (curIndex > 0) { curIndex--; render(); }
        };
        window.deleteCurrentStory = function () {
            const u = window.STORY_DATA[curUser];
            if (!u) return;
            const item = u.items[curIndex];
            if (!item || !item.id) return;

            const form = document.getElementById('storyDeleteForm');
            form.action = '/stories/' + item.id;

            if (window.ajaxDeleteForm) {
                // SweetAlert2 tabanlı onay + AJAX silme
                Swal.fire({
                    title: 'Hikayeyi silmek istediğine emin misin?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, sil',
                    cancelButtonText: 'Vazgeç',
                    reverseButtons: true,
                    confirmButtonColor: '#ef4444',
                    heightAuto: false,
                    didOpen: () => document.body.classList.remove('swal2-height-auto')
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    window.ajaxDeleteForm(form).then(function () {
                        // Bellekten çıkar
                        u.items.splice(curIndex, 1);
                        if (u.items.length === 0) {
                            // Kullanıcıya ait hikaye kalmadıysa story-source elementini de sil
                            const src = document.querySelector('.story-source[data-user-id="' + curUser + '"]');
                            if (src) src.remove();
                            // Story-bar / profil kartından uygun listeden kaldır
                            const bar = document.querySelector('.story-item[data-story-uid="' + curUser + '"]');
                            if (bar) bar.remove();
                            document.getElementById('storyViewer').classList.remove('open');
                            document.body.style.overflow = '';
                            delete window.STORY_DATA[curUser];
                            return;
                        }
                        if (curIndex >= u.items.length) curIndex = u.items.length - 1;
                        render();
                    });
                });
            } else {
                if (!confirm('Bu hikayeyi silmek istediğine emin misin?')) return;
                form.submit();
            }
        };
        function render() {
            const u = window.STORY_DATA[curUser];
            if (!u) return;
            const item = u.items[curIndex];
            document.getElementById('svAvatar').src = u.avatar;
            document.getElementById('svName').textContent = u.name;
            document.getElementById('svCaption').textContent = item.caption || '';
            document.getElementById('svDelete').style.display = u.isOwner ? 'inline-flex' : 'none';
            const media = document.getElementById('svMedia');
            clearTimeout(timer);
            if (item.type === 'video') {
                media.innerHTML = '<video src="' + item.url + '" autoplay playsinline controls></video>';
            } else {
                media.innerHTML = '<img src="' + item.url + '" alt="">';
                timer = setTimeout(window.storyNext, 5000);
            }
            const prog = document.getElementById('storyProgress');
            prog.innerHTML = u.items.map((_, i) =>
                '<span class="' + (i < curIndex ? 'done' : (i === curIndex ? 'active' : '')) + '"></span>'
            ).join('');
        }
        document.addEventListener('keydown', e => {
            if (!viewer.classList.contains('open')) return;
            if (e.key === 'ArrowRight') window.storyNext();
            if (e.key === 'ArrowLeft') window.storyPrev();
            if (e.key === 'Escape') window.closeStoryViewer();
        });

        /* ── Instagram mantığı: görülen hikaye halkası soluk/gri ── */
        const SEEN_KEY = 'artirdim_seen_stories';
        function getSeen() {
            try { return new Set(JSON.parse(localStorage.getItem(SEEN_KEY) || '[]')); }
            catch (e) { return new Set(); }
        }
        function saveSeen(set) { localStorage.setItem(SEEN_KEY, JSON.stringify([...set])); }

        function paintRing(item, seen) {
            const ring = item.querySelector('.story-ring');
            if (seen) {
                item.classList.add('seen');
                if (ring && item.dataset.ringSeen) ring.setAttribute('style', item.dataset.ringSeen);
            } else {
                item.classList.remove('seen');
                if (ring && item.dataset.ringUnseen) ring.setAttribute('style', item.dataset.ringUnseen);
            }
        }

        function applySeenStates() {
            const seen = getSeen();
            document.querySelectorAll('.story-item[data-story-ids]').forEach(item => {
                if (item.classList.contains('story-add')) return;
                let ids = [];
                try { ids = JSON.parse(item.dataset.storyIds || '[]'); } catch (e) {}
                const allSeen = ids.length > 0 && ids.every(id => seen.has(id));
                paintRing(item, allSeen);
            });
        }

        function markUserSeen(uid) {
            const u = window.STORY_DATA[uid];
            if (!u || !u.items) return;
            const seen = getSeen();
            u.items.forEach(it => seen.add(it.id));
            saveSeen(seen);
            const item = document.querySelector('.story-item[data-story-uid="' + uid + '"]');
            if (item) paintRing(item, true);
        }

        document.addEventListener('DOMContentLoaded', applySeenStates);
        window.addEventListener('pageshow', applySeenStates);
    })();
