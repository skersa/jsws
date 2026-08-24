// LiveKit (WebRTC SFU) yardımcı fonksiyonları.
// Yayıncı kamerayı/mikrofonu yayınlar; izleyiciler abone olup uzak video/ses track'lerini alır.
import { Room, RoomEvent, Track } from 'livekit-client';

// Backend'den kısa ömürlü katılım token'ı al. API secret ASLA istemciye gelmez.
export async function fetchLiveKitToken({ auctionSlug, role, csrf }) {
    const res = await fetch('/livekit/token', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf || '',
        },
        body: JSON.stringify({ auction: auctionSlug, role }),
    });
    if (res.status === 503) {
        const j = await res.json().catch(() => ({}));
        const e = new Error(j.message || 'LiveKit yapılandırılmadı');
        e.code = 'not_configured';
        throw e;
    }
    if (!res.ok) throw new Error(`Token alınamadı (${res.status})`);
    return res.json();
}

// Odaya bağlan. onVideo(el|track) ve onStatus geri çağrılarıyla UI güncellenir.
export async function connectRoom({ auctionSlug, role, csrf, videoEl, onStatus, onParticipants }) {
    onStatus?.('connecting');
    const { server_url, participant_token } = await fetchLiveKitToken({ auctionSlug, role, csrf });

    const room = new Room({ adaptiveStream: true, dynacast: true });

    const attach = (track) => {
        if (!videoEl) return;
        if (track.kind === Track.Kind.Video) track.attach(videoEl);
        if (track.kind === Track.Kind.Audio) track.attach(); // ses için ayrı <audio> otomatik
    };

    room.on(RoomEvent.TrackSubscribed, (track) => attach(track));
    room.on(RoomEvent.TrackUnsubscribed, (track) => { try { track.detach(); } catch (e) {} });
    room.on(RoomEvent.ParticipantConnected, () => onParticipants?.(room.numParticipants));
    room.on(RoomEvent.ParticipantDisconnected, () => onParticipants?.(room.numParticipants));
    room.on(RoomEvent.Disconnected, () => onStatus?.('disconnected'));

    await room.connect(server_url, participant_token, { autoSubscribe: true });
    onStatus?.('connected');
    onParticipants?.(room.numParticipants);

    // Bize katılmadan önce yayınlanmış track'leri de bağla
    room.remoteParticipants.forEach((p) => {
        p.trackPublications.forEach((pub) => { if (pub.isSubscribed && pub.track) attach(pub.track); });
    });

    return room;
}
