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
    const lb = document.getElementById('lightbox');
    document.getElementById('lightbox-img').src = src;
    lb.style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}
document.addEventListener('keydown', e => { if(e.key==='Escape') closeLightbox(); });

async function startCamera() {
    try {
        mediaStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode, width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: true
        });
        const video = document.getElementById('liveVideo');
        video.srcObject = mediaStream;
        video.style.display = 'block';
        document.getElementById('cam-off-state').style.display = 'none';
        document.getElementById('start-cam-btn').style.display = 'none';
        document.getElementById('stop-cam-btn').style.display = 'inline-flex';
        document.getElementById('record-indicator').style.display = 'inline-flex';
        document.getElementById('stream-live-pill').style.display = 'inline-flex';
        document.getElementById('seller-extra-controls').style.removeProperty('display');
        document.getElementById('cam-top-controls').style.removeProperty('display');
    } catch(err) {
        alert('Kamera erişimi sağlanamadı: ' + err.message);
    }
}

function stopCamera() {
    if(mediaStream) { mediaStream.getTracks().forEach(t => t.stop()); mediaStream = null; }
    const video = document.getElementById('liveVideo');
    video.srcObject = null;
    video.style.display = 'none';
    document.getElementById('cam-off-state').style.display = 'flex';
    document.getElementById('start-cam-btn').style.display = 'inline-flex';
    document.getElementById('stop-cam-btn').style.display = 'none';
    document.getElementById('record-indicator').style.display = 'none';
    document.getElementById('stream-live-pill').style.display = 'none';
    document.getElementById('seller-extra-controls').style.display = 'none';
    document.getElementById('cam-top-controls').style.display = 'none';
}

async function switchFacingMode() {
    facingMode = facingMode === 'environment' ? 'user' : 'environment';
    stopCamera(); setTimeout(startCamera, 300);
}

async function toggleScreenShare() {
    try {
        const screen = await navigator.mediaDevices.getDisplayMedia({ video: true });
        const video = document.getElementById('liveVideo');
        video.srcObject = screen;
        screen.getVideoTracks()[0].onended = () => { if(mediaStream) video.srcObject = mediaStream; };
    } catch(e) {}
}

function toggleMute() {
    if(!mediaStream) return;
    const at = mediaStream.getAudioTracks();
    if(!at.length) return;
    streamMuted = !streamMuted;
    at.forEach(t => t.enabled = !streamMuted);
    document.getElementById('mic-icon').className = streamMuted ? 'bi bi-mic-mute' : 'bi bi-mic';
}

function toggleStreamVolume() {
    const video = document.getElementById('liveVideo');
    video.muted = !video.muted;
    document.getElementById('vol-icon').className = video.muted ? 'bi bi-volume-mute' : 'bi bi-volume-up';
}

function toggleFullscreen() {
    const section = document.querySelector('.camera-section');
    if(!document.fullscreenElement) {
        section.requestFullscreen?.();
        document.getElementById('fs-icon').className = 'bi bi-fullscreen-exit';
    } else {
        document.exitFullscreen?.();
        document.getElementById('fs-icon').className = 'bi bi-fullscreen';
    }
}

function setQuick(amount) {
    const input = document.getElementById('bid-input');
    if(input) input.value = amount;
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
    if(errEl) errEl.style.display = 'none';
    if(!amount || amount < currentMin) {
        if(errEl) { errEl.textContent = `En az ${currentMin.toLocaleString('tr-TR')} ₺ girmelisiniz.`; errEl.style.display = 'block'; }
        return;
    }
    if(btn) btn.disabled = true;
    if(btnTxt) btnTxt.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Gönderiliyor...';
    try {
        const res  = await fetch(BID_URL, {
            method: 'POST',
            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest' },
            body: JSON.stringify({ amount })
        });
        const data = await res.json();
        if(!res.ok) {
            if(errEl) { errEl.textContent = data.message ?? 'Bir hata oluştu.'; errEl.style.display = 'block'; }
        } else {
            if(input) input.value = '';
            addBidToFeed(data.bidder_name, data.amount, true);
            updateStats(data);
        }
    } catch(e) {
        if(errEl) { errEl.textContent = 'Bağlantı hatası. Tekrar deneyin.'; errEl.style.display = 'block'; }
    }
    if(btn) btn.disabled = false;
    if(btnTxt) btnTxt.innerHTML = '<i class="bi bi-lightning-charge-fill"></i> Teklif Ver';
}

