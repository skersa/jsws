const LB = (() => {
    'use strict';

    const s = {
        cameraOn       : false,
        micOn          : false,
        screenOn       : false,
        facingMode     : 'user',
        selectedBidId  : LB_CONFIG.topBidId,
        selectedName   : LB_CONFIG.topBidName,
        selectedAmount : LB_CONFIG.topBidAmount,
        selling        : false,
        sellInterval   : null,
        sellTimeout    : null,
        timerInterval  : null,
        remainingSecs  : LB_CONFIG.remainingSecs,
        mediaStream    : null,
        avatarIdx      : LB_CONFIG.bidCount,
        peers          : {},
        presenceChannel: null,
        presenceActive : false,
        pollTimer      : null,
        chatTimer      : null,
        chatLastId     : 0,
        memberIds      : new Set(),
    };

    const AVATAR_CLASSES = ['lb-av-purple','lb-av-green','lb-av-amber','lb-av-pink','lb-av-blue'];

    /* ════ KAMERA ════ */
    async function toggleCamera() {
        s.cameraOn ? stopCamera() : await startCamera();
    }

    async function startCamera(facingOverride) {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            _showToast('Kamera Açılamadı', 'Tarayıcın kamera erişimini desteklemiyor ya da sayfa güvenli (HTTPS) değil.');
            return;
        }
        try {
            let stream;
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video : { facingMode: facingOverride ?? s.facingMode, width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio : true,
                });
            } catch (inner) {
                if (inner && (inner.name === 'NotFoundError' || inner.name === 'NotReadableError' || inner.name === 'OverconstrainedError')) {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video : { facingMode: facingOverride ?? s.facingMode },
                        audio : false,
                    });
                } else {
                    throw inner;
                }
            }
            _applyStream(stream);
            s.micOn = stream.getAudioTracks().length > 0;
            document.getElementById('camFlipBtn').style.display = '';
            _offerToAllViewers();
        } catch (err) {
            const map = {
                NotAllowedError    : 'Kamera/mikrofon izni reddedildi. Tarayıcı adres çubuğundaki kilit simgesinden izin ver.',
                PermissionDeniedError: 'Kamera/mikrofon izni reddedildi. Lütfen izin ver.',
                NotFoundError      : 'Cihazında kamera bulunamadı.',
                NotReadableError   : 'Kamera başka bir uygulama tarafından kullanılıyor olabilir.',
                OverconstrainedError: 'Kamera bu ayarları desteklemiyor.',
                SecurityError      : 'Güvenlik nedeniyle kameraya erişilemedi (HTTPS gerekli).',
            };
            _showToast('Kamera Hatası', map[err && err.name] || (err && err.message) || 'Kamera açılamadı.');
            console.error('[LB] startCamera hatası:', err);
        }
    }

    function _applyStream(stream) {
        if (s.mediaStream) s.mediaStream.getTracks().forEach(t => t.stop());
        s.mediaStream = stream;
        s.cameraOn    = true;
        const video = document.getElementById('videoStream');
        video.srcObject = stream;
        video.classList.add('active');
        document.getElementById('camOffState').classList.add('hidden');
        document.getElementById('camBtn').classList.add('lb-cam-on');
        document.getElementById('camBtnLabel').textContent = 'Kamerayı Kapat';
        document.getElementById('liveOverlay').style.display = '';
        document.getElementById('liveBadge').style.display   = '';
        _setLiveStatus(true);
    }

    function _setLiveStatus(live) {
        fetch(LB_CONFIG.liveStatusUrl, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': LB_CONFIG.csrfToken, 'Accept': 'application/json' },
            body   : JSON.stringify({ live: live }),
        }).catch(() => {});
    }

    function stopCamera() {
        if (s.mediaStream) { s.mediaStream.getTracks().forEach(t => t.stop()); s.mediaStream = null; }
        s.cameraOn = false; s.micOn = false; s.screenOn = false;
        Object.values(s.peers).forEach(pc => pc.close());
        s.peers = {};
        const video = document.getElementById('videoStream');
        video.srcObject = null; video.classList.remove('active');
        document.getElementById('camOffState').classList.remove('hidden');
        document.getElementById('camBtn').classList.remove('lb-cam-on');
        document.getElementById('camBtnLabel').textContent = 'Kamera Başlat';
        document.getElementById('liveOverlay').style.display = 'none';
        document.getElementById('liveBadge').style.display   = 'none';
        document.getElementById('camFlipBtn').style.display  = 'none';
        document.getElementById('screenBtn').classList.remove('lb-ctrl-active');
        document.getElementById('screenLabel').textContent = 'Ekran Paylaş';
        document.getElementById('micBtn').classList.remove('lb-ctrl-danger');
        document.getElementById('micIcon').className   = 'bi bi-mic';
        document.getElementById('micLabel').textContent = 'Mikrofon';
        _setLiveStatus(false);
    }

    async function flipCamera() {
        s.facingMode = s.facingMode === 'user' ? 'environment' : 'user';
        await startCamera(s.facingMode);
    }

    function toggleMic() {
        if (!s.mediaStream) return;
        const track = s.mediaStream.getAudioTracks()[0];
        if (!track) return;
        s.micOn = !s.micOn;
        track.enabled = s.micOn;
        const btn = document.getElementById('micBtn');
        const icon = document.getElementById('micIcon');
        const label = document.getElementById('micLabel');
        if (s.micOn) { btn.classList.remove('lb-ctrl-danger'); icon.className = 'bi bi-mic'; label.textContent = 'Mikrofon'; }
        else { btn.classList.add('lb-ctrl-danger'); icon.className = 'bi bi-mic-mute'; label.textContent = 'Sessiz'; }
    }

    async function toggleScreen() {
        if (s.screenOn) { stopCamera(); return; }
        try {
            const stream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: true });
            _applyStream(stream);
            s.screenOn = true;
            document.getElementById('screenBtn').classList.add('lb-ctrl-active');
            document.getElementById('screenLabel').textContent = 'Paylaşımı Durdur';
            stream.getVideoTracks()[0].onended = stopCamera;
            _offerToAllViewers();
        } catch (err) { /* kullanıcı iptal etti */ }
    }

    /* ════ WEBRTC — SATICI TARAFI ════ */
    function _offerToAllViewers() {
        s.memberIds.forEach(uid => {
            if (uid !== LB_CONFIG.userId) {
                _createOfferForViewer(uid);
            }
        });
    }

    async function _createOfferForViewer(viewerUserId) {
        if (s.peers[viewerUserId]) { s.peers[viewerUserId].close(); }

        const pc = new RTCPeerConnection(ICE_SERVERS);
        s.peers[viewerUserId] = pc;

        if (s.mediaStream) {
            s.mediaStream.getTracks().forEach(track => pc.addTrack(track, s.mediaStream));
        }

        pc.onicecandidate = ({ candidate }) => {
            if (candidate && s.presenceChannel) {
                s.presenceChannel.whisper('webrtc-signal', {
                    type        : 'ice-candidate',
                    candidate   : candidate,
                    targetUserId: viewerUserId,
                    fromUserId  : LB_CONFIG.userId,
                });
            }
        };

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);

        if (s.presenceChannel) {
            s.presenceChannel.whisper('webrtc-signal', {
                type        : 'offer',
                sdp         : offer,
                targetUserId: viewerUserId,
                fromUserId  : LB_CONFIG.userId,
            });
        }
    }

    async function _handleAnswer(viewerUserId, sdp) {
        const pc = s.peers[viewerUserId];
        if (!pc) {
            console.warn('[LB] _handleAnswer: peer yok, userId:', viewerUserId);
            return;
        }
        if (pc.signalingState !== 'have-local-offer') {
            console.warn('[LB] _handleAnswer: yanlış state:', pc.signalingState, 'userId:', viewerUserId);
            return;
        }
        try {
            await pc.setRemoteDescription(new RTCSessionDescription({ type: 'answer', sdp: sdp.sdp ?? sdp }));
            console.log('[LB] Answer alındı, izleyici:', viewerUserId);
        } catch (err) {
            console.error('[LB] _handleAnswer hatası:', err);
        }
    }

    async function _handleViewerIce(viewerUserId, candidate) {
        const pc = s.peers[viewerUserId];
        if (!pc) return;
        if (pc.remoteDescription === null) {
            console.warn('[LB] ICE geldi ama remoteDescription henüz yok, userId:', viewerUserId);
            return;
        }
        try { await pc.addIceCandidate(new RTCIceCandidate(candidate)); } catch(e) {
            console.warn('[LB] addIceCandidate hatası:', e);
        }
    }

    /* ════ TEKLİF SEÇ ════ */
    function selectBid(row) {
        if (s.selling) return;
        document.querySelectorAll('#bidList .lb-bid-radio').forEach(r => r.classList.remove('lb-selected'));
        row.querySelector('.lb-bid-radio').classList.add('lb-selected');
        s.selectedBidId  = parseInt(row.dataset.bidId, 10);
        s.selectedName   = row.dataset.name;
        s.selectedAmount = parseInt(row.dataset.amount, 10);
        document.getElementById('selectedLabel').textContent =
            s.selectedName + ' — ' + s.selectedAmount.toLocaleString('tr-TR') + ' ₺';
        _resetSellBtn();
    }

    /* ════ SATIŞ AKIŞI ════ */
    function startSell() {
        if (s.selling || !s.selectedBidId) return;
        s.selling = true;
        const DURATION = 3000; const STEPS = 100; const INTERVAL = DURATION / STEPS;
        let step = 0;
        const btn  = document.getElementById('sellBtn');
        const cbar = document.getElementById('sellCbar');
        btn.className = 'lb-sell-btn lb-sell-confirming';
        btn.disabled  = true;
        btn.onclick   = _cancelSell;
        const _updateLabel = (r) => { document.getElementById('sellBtnText').textContent = 'Satılıyor... İptal için tıkla (' + r + ')'; };
        _updateLabel(3);
        s.presenceChannel?.whisper('sell-countdown', { seconds: 3, cancelled: false });
        s.sellInterval = setInterval(() => {
            step++;
            cbar.style.width = (step / STEPS * 100) + '%';
            _updateLabel(Math.ceil((DURATION - step * INTERVAL) / 1000));
            if (step >= STEPS) { clearInterval(s.sellInterval); s.sellInterval = null; }
        }, INTERVAL);
        s.sellTimeout = setTimeout(_completeSell, DURATION);
    }

    function _cancelSell() {
        clearInterval(s.sellInterval); s.sellInterval = null;
        clearTimeout(s.sellTimeout);   s.sellTimeout  = null;
        s.selling = false;
        document.getElementById('sellCbar').style.width = '0';
        s.presenceChannel?.whisper('sell-countdown', { cancelled: true });
        _resetSellBtn();
    }

    function _resetSellBtn() {
        const btn = document.getElementById('sellBtn');
        btn.className = 'lb-sell-btn';
        btn.disabled  = !s.selectedBidId;
        btn.onclick   = LB.startSell;
        document.getElementById('sellBtnText').textContent = 'Bu Teklife Sat';
        document.getElementById('sellBtnIcon').className   = 'bi bi-check-lg';
    }

    async function _completeSell() {
        s.selling = false;
        const btn  = document.getElementById('sellBtn');
        const cbar = document.getElementById('sellCbar');
        btn.className = 'lb-sell-btn lb-sell-done';
        btn.disabled  = true;
        document.getElementById('sellBtnText').textContent = 'Satış Tamamlandı!';
        document.getElementById('sellBtnIcon').className   = 'bi bi-check-circle';
        cbar.classList.add('lb-cbar-done');
        cbar.style.width = '100%';
        _showToast('Satış Tamamlandı! 🎉', s.selectedName + ' — ' + s.selectedAmount.toLocaleString('tr-TR') + ' ₺');
        _lockUiAfterSale();

        try {
            const res = await fetch(LB_CONFIG.sellEndpoint, {
                method  : 'POST',
                headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': LB_CONFIG.csrfToken, 'Accept': 'application/json' },
                body    : JSON.stringify({ bid_id: s.selectedBidId }),
            });
            const data = await res.json().catch(() => ({}));
            console.log('[LB] Satış response:', res.status, data);
            if (!res.ok) {
                _showToast('Sunucu Hatası', data.message ?? 'Satış kaydedilemedi.');
            }
        } catch (err) {
            console.error('[LB] Satış fetch hatası:', err);
            _showToast('Bağlantı Hatası', 'Sunucuya ulaşılamadı.');
        }
    }

    /* ════ SATIŞ SONRASI UI KİLİTLE ════ */
    function _lockUiAfterSale() {
        stopCamera();

        const camBtn = document.getElementById('camBtn');
        if (camBtn) { camBtn.disabled = true; camBtn.style.opacity = '.4'; camBtn.style.cursor = 'not-allowed'; camBtn.onclick = null; }

        const sellBtn = document.getElementById('sellBtn');
        if (sellBtn) {
            sellBtn.disabled = true;
            sellBtn.onclick  = null;
            sellBtn.className = 'lb-sell-btn lb-sell-done';
            sellBtn.style.opacity = '.45';
            sellBtn.style.cursor  = 'not-allowed';
            document.getElementById('sellBtnText').textContent = 'Satış Tamamlandı';
            document.getElementById('sellBtnIcon').className   = 'bi bi-check-circle';
        }

        document.querySelectorAll('.lb-ctrl-btn').forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '.4';
            btn.style.cursor  = 'not-allowed';
            btn.onclick = null;
        });

        document.querySelectorAll('#bidList .lb-bid-row').forEach(row => {
            row.onclick = null;
            row.style.cursor = 'default';
        });

        if (s.timerInterval) { clearInterval(s.timerInterval); s.timerInterval = null; }
        const timerEl = document.getElementById('auctionTimer');
        if (timerEl) { timerEl.textContent = 'Satıldı'; timerEl.classList.remove('lb-timer-safe'); timerEl.style.color = '#10b981'; }

        const wrap = document.getElementById('videoWrap');
        if (wrap) {
            const banner = document.createElement('div');
            banner.style.cssText = 'position:absolute;inset:0;background:rgba(0,0,0,.7);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;z-index:10;border-radius:14px;';
            banner.innerHTML = `
                <div style="font-size:48px;">🎉</div>
                <div style="font-size:22px;font-weight:800;color:#10b981;">Satış Tamamlandı!</div>
                <div style="font-size:14px;color:rgba(255,255,255,.7);">${_esc(s.selectedName)} — ${s.selectedAmount.toLocaleString('tr-TR')} ₺</div>
            `;
            wrap.appendChild(banner);
        }
    }

    /* ════ TOAST ════ */
    function _showToast(title, sub) {
        document.getElementById('toastTitle').textContent = title;
        document.getElementById('toastSub').textContent   = sub;
        const toast = document.getElementById('soldToast');
        toast.classList.add('lb-toast-show');
        setTimeout(() => toast.classList.remove('lb-toast-show'), 5000);
    }

    /* ════ GERİ SAYIM ════ */
    function _startTimer() {
        const el = document.getElementById('auctionTimer');
        const _tick = () => {
            if (s.remainingSecs > 0) s.remainingSecs--;
            const m   = Math.floor(s.remainingSecs / 60);
            const sec = s.remainingSecs % 60;
            el.textContent = String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
            el.classList.toggle('lb-timer-safe', s.remainingSecs > 120);
            if (s.remainingSecs <= 0) { clearInterval(s.timerInterval); el.textContent = 'Bitti'; el.classList.remove('lb-timer-safe'); }
        };
        if (s.remainingSecs <= 0) { el.textContent = 'Bitti'; return; }
        _tick();
        s.timerInterval = setInterval(_tick, 1000);
    }

    /* ════ TEKLİF SATIRI EKLE ════ */
    function _addBidRow(bidId, name, amount, timeLabel) {
        const list = document.getElementById('bidList');
        document.getElementById('bidListEmpty')?.remove();

        const avClass  = AVATAR_CLASSES[s.avatarIdx % AVATAR_CLASSES.length];
        s.avatarIdx++;
        const parts    = name.trim().split(' ');
        const initials = (parts[0]?.[0] ?? 'X').toUpperCase() + (parts[1]?.[0] ?? 'X').toUpperCase();

        const row = document.createElement('div');
        row.className     = 'lb-bid-row lb-bid-new';
        row.dataset.bidId = bidId;
        row.dataset.amount = amount;
        row.dataset.name   = name;
        row.onclick        = () => LB.selectBid(row);
        row.innerHTML = `
            <div class="lb-bid-radio" id="radio-${bidId}"></div>
            <div class="lb-bid-avatar ${avClass}">${_esc(initials)}</div>
            <div style="flex:1;min-width:0;">
                <div class="lb-bid-name">${_esc(name)}</div>
                <div class="lb-bid-time">${_esc(timeLabel)}</div>
            </div>
            <div class="lb-bid-amount">${parseInt(amount,10).toLocaleString('tr-TR')} ₺</div>
        `;
        list.insertBefore(row, list.firstChild);

        const count = list.querySelectorAll('.lb-bid-row').length;
        document.getElementById('bidCountLabel').textContent  = count + ' teklif';
        document.getElementById('bidCountInline').textContent = count;

        document.getElementById('topBidPrice').textContent =
            parseInt(amount, 10).toLocaleString('tr-TR') + ' ₺';

        if (!s.selectedBidId) LB.selectBid(row);
        document.getElementById('sellBtn').disabled = false;
    }

    function _esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ════ ECHO / REVERB ════ */
    function _initEcho() {
        if (typeof window.Echo === 'undefined') {
            return;
        }

        s.presenceChannel = window.Echo.join('auction.' + LB_CONFIG.auctionId);
        s.presenceActive  = true;

        s.presenceChannel
            .here((users) => {
                users.forEach(u => s.memberIds.add(parseInt(u.id, 10)));
                const viewers = users.filter(u => parseInt(u.id,10) !== LB_CONFIG.userId).length;
                _setViewers(viewers);
                console.log('[LB] Kanalda', users.length, 'üye:', users.map(u => u.id));
            })
            .joining((user) => {
                const uid = parseInt(user.id, 10);
                s.memberIds.add(uid);
                if (uid === LB_CONFIG.userId) return;
                const viewers = [...s.memberIds].filter(id => id !== LB_CONFIG.userId).length;
                _setViewers(viewers);
                console.log('[LB] Katıldı:', uid, '→ izleyici:', viewers);
                if (s.cameraOn) {
                    setTimeout(() => _createOfferForViewer(uid), 800);
                }
            })
            .leaving((user) => {
                const uid = parseInt(user.id, 10);
                s.memberIds.delete(uid);
                if (uid === LB_CONFIG.userId) return;
                const viewers = [...s.memberIds].filter(id => id !== LB_CONFIG.userId).length;
                _setViewers(Math.max(0, viewers));
                if (s.peers[uid]) { s.peers[uid].close(); delete s.peers[uid]; }
            })
            .listen('.bid.placed', (data) => {
                console.log('[LB] Yeni teklif:', data);
                _addBidRow(data.bid_id, data.bidder_name, data.amount, 'az önce');
            })
            .listen('.chat.message', (data) => {
                _renderChatMsg(data);
                if (data.id > s.chatLastId) s.chatLastId = data.id;
            })
            .listenForWhisper('webrtc-signal', async (data) => {
                if (data.targetUserId !== LB_CONFIG.userId) return;
                if (data.type === 'answer') {
                    await _handleAnswer(data.fromUserId, data.sdp);
                } else if (data.type === 'ice-candidate') {
                    await _handleViewerIce(data.fromUserId, data.candidate);
                }
            });

        window.Echo.connector.pusher?.connection?.bind('error', (err) => {
            console.error('[LB] Echo bağlantı hatası:', err);
        });
    }

    function _setViewers(n) {
        ['viewerCount','viewerCount2'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = n;
        });
    }

    /* ════ YAYINI BİTİR ════ */
    function endBroadcast() {
        if (!confirm('Yayını sonlandırmak istiyor musunuz?')) return;
        stopCamera();
        _teardownLive();
        fetch(LB_CONFIG.endEndpoint, {
            method  : 'POST',
            headers : { 'X-CSRF-TOKEN': LB_CONFIG.csrfToken, 'Accept': 'application/json' },
        }).finally(() => { window.location.href = LB_CONFIG.sellerDashboardUrl; });
    }

    /* ════ SOCKET/POLLING KAPAT ════ */
    function _teardownLive() {
        if (s.pollTimer)  { clearInterval(s.pollTimer);  s.pollTimer = null; }
        if (s.chatTimer)  { clearInterval(s.chatTimer);  s.chatTimer = null; }
        Object.values(s.peers).forEach(pc => { try { pc.close(); } catch(e){} });
        s.peers = {};
        if (s.presenceChannel && window.Echo) {
            try { window.Echo.leave('auction.' + LB_CONFIG.auctionId); } catch(e){}
            s.presenceChannel = null;
            s.presenceActive  = false;
        }
    }

    /* ════ CANLI DURUM (polling fallback) ════ */
    let _lbLastBidId = LB_CONFIG.lastBidId;
    let _lbSoldHandled = LB_CONFIG.isSold;
    async function _pollLive() {
        try {
            const res = await fetch(LB_CONFIG.liveStateUrl + '?after=' + _lbLastBidId, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const d = await res.json();
            if (!s.presenceActive && typeof d.viewer_count !== 'undefined') _setViewers(d.viewer_count);
            if (Array.isArray(d.new_bids)) {
                d.new_bids.forEach(b => {
                    if (b.bid_id > _lbLastBidId) {
                        _lbLastBidId = b.bid_id;
                        _addBidRow(b.bid_id, b.bidder_name, b.amount, 'az önce');
                    }
                });
            }
            if (!_lbSoldHandled && (d.status === 'sold' || d.status === 'ended')) {
                _lbSoldHandled = true;
                if (d.sold) { s.selectedName = d.sold.buyer_name || s.selectedName; }
                if (typeof d.current_price !== 'undefined') { s.selectedAmount = d.current_price; }
                _lockUiAfterSale();
                _teardownLive();
            }
        } catch (e) { /* sessiz */ }
    }

    /* ════ SOHBET ════ */
    function _initChat() {
        const form = document.getElementById('lbChatForm');
        if (form) form.addEventListener('submit', _sendChat);
        _pollChat();
        s.chatTimer = setInterval(_pollChat, 3000);
    }

    async function _pollChat() {
        try {
            const res = await fetch(LB_CONFIG.chatPollUrl + '?after=' + s.chatLastId, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const d = await res.json();
            (d.messages || []).forEach(m => {
                if (m.id > s.chatLastId) { s.chatLastId = m.id; _renderChatMsg(m); }
            });
        } catch (e) { /* sessiz */ }
    }

    function _renderChatMsg(m) {
        const box = document.getElementById('lbChatMessages');
        if (!box) return;
        document.getElementById('lbChatEmpty')?.remove();
        const wrap = document.createElement('div');
        wrap.style.cssText = 'padding:6px 4px;font-size:13px;line-height:1.45;';
        const nameColor = m.is_seller ? '#10b981' : '#155eef';
        const badge = m.is_seller ? ' <span style="font-size:9px;background:#10b981;color:#fff;padding:1px 5px;border-radius:6px;">SATICI</span>' : '';
        wrap.innerHTML = '<span style="font-weight:700;color:'+nameColor+';">'+_esc(m.user_name)+'</span>'+badge+' <span style="color:var(--text);">'+_esc(m.message)+'</span>';
        box.appendChild(wrap);
        box.scrollTop = box.scrollHeight;
    }

    async function _sendChat(e) {
        e.preventDefault();
        const input = document.getElementById('lbChatInput');
        const text = (input.value || '').trim();
        if (!text) return;
        input.disabled = true;
        try {
            const res = await fetch(LB_CONFIG.chatStoreUrl, {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': LB_CONFIG.csrfToken, 'Accept': 'application/json' },
                body   : JSON.stringify({ message: text }),
            });
            const d = await res.json().catch(() => ({}));
            if (res.ok) {
                input.value = '';
                if (d.id > s.chatLastId) { s.chatLastId = d.id; _renderChatMsg(d); }
            } else {
                _showToast('Sohbet', d.message || 'Mesaj gönderilemedi.');
            }
        } catch (err) { /* sessiz */ }
        input.disabled = false;
        input.focus();
    }

    /* ════ INIT ════ */
    function _init() {
        _startTimer();
        if (s.selectedBidId) {
            document.getElementById('sellBtn').disabled = false;
        }
        if (LB_CONFIG.isSold) {
            _lockUiAfterSale();
            return;
        }
        _initEcho();
        _initChat();
        _pollLive();
        s.pollTimer = setInterval(_pollLive, 2500);
    }

    document.addEventListener('DOMContentLoaded', _init);

    return { toggleCamera, flipCamera, toggleMic, toggleScreen, selectBid, startSell, endBroadcast };
})();
