<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Yeni İletişim Mesajı</title>
</head>
<body style="margin:0;padding:0;background:#0f1221;font-family:Inter,Arial,sans-serif;">

    <div style="max-width:560px;margin:40px auto;background:#171a2b;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.4)">

        <div style="padding:30px 30px 10px;text-align:center;">
            <div style="display:inline-flex;align-items:center;gap:8px;justify-content:center;margin-bottom:20px;">
                <svg width="260" height="88" viewBox="0 0 360 88" xmlns="http://www.w3.org/2000/svg">
                    <rect width="260" height="88" fill="none" />
                    <g transform="translate(16, 16)">
                        <rect width="56" height="56" rx="14" fill="#0F2240" />
                        <rect x="0.75" y="0.75" width="54.5" height="54.5" rx="13.25" fill="none" stroke="#1A3A5C" stroke-width="1.5" />
                        <circle cx="28" cy="38" r="4.5" fill="#00C8E0" />
                        <path d="M15,38 A13,13 0 0,1 41,38" fill="none" stroke="#00C8E0" stroke-width="2.8" stroke-linecap="round" opacity="0.95" />
                        <path d="M8,38 A20,20 0 0,1 48,38" fill="none" stroke="#00C8E0" stroke-width="2.2" stroke-linecap="round" opacity="0.45" />
                        <path d="M2,38 A26,26 0 0,1 54,38" fill="none" stroke="#00C8E0" stroke-width="1.5" stroke-linecap="round" opacity="0.18" />
                    </g>
                    <line x1="86" y1="20" x2="86" y2="68" stroke="#162E48" stroke-width="0.8" />
                    <text x="102" y="50" font-family="-apple-system,'SF Pro Display','Helvetica Neue',Arial,sans-serif" font-weight="800" font-size="28" letter-spacing="-0.8">
                        <tspan fill="#EDF4FF">artirdim</tspan>
                        <tspan fill="#00C8E0">.</tspan>
                        <tspan fill="#2A4A6E">com</tspan>
                    </text>
                    <text x="103" y="65" font-family="-apple-system,'SF Pro Text','Helvetica Neue',Arial,sans-serif" font-size="8" fill="#1E3A58" letter-spacing="2.5">LIVE AUCTION PLATFORM</text>
                    <rect x="103" y="70" width="40" height="1.5" rx="0.75" fill="#00C8E0" opacity="0.55" />
                </svg>

            </div>

            <div style="width:52px;height:52px;background:rgba(79,70,229,.15);border-radius:14px;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:24px;line-height:1;">
                📩
            </div>

            <div style="font-size:20px;font-weight:700;color:#ffffff;">
                Yeni İletişim Mesajı
            </div>
            <div style="margin-top:6px;font-size:13px;color:#8b90a7;">
                İletişim formu aracılığıyla yeni bir mesaj aldınız
            </div>
        </div>

        <div style="padding:20px 30px;">

            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
                <tr>
                    <td width="48%" style="background:#1e2235;border:1px solid #23263a;border-radius:12px;padding:14px 16px;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Gönderen</div>
                        <div style="font-size:14px;font-weight:600;color:#ffffff;">{{ $name }}</div>
                    </td>
                    <td width="4%"></td>
                    <td width="48%" style="background:#1e2235;border:1px solid #23263a;border-radius:12px;padding:14px 16px;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">E-posta</div>
                        <div style="font-size:14px;font-weight:600;color:#4b8bff;word-break:break-all;">{{ $email }}</div>
                    </td>
                </tr>
            </table>

            <div style="margin-bottom:16px;">
                <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Konu</div>
                <span style="display:inline-block;padding:5px 14px;border-radius:20px;background:rgba(21,94,239,.15);color:#4b8bff;font-size:13px;font-weight:700;border:1px solid rgba(21,94,239,.25);">
                    {{ $subject }}
                </span>
            </div>

            <div style="border-top:1px solid #23263a;margin:0 0 16px;"></div>

            <div style="margin-bottom:24px;">
                <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Mesaj</div>
                <div style="background:#1e2235;border:1px solid #23263a;border-radius:12px;padding:18px;font-size:14px;color:#cfd3e6;line-height:1.75;white-space:pre-wrap;">{{ $userMessage }}</div>
            </div>

            <div style="text-align:center;">
                <a href="mailto:{{ $email }}" style="display:inline-block;padding:12px 26px;border-radius:10px;background:linear-gradient(135deg,#155eef,#1e40af);color:#fff;font-weight:600;font-size:14px;text-decoration:none;box-shadow:0 10px 25px rgba(21,94,239,.35);">
                    Yanıtla →
                </a>
            </div>

        </div>

        <div style="padding:18px;text-align:center;font-size:11px;color:#4b5563;border-top:1px solid #23263a;">
            Bu e-posta {{ config('app.name') }} iletişim formu aracılığıyla gönderilmiştir · © {{ date('Y') }}
        </div>

    </div>

</body>
</html>
