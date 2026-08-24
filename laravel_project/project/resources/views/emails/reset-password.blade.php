<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Şifreni Sıfırla</title>
</head>

<body style="margin:0;padding:0;background:#0f1221;font-family:Inter,Arial,sans-serif;">

    <div style="max-width:560px;margin:40px auto;background:#171a2b;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.4)">

        <div style="padding:30px 30px 10px;text-align:center;">
            <div style="font-size:20px;font-weight:700;color:#ffffff;">
                Şifreni Sıfırla
            </div>
            <div style="margin-top:6px;font-size:13px;color:#8b90a7;">
                {{ config('app.name') }}
            </div>
        </div>

        <div style="padding:20px 30px 10px;text-align:center;color:#cfd3e6;">

            <div style="font-size:14px;line-height:1.6;">
                Merhaba <b style="color:#ffffff">{{ $user->name }}</b>,
                <br><br>
                Şifreni sıfırlamak için aşağıdaki butona tıkla.
            </div>

        </div>

        <div style="text-align:center;padding:20px 30px 10px;">
            <a href="{{ $actionUrl }}" style="
                display:inline-block;
                padding:12px 26px;
                border-radius:10px;
                background:linear-gradient(135deg,#155eef,#1e40af);
                color:#fff;
                font-weight:600;
                font-size:14px;
                text-decoration:none;
                box-shadow:0 10px 25px rgba(21,94,239,.35);
           ">
                Şifreyi Sıfırla
            </a>
        </div>

        <div style="padding:15px 30px 30px;text-align:center;color:#6b7280;font-size:12px;line-height:1.5;">
            Buton çalışmazsa aşağıdaki linki kopyala:
            <br>
            <span style="color:#4b8bff;word-break:break-all;">
                {{ $actionUrl }}
            </span>
        </div>

        <div style="padding:18px;text-align:center;font-size:11px;color:#4b5563;border-top:1px solid #23263a;">
            © {{ date('Y') }} {{ config('app.name') }} · Güvenli sistem
        </div>

    </div>

</body>
</html>
