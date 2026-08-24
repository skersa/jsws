import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Laravel Reverb, Pusher protokolünü konuşur. En yüksek uyumluluk için
// broadcaster: 'pusher' kullanıp wsHost/wsPort ile Reverb'e yönlendiriyoruz.
// Bağlantı kurulamazsa uygulama çökmesin diye try/catch ile sarılıdır.
try {
    const key = import.meta.env.VITE_REVERB_APP_KEY;
    if (key) {
        const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'https';
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: key,
            cluster: 'mt1',
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
            wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
            wsPath: import.meta.env.VITE_REVERB_PATH ?? '',
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
        });

        window.Echo.connector.pusher.connection.bind('error', () => {
            // Sessizce yut — canlı bağlantı yoksa sayfa polling ile çalışmaya devam eder.
        });
    } else {
        console.info('[Echo] Reverb anahtarı tanımlı değil, canlı bağlantı devre dışı.');
    }
} catch (e) {
    console.info('[Echo] Canlı bağlantı başlatılamadı:', e && e.message);
}
