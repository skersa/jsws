/* Inertia dinamik yükleme köprüsü: DOM zaten hazırsa DOMContentLoaded
   callback'lerini kuyruğa al, dosya sonunda çalıştır (native davranış blade'de korunur). */
(function () {
    if (document.readyState === 'loading') return; // blade: normal akış
    window.__asQueue = [];
    window.__asQueuedInit = true;
    window.__asOrigAdd = document.addEventListener.bind(document);
    document.addEventListener = function (type, cb, opts) {
        if (type === 'DOMContentLoaded') { window.__asQueue.push(cb); return; }
        return window.__asOrigAdd(type, cb, opts);
    };
})();


/* ─── Satıldı / Geri Sayım ─── */
let _cdInterval = null;

function _showSellCountdown(seconds) {
    const bar  = document.getElementById('viewer-sell-bar');
    const text = document.getElementById('viewer-sell-bar-text');
    if (!bar) return;
    bar.style.display = 'flex';
    let rem = seconds;
    text.textContent = rem + ' saniye sonra satış tamamlanacak…';
    clearInterval(_cdInterval);
    _cdInterval = setInterval(() => {
        rem--;
        if (rem <= 0) { clearInterval(_cdInterval); bar.style.display = 'none'; return; }
        text.textContent = rem + ' saniye sonra satış tamamlanacak…';
    }, 1000);
}

function _hideSellCountdown() {
    clearInterval(_cdInterval);
    const bar = document.getElementById('viewer-sell-bar');
    if (bar) bar.style.display = 'none';
}

function _showSoldUi(buyerName, displayPrice) {
    _hideSellCountdown();
    // Overlay
    const overlay = document.getElementById('viewer-sold-overlay');
    if (overlay) {
        document.getElementById('viewer-sold-sub').textContent =
            (buyerName && displayPrice) ? buyerName + ' — ' + displayPrice : (buyerName || displayPrice || '—');
        overlay.style.display = 'flex';
    }
    // Teklif formunu kapat
    const formArea = document.querySelector('.bid-form-area');
    if (formArea) formArea.innerHTML = '<div class="alert alert-success mb-0" style="font-size:13px;border-radius:10px;margin:0;"><i class="bi bi-check-circle me-1"></i> Bu ürün satışa kapatıldı.</div>';
    // Timer durdur
    clearInterval(timerInt);
    ['live-timer','live-timer-mobile'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.textContent = 'Satıldı'; el.classList.remove('timer-critical'); el.style.color = '#10b981'; }
    });
    // Mobile sticky bar gizle
    document.querySelector('.bid-sticky-bar')?.remove();
    // Swal
    if (typeof Swal !== 'undefined' && buyerName) {
        Swal.fire({ toast:true, position:'top-end', icon:'success', title:'🎉 Satış Tamamlandı',
            text: buyerName + (displayPrice ? ' — ' + displayPrice : ''),
            showConfirmButton:false, timer:6000, timerProgressBar:true });
    }
}

/* ─── WebRTC ─── */
const ICE_SERVERS = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }, { urls: 'stun:stun1.l.google.com:19302' }] };
let peerConnection  = null;
let presenceChannel = null;
let streamMuted     = true;
const MEMBERS = new Set();
let presenceActive = false;
let pollInterval = null;
let chatInterval = null;

/* ─── Geri Sayım ─── */
let remainingSecs = REMAINING_SECS;
let timerInt = null;
function _startTimer() {
    const tick = () => {
        if (remainingSecs > 0) remainingSecs--;
        const m   = Math.floor(remainingSecs / 60);
        const sec = remainingSecs % 60;
        const txt = String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
        ['live-timer','live-timer-mobile'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = remainingSecs > 0 ? txt : 'Bitti';
            el.classList.toggle('timer-critical', remainingSecs <= 60 && remainingSecs > 0);
        });
        if (remainingSecs <= 0) clearInterval(timerInt);
    };
    if (remainingSecs <= 0) { ['live-timer','live-timer-mobile'].forEach(id => { const el=document.getElementById(id); if(el) el.textContent='Bitti'; }); return; }
    tick();
    timerInt = setInterval(tick, 1000);
}

