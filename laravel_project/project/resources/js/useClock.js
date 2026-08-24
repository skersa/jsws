import { ref } from 'vue';

// Tek bir global saat (unix saniye) — tüm geri sayımlar bunu paylaşır
const now = ref(Math.floor(Date.now() / 1000));
setInterval(() => { now.value = Math.floor(Date.now() / 1000); }, 1000);

export function useClock() {
    return now;
}

export function formatCountdown(endsTs, nowTs) {
    const diff = endsTs - nowTs;
    if (diff <= 0) return { text: 'Bitti', critical: true };
    const h = Math.floor(diff / 3600);
    const m = Math.floor((diff % 3600) / 60);
    const s = diff % 60;
    const text = h > 0
        ? `${h}s ${String(m).padStart(2, '0')}d`
        : `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    return { text, critical: diff < 1800 };
}
