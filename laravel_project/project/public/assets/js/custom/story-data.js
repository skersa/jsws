/**
 * Story verileri — Blade'den taşınan JSON'u okuyup window.STORY_DATA'ya yazar.
 *
 * Kullanım:
 *   <div class="story-source"
 *        data-user-id="123"
 *        data-story-payload='{"name":"...","avatar":"...","isOwner":true,"items":[...]}'>
 *   </div>
 *
 * Aynı sayfada birden fazla source olabilir (story-bar + profile-stories birlikte).
 */
(function () {
    'use strict';

    function loadFromDom() {
        window.STORY_DATA = window.STORY_DATA || {};
        document.querySelectorAll('.story-source').forEach(function (el) {
            var uid = parseInt(el.dataset.userId, 10);
            if (!uid) return;
            try {
                window.STORY_DATA[uid] = JSON.parse(el.dataset.storyPayload || '{}');
            } catch (e) {
                console.warn('[story-data] geçersiz payload uid=' + uid, e);
            }
        });
    }

    if (document.readyState !== 'loading') {
        loadFromDom();
    } else {
        document.addEventListener('DOMContentLoaded', loadFromDom);
    }
})();
