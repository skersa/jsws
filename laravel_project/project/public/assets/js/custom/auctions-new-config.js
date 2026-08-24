/**
 * Yeni açık artırma detay sayfası — konfigürasyon köprüsü
 * Blade'deki #auctionNewConfigRoot data-* niteliklerinden global sabitleri kurar.
 */
(function () {
    'use strict';

    var root = document.getElementById('auctionNewConfigRoot');
    if (!root) return;

    function num(v, def) {
        var n = parseInt(v, 10);
        return isNaN(n) ? def : n;
    }

    function bool(v) {
        return v === '1' || v === 'true';
    }

    window.AUCTION_ID     = num(root.dataset.auctionId, 0);
    window.MIN_INCREMENT  = num(root.dataset.minIncrement, 0);
    window.BID_URL        = root.dataset.bidUrl || '';
    window.CSRF           = root.dataset.csrf || '';
    window.SELLER_ID      = num(root.dataset.sellerId, 0);
    window.REMAINING_SECS = num(root.dataset.remainingSecs, 0);
    window.LIVE_STATE_URL = root.dataset.liveStateUrl || '';
    window.CHAT_POLL_URL  = root.dataset.chatPollUrl || '';
    window.CHAT_STORE_URL = root.dataset.chatStoreUrl || '';
    window.IS_FINISHED    = bool(root.dataset.isFinished);
    window.USES_VIDEO     = bool(root.dataset.usesVideo);
    window.CHAT_LAST_ID   = 0;
    window.LAST_BID_ID    = num(root.dataset.lastBidId, 0);
    window._soldHandled   = bool(root.dataset.soldHandled);
    window.IS_AUTH        = bool(root.dataset.isAuth);
    window.CURRENT_USER_ID = window.IS_AUTH ? num(root.dataset.currentUserId, null) : null;
    window.currentMin     = num(root.dataset.currentMin, 0);
})();
