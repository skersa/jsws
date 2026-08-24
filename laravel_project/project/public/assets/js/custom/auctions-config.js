/**
 * Açık artırma (canlı) sayfası — konfigürasyon köprüsü
 * Blade'deki #auctionConfigRoot data-* niteliklerinden LB_CONFIG ve
 * ICE_SERVERS global değişkenlerini oluşturur.
 *
 * Bu dosyanın devamındaki gerçek yayın/teklif mantığı canlı yayın kodları
 * (live-broadcast.js vb.) tarafından okunur.
 */
(function () {
    'use strict';

    var root = document.getElementById('auctionConfigRoot');
    if (!root) return;

    function num(v, def) {
        var n = parseInt(v, 10);
        return isNaN(n) ? def : n;
    }

    function bool(v) {
        return v === '1' || v === 'true';
    }

    window.LB_CONFIG = Object.freeze({
        auctionId          : num(root.dataset.auctionId, 0),
        sellEndpoint       : root.dataset.sellEndpoint || '',
        endEndpoint        : root.dataset.endEndpoint || '',
        csrfToken          : root.dataset.csrfToken || '',
        remainingSecs      : num(root.dataset.remainingSecs, 0),
        topBidId           : root.dataset.topBidId ? num(root.dataset.topBidId, null) : null,
        topBidName         : root.dataset.topBidName || '',
        topBidAmount       : num(root.dataset.topBidAmount, 0),
        userId             : num(root.dataset.userId, 0),
        isSold             : bool(root.dataset.isSold),
        liveStateUrl       : root.dataset.liveStateUrl || '',
        liveStatusUrl      : root.dataset.liveStatusUrl || '',
        chatPollUrl        : root.dataset.chatPollUrl || '',
        chatStoreUrl       : root.dataset.chatStoreUrl || '',
        userName           : root.dataset.userName || '',
        lastBidId          : num(root.dataset.lastBidId, 0),
        sellerDashboardUrl : root.dataset.sellerDashboardUrl || '',
        bidCount           : num(root.dataset.bidCount, 0)
    });

    window.ICE_SERVERS = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ]
    };
})();
