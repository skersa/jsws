/**
 * Eski canlı açık artırma sayfası (old-live) — konfigürasyon köprüsü
 * Blade'deki #oldLiveConfigRoot data-* niteliklerinden global sabitleri kurar.
 */
(function () {
    'use strict';

    var root = document.getElementById('oldLiveConfigRoot');
    if (!root) return;

    function num(v, def) {
        var n = parseInt(v, 10);
        return isNaN(n) ? def : n;
    }

    window.AUCTION_ID    = num(root.dataset.auctionId, 0);
    window.MIN_INCREMENT = num(root.dataset.minIncrement, 0);
    window.BID_URL       = root.dataset.bidUrl || '';
    window.CSRF          = root.dataset.csrf || '';
    window.currentMin    = num(root.dataset.currentMin, 0);
    window.mediaStream   = null;
    window.facingMode    = 'environment';
    window.streamMuted   = false;
})();