/* ─── WebRTC izleyici tarafı ─── */
async function handleOffer(sdp) {
    // Önceki bağlantıyı temiz kapat
    if (peerConnection) {
        peerConnection.ontrack = null;
        peerConnection.onicecandidate = null;
        peerConnection.onconnectionstatechange = null;
        peerConnection.close();
        peerConnection = null;
    }

    peerConnection = new RTCPeerConnection(ICE_SERVERS);

    peerConnection.ontrack = (event) => {
        const video = document.getElementById('liveVideo');
        if (video.srcObject !== event.streams[0]) video.srcObject = event.streams[0];
        _showLiveStream();
    };

    peerConnection.onicecandidate = ({ candidate }) => {
        if (candidate && presenceChannel) {
            presenceChannel.whisper('webrtc-signal', {
                type        : 'ice-candidate',
                candidate   : candidate,
                targetUserId: SELLER_ID,
                fromUserId  : CURRENT_USER_ID,
            });
        }
    };

    peerConnection.onconnectionstatechange = () => {
        console.log('[Viewer] connectionState:', peerConnection?.connectionState);
        if (['disconnected', 'failed'].includes(peerConnection?.connectionState)) _hideLiveStream();
    };

    try {
        // Sadece offer tipinde sdp kabul et
        await peerConnection.setRemoteDescription(new RTCSessionDescription({ type: 'offer', sdp: sdp.sdp ?? sdp }));
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        if (presenceChannel) {
            presenceChannel.whisper('webrtc-signal', {
                type        : 'answer',
                sdp         : answer,
                targetUserId: SELLER_ID,
                fromUserId  : CURRENT_USER_ID,
            });
        }
        console.log('[Viewer] Answer gönderildi → satıcıya');
    } catch (err) {
        console.error('[Viewer] handleOffer hatası:', err);
    }
}

async function handleSellerIce(candidate) {
    if (!peerConnection) return;
    if (peerConnection.remoteDescription === null) {
        console.warn('[Viewer] ICE geldi ama remoteDescription henüz yok, bekleniyor...');
        return;
    }
    try {
        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
    } catch(e) {
        console.warn('[Viewer] addIceCandidate hatası:', e);
    }
}

function _showLiveStream() {
    const video = document.getElementById('liveVideo');
    video.style.display = 'block';
    document.getElementById('cam-off-state').style.display = 'none';
    document.getElementById('stream-live-pill').style.display = 'inline-flex';
    document.getElementById('vol-btn').style.display = 'inline-flex';
    document.getElementById('fs-btn').style.display  = 'inline-flex';
    document.getElementById('vol-icon').className = 'bi bi-volume-mute';
}

function _hideLiveStream() {
    const video = document.getElementById('liveVideo');
    video.style.display = 'none'; video.srcObject = null;
    document.getElementById('cam-off-state').style.display = 'flex';
    document.getElementById('stream-live-pill').style.display = 'none';
    document.getElementById('vol-btn').style.display = 'none';
    document.getElementById('fs-btn').style.display  = 'none';
}

function toggleStreamVolume() {
    const video = document.getElementById('liveVideo');
    video.muted = !video.muted;
    streamMuted = video.muted;
    document.getElementById('vol-icon').className = video.muted ? 'bi bi-volume-mute' : 'bi bi-volume-up';
}

function toggleFullscreen() {
    const section = document.querySelector('.camera-section');
    if (!document.fullscreenElement) {
        section.requestFullscreen?.();
        document.getElementById('fs-icon').className = 'bi bi-fullscreen-exit';
    } else {
        document.exitFullscreen?.();
        document.getElementById('fs-icon').className = 'bi bi-fullscreen';
    }
}

/* ─── Viewer count ─── */
function setViewerCount(n) {
    ['viewer-count','viewer-count-stream','live-viewer-stat'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = n;
    });
}

/* Presence üyelerinden izleyici sayısını hesapla (satıcı hariç, tekilleştirilmiş) */
function _recountViewers() {
    let count = 0;
    MEMBERS.forEach(id => { if (id !== SELLER_ID) count++; });
    setViewerCount(count);
}

/* Canlı yayın sona erdi — socket & polling durdur, kaynak tasarrufu */
function _teardownLive() {
    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
    if (peerConnection) { try { peerConnection.close(); } catch(e){} peerConnection = null; }
    if (presenceChannel && window.Echo) {
        try { window.Echo.leave(`auction.${AUCTION_ID}`); } catch(e){}
        presenceChannel = null;
        presenceActive  = false;
    }
    if (!USES_VIDEO) _hideLiveStream();
    // İzleyici sayacını gizle
    document.querySelectorAll('.viewer-pill').forEach(el => el.style.display = 'none');
    _updateStreamTab(false);
}

/* "Canlı İzle" sekmesini yalnızca yayın açıkken göster (video modu hariç). */
function _updateStreamTab(isLive) {
    const btn = document.getElementById('tab-stream');
    if (!btn) return;
    if (USES_VIDEO) { btn.style.display = ''; return; } // tanıtım videosu her zaman erişilebilir
    if (isLive) {
        btn.style.display = '';
    } else {
        btn.style.display = 'none';
        // Yayın kapanınca canlı sekmesindeysek fotoğraflara dön
        const panel = document.getElementById('panel-stream');
        if (panel && panel.classList.contains('active')) switchTab('gallery');
    }
}

