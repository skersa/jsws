/*
 | SPA-güvenli CSRF başlıkları.
 | Inertia SPA'da <meta name="csrf-token"> ve XSRF cookie login/gezinti sonrası
 | BAYAT kalabiliyor ve fetch POST'ları 419 veriyor.
 | En garantili yol: o ANKİ Inertia yanıtından gelen taze token'ı (usePage().props.csrf_token)
 | X-CSRF-TOKEN olarak göndermek. Verilmezse XSRF cookie, o da yoksa meta'ya düşer.
*/
export function csrfHeaders(extra = {}, token = null) {
    const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...extra };
    if (token) {
        headers['X-CSRF-TOKEN'] = token;
        return headers;
    }
    const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    if (m) {
        headers['X-XSRF-TOKEN'] = decodeURIComponent(m[1]);
    } else {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) headers['X-CSRF-TOKEN'] = meta.content;
    }
    return headers;
}