function addBidToFeed(name, amount, isMine = false) {
    const feed = document.getElementById('bid-feed');
    document.getElementById('feed-empty')?.remove();
    feed.querySelectorAll('.bid-rank').forEach((el, i) => {
        el.textContent = i + 2;
        el.className = 'bid-rank ' + (i===0?'r2':i===1?'r3':'rn');
    });
    feed.querySelectorAll('.top-label').forEach(el => el.remove());
    feed.querySelectorAll('.bid-item.bid-top').forEach(el => el.classList.remove('bid-top'));
    const color = isMine ? '10b981' : '155eef';
    const item  = document.createElement('div');
    item.className = 'bid-item bid-new bid-top';
    item.innerHTML = `
        <span class="top-label">En Yüksek</span>
        <span class="bid-rank r1">1</span>
        <img class="bid-avatar" src="https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=32&background=${color}&color=fff">
        <div style="flex:1;min-width:0;">
            <div class="bid-name">${name}${isMine?' <span style="font-size:10px;color:#10b981;">(sen)</span>':''}</div>
            <div class="bid-time">az önce</div>
        </div>
        <div class="bid-amount">${parseFloat(amount).toLocaleString('tr-TR',{minimumFractionDigits:0})} ₺</div>
    `;
    feed.insertBefore(item, feed.firstChild);
    const items = feed.querySelectorAll('.bid-item');
    if(items.length > 15) items[items.length-1].remove();
    document.querySelector('.feed-scroll')?.scrollTo({ top:0, behavior:'smooth' });
}

function updateStats(data) {
    ['live-price','live-price-mobile'].forEach(id => {
        const el = document.getElementById(id);
        if(!el) return;
        el.textContent = data.display_price;
        el.classList.remove('price-flash');
        void el.offsetWidth;
        el.classList.add('price-flash');
    });
    const countEl = document.getElementById('live-bid-count');
    if(countEl) countEl.textContent = data.total_bids;
    const badgeEl = document.getElementById('bid-count-badge');
    if(badgeEl) badgeEl.textContent = data.total_bids + ' teklif';
    currentMin = parseFloat(data.amount) + MIN_INCREMENT;
    ['bid-input','bid-input-mobile'].forEach(id => {
        const el = document.getElementById(id);
        if(el) { el.min = currentMin; el.placeholder = `Min: ${currentMin.toLocaleString('tr-TR')} ₺`; }
    });
    const btns = document.querySelectorAll('.quick-btn');
    const steps = [0,4,9];
    btns.forEach((btn, i) => {
        const val   = currentMin + (MIN_INCREMENT * steps[i]);
        const extra = MIN_INCREMENT * (steps[i] + 1);
        btn.innerHTML = `+${extra.toLocaleString('tr-TR')} ₺<span>${val.toLocaleString('tr-TR')} ₺</span>`;
        btn.onclick   = () => setQuick(val);
    });
}

function setViewerCount(n) {
    ['viewer-count','viewer-count-stream','live-viewer-stat'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.textContent = n;
    });
}

document.getElementById('bid-input')?.addEventListener('keydown', e => { if(e.key==='Enter') submitBid(); });
document.getElementById('bid-input-mobile')?.addEventListener('keydown', e => { if(e.key==='Enter') submitBidMobile(); });

@auth
const presenceChannel = window.Echo.join(`auction.${AUCTION_ID}`);
presenceChannel
    .here(users => setViewerCount(users.length))
    .joining(() => { const el = document.getElementById('viewer-count'); if(el) setViewerCount(parseInt(el.textContent||'0')+1); })
    .leaving(() => { const el = document.getElementById('viewer-count'); if(el) setViewerCount(Math.max(1,parseInt(el.textContent||'1')-1)); })
    .listen('.bid.placed', data => {
        addBidToFeed(data.bidder_name, data.amount, false);
        updateStats(data);
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'info',
                title: `🔴 ${data.bidder_name}`,
                text: `${data.display_price} teklif verdi`,
                showConfirmButton: false, timer: 4000, timerProgressBar: true,
            });
        }
    });
@else
window.Echo.channel(`auction.${AUCTION_ID}`)
    .listen('.bid.placed', data => {
        addBidToFeed(data.bidder_name, data.amount, false);
        updateStats(data);
    });
setViewerCount('?');
@endauth