/* ─── Echo / Reverb init ─── */
document.addEventListener('DOMContentLoaded', () => {
    // Geri sayımı başlat
    _startTimer();

    // Yayın bitmişse: socket/WebRTC hiç başlatma (kaynak tasarrufu)
    if (IS_FINISHED) {
        document.querySelectorAll('.viewer-pill').forEach(el => el.style.display = 'none');
        return;
    }

    if (typeof window.Echo === 'undefined') {
        // Reverb/WebSocket bu ortamda devre dışı; canlı akış polling ile sağlanıyor.
        return;
    }

    if (IS_AUTH) {
        /* Giriş yapmış kullanıcı — presence kanalı */
        presenceChannel = window.Echo.join(`auction.${AUCTION_ID}`);
        presenceActive  = true;

        presenceChannel
            .here((users) => {
                MEMBERS.clear();
                users.forEach(u => MEMBERS.add(parseInt(u.id, 10)));
                _recountViewers();
            })
            .joining((user) => {
                MEMBERS.add(parseInt(user.id, 10));
                _recountViewers();
            })
            .leaving((user) => {
                MEMBERS.delete(parseInt(user.id, 10));
                _recountViewers();
            })
            .listen('.bid.placed', (data) => {
                if (data.bidder_id && data.bidder_id === CURRENT_USER_ID) return;
                addBidToFeed(data.bidder_name, data.amount, false);
                updateStats(data);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'info',
                        title: `🔴 ${data.bidder_name}`,
                        text : `${data.display_price} teklif verdi`,
                        showConfirmButton: false, timer: 4000, timerProgressBar: true,
                    });
                }
            })
            .listen('.auction.sold', (data) => {
                _showSoldUi(data.buyer_name, data.display_price);
                _teardownLive();
            })
            .listen('.chat.message', (data) => {
                renderChatMessage(data);
                if (data.id > CHAT_LAST_ID) CHAT_LAST_ID = data.id;
            })
            .listenForWhisper('sell-countdown', (data) => {
                data.cancelled ? _hideSellCountdown() : _showSellCountdown(data.seconds ?? 3);
            });

        // WebRTC yalnızca canlı yayın modunda (tanıtım videosunda gerekmez)
        if (!USES_VIDEO) {
            presenceChannel.listenForWhisper('webrtc-signal', async (data) => {
                if (data.targetUserId !== CURRENT_USER_ID) return;
                if (data.type === 'offer') {
                    await handleOffer(data.sdp);
                } else if (data.type === 'ice-candidate') {
                    await handleSellerIce(data.candidate);
                }
            });
        }

    } else {
        /* Giriş yapmamış kullanıcı — public kanal */
        window.Echo.channel(`auction.${AUCTION_ID}`)
            .listen('.bid.placed', (data) => {
                addBidToFeed(data.bidder_name, data.amount, false);
                updateStats(data);
            })
            .listen('.auction.sold', (data) => {
                _showSoldUi(data.buyer_name, data.display_price);
            });
        setViewerCount('?');
    }
});

/* ─── Teklif Gönder ─── */
function setQuick(amount) {
    const input = document.getElementById('bid-input');
    if (input) input.value = amount;
}

async function submitBid() {
    await _doSubmit(
        document.getElementById('bid-input'),
        document.getElementById('bid-btn'),
        document.getElementById('bid-btn-text'),
        document.getElementById('bid-error')
    );
}
async function submitBidMobile() {
    await _doSubmit(
        document.getElementById('bid-input-mobile'),
        null, null,
        document.getElementById('bid-error-mobile')
    );
}

async function _doSubmit(input, btn, btnTxt, errEl) {
    const amount = parseFloat(input?.value);
    if (errEl) errEl.style.display = 'none';
    if (!amount || amount < currentMin) {
        if (errEl) { errEl.textContent = `En az ${currentMin.toLocaleString('tr-TR')} ₺ girmelisiniz.`; errEl.style.display = 'block'; }
        return;
    }
    if (btn) btn.disabled = true;
    if (btnTxt) btnTxt.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Gönderiliyor...';

    try {
        const res  = await fetch(BID_URL, {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body    : JSON.stringify({ amount }),
        });
        const data = await res.json();
        if (!res.ok) {
            if (errEl) { errEl.textContent = data.message ?? 'Bir hata oluştu.'; errEl.style.display = 'block'; }
        } else {
            if (input) input.value = '';
            addBidToFeed(data.bidder_name, data.amount, true);
            updateStats(data);
        }
    } catch(e) {
        if (errEl) { errEl.textContent = 'Bağlantı hatası. Tekrar deneyin.'; errEl.style.display = 'block'; }
    }

    if (btn) btn.disabled = false;
    if (btnTxt) btnTxt.innerHTML = '<i class="bi bi-lightning-charge-fill"></i> Teklif Ver';
}

