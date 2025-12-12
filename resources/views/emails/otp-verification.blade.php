<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Verifikasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .otp-code {
            background: #fff;
            border: 2px dashed #667eea;
            padding: 20px;
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
            border-radius: 10px;
            letter-spacing: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Kode OTP Verifikasi</h1>
        <p>Halo, {{ $user->name }}!</p>
    </div>
    
    <div class="content">
        <p>Anda telah meminta kode OTP untuk verifikasi. Berikut adalah kode OTP Anda:</p>
        
        <div class="otp-code">
            {{ $otp }}
        </div>
        
        <div class="warning">
            <strong>⚠️ Penting:</strong>
            <ul>
                <li>Kode OTP ini hanya berlaku selama 60 detik</li>
                <li>Jangan bagikan kode ini kepada siapapun</li>
                <li>Jika Anda tidak meminta kode ini, abaikan email ini</li>
            </ul>
        </div>
        
        <p>Masukkan kode di atas pada halaman verifikasi untuk melanjutkan.</p>
        
        <p>Terima kasih,<br>
        <strong>Tim Astera Project</strong></p>
    </div>
    
    <div class="footer">
        <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
        <p>&copy; {{ date('Y') }} Astera Project. All rights reserved.</p>
    </div>
</body>
</html>