/* ─── Teklif Feed ─── */
function addBidToFeed(name, amount, isMine = false) {
    const feed = document.getElementById('bid-feed');
    document.getElementById('feed-empty')?.remove();

    feed.querySelectorAll('.bid-rank').forEach((el, i) => {
        el.textContent = i + 2;
        el.className   = 'bid-rank ' + (i===0?'r2':i===1?'r3':'rn');
    });
    feed.querySelectorAll('.top-label').forEach(el => el.remove());
    feed.querySelectorAll('.bid-item.bid-top').forEach(el => el.classList.remove('bid-top'));

    const color = isMine ? '10b981' : '155eef';
    const item  = document.createElement('div');
    item.className = 'bid-item bid-new bid-top';
    item.innerHTML = `
        <span class="top-label">En Yüksek</span>
        <span class="bid-rank r1">1</span>
        <img class="bid-avatar"
             src="https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=32&background=${color}&color=fff">
        <div style="flex:1;min-width:0;">
            <div class="bid-name">${name}${isMine?' <span style="font-size:10px;color:#10b981;">(sen)</span>':''}</div>
            <div class="bid-time">az önce</div>
        </div>
        <div class="bid-amount">${parseFloat(amount).toLocaleString('tr-TR',{minimumFractionDigits:0})} ₺</div>
    `;
    feed.insertBefore(item, feed.firstChild);
    const items = feed.querySelectorAll('.bid-item');
    if (items.length > 15) items[items.length-1].remove();
    document.querySelector('.feed-scroll')?.scrollTo({ top:0, behavior:'smooth' });
}

function updateStats(data) {
    ['live-price','live-price-mobile'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = data.display_price;
        el.classList.remove('price-flash');
        void el.offsetWidth;
        el.classList.add('price-flash');
    });
    const countEl = document.getElementById('live-bid-count');
    if (countEl) countEl.textContent = data.total_bids;
    const badgeEl = document.getElementById('bid-count-badge');
    if (badgeEl) badgeEl.textContent = data.total_bids + ' teklif';
    currentMin = parseFloat(data.amount) + MIN_INCREMENT;
    ['bid-input','bid-input-mobile'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.min = currentMin; el.placeholder = `Min: ${currentMin.toLocaleString('tr-TR')} ₺`; }
    });
    const btns  = document.querySelectorAll('.quick-btn');
    const steps = [0, 4, 9];
    btns.forEach((btn, i) => {
        const val   = currentMin + (MIN_INCREMENT * steps[i]);
        const extra = MIN_INCREMENT * (steps[i] + 1);
        btn.innerHTML = `+${extra.toLocaleString('tr-TR')} ₺<span>${val.toLocaleString('tr-TR')} ₺</span>`;
        btn.onclick   = () => setQuick(val);
    });
}

/* ─── Galeri ─── */
function switchTab(tab) {
    document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}
function switchImg(el, src) {
    document.getElementById('mainImg').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}
document.addEventListener('keydown', e => { if(e.key==='Escape') closeLightbox(); });

/* ─── Klavye ─── */
document.getElementById('bid-input')?.addEventListener('keydown', e => { if (e.key === 'Enter') submitBid(); });
document.getElementById('bid-input-mobile')?.addEventListener('keydown', e => { if (e.key === 'Enter') submitBidMobile(); });

/* ─── Canlı durum (polling fallback) — WebSocket olmadan gerçek-zamanlı akış ─── */
async function pollLiveState() {
    try {
        const res = await fetch(LIVE_STATE_URL + '?after=' + LAST_BID_ID, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;
        const d = await res.json();

        // İzleyici sayısını yalnızca presence (socket) aktif değilken sunucudan al
        if (!presenceActive && typeof d.viewer_count !== 'undefined') setViewerCount(d.viewer_count);

        // Canlı İzle sekmesini yayın durumuna göre göster/gizle
        if (typeof d.is_live !== 'undefined') _updateStreamTab(!!d.is_live);

        let hadNew = false;
        if (Array.isArray(d.new_bids)) {
            d.new_bids.forEach(b => {
                if (b.bid_id > LAST_BID_ID) LAST_BID_ID = b.bid_id;
                if (b.bidder_id === CURRENT_USER_ID) return; // kendi teklifim zaten eklendi
                addBidToFeed(b.bidder_name, b.amount, false);
                hadNew = true;
            });
        }
        if (hadNew) {
            updateStats({ display_price: d.display_price, total_bids: d.total_bids, amount: d.current_price });
        }

        // Canlı yayın kapandıysa video/socket temizle (kaynak tasarrufu)
        if (typeof d.is_live !== 'undefined' && !d.is_live && !USES_VIDEO) {
            _hideLiveStream();
        }

        if (d.sold && !_soldHandled && (d.status === 'sold')) {
            _soldHandled = true;
            _showSoldUi(d.sold.buyer_name, d.sold.display_price);
            _teardownLive();
        } else if (d.status === 'ended' && !_soldHandled) {
            _soldHandled = true;
            clearInterval(timerInt);
            ['live-timer','live-timer-mobile'].forEach(id => { const el=document.getElementById(id); if(el){ el.textContent='Bitti'; } });
            _teardownLive();
        }
    } catch (e) { /* sessiz geç */ }
}

/* ─── Canlı Sohbet (satıcıya sor) ─── */
function renderChatMessage(m) {
    const box = document.getElementById('chatMessages');
    if (!box) return;
    document.getElementById('chatEmpty')?.remove();
    const wrap = document.createElement('div');
    wrap.style.cssText = 'padding:6px 4px;font-size:13px;line-height:1.45;';
    const nameColor = m.is_seller ? '#10b981' : '#155eef';
    const badge = m.is_seller ? ' <span style="font-size:9px;background:#10b981;color:#fff;padding:1px 5px;border-radius:6px;">SATICI</span>' : '';
    wrap.innerHTML = '<span style="font-weight:700;color:'+nameColor+';">'+_escChat(m.user_name)+'</span>'+badge+' <span style="color:var(--text);">'+_escChat(m.message)+'</span>';
    box.appendChild(wrap);
    box.scrollTop = box.scrollHeight;
}
function _escChat(str){ return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

async function pollChat() {
    try {
        const res = await fetch(CHAT_POLL_URL + '?after=' + CHAT_LAST_ID, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) return;
        const d = await res.json();
        (d.messages || []).forEach(m => {
            if (m.id > CHAT_LAST_ID) { CHAT_LAST_ID = m.id; renderChatMessage(m); }
        });
    } catch (e) { /* sessiz */ }
}

async function sendChat(e) {
    e.preventDefault();
    const input = document.getElementById('chatInput');
    const errEl = document.getElementById('chatError');
    const text = (input.value || '').trim();
    if (errEl) errEl.style.display = 'none';
    if (!text) return;
    input.disabled = true;
    try {
        const res = await fetch(CHAT_STORE_URL, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body   : JSON.stringify({ message: text }),
        });
        const d = await res.json().catch(() => ({}));
        if (res.ok) {
            input.value = '';
            if (d.id > CHAT_LAST_ID) { CHAT_LAST_ID = d.id; renderChatMessage(d); }
        } else if (errEl) {
            errEl.textContent = d.message || 'Mesaj gönderilemedi.';
            errEl.style.display = 'block';
        }
    } catch (err) { /* sessiz */ }
    input.disabled = false;
    input.focus();
}

document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chatForm');
    if (chatForm) chatForm.addEventListener('submit', sendChat);

    // Sohbet geçmişini yükle (yayın bitse bile geçmiş görünür)
    pollChat();

    if (IS_FINISHED) {
        // Yayın bitti: sürekli polling yok, tek sefer durum al
        pollLiveState();
        return;
    }
    pollLiveState();
    pollInterval = setInterval(pollLiveState, 2500);
    chatInterval = setInterval(pollChat, 3000);
});

/* Inertia köprüsü: init'i tekrar çağrılabilir yap + tam temizlik kancası */
(function () {
    var q = window.__asQueue || [];
    if (window.__asOrigAdd) {
        document.addEventListener = window.__asOrigAdd;
        window.__asOrigAdd = null;
    }
    window.__auctionShowCleanup = function () {
        try { clearInterval(pollInterval); } catch (e) {}
        try { clearInterval(chatInterval); } catch (e) {}
        try { clearInterval(_cdInterval); } catch (e) {}
        try { clearInterval(timerInt); } catch (e) {}
    };
    window.__auctionShowInit = function () {
        try { window.__auctionShowCleanup(); } catch (e) {}
        q.forEach(function (cb) { try { cb(); } catch (e) {} });
    };
    if (window.__asQueuedInit) { window.__auctionShowInit(); }
})();
