# Gmail SMTP Setup untuk OTP Astera

## Langkah-langkah Konfigurasi Gmail SMTP

### 1. Buat App Password di Gmail

1. Buka [Google Account Settings](https://myaccount.google.com/)
2. Pilih tab **Security**
3. Aktifkan **2-Step Verification** jika belum aktif
4. Scroll ke bawah dan pilih **App passwords**
5. Pilih **Mail** sebagai aplikasi
6. Pilih **Other (Custom name)** dan beri nama "Astera OTP"
7. Klik **Generate**
8. **Copy App Password** yang muncul (16 karakter)

### 2. Konfigurasi .env File

Buat file `.env` di root project dengan konfigurasi berikut:

```env
APP_NAME=Astera
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=astera
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Gmail SMTP Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 3. Update Konfigurasi Email

Ganti nilai berikut di file `.env`:

- `MAIL_USERNAME`: Email Gmail Anda (contoh: `myemail@gmail.com`)
- `MAIL_PASSWORD`: App Password yang sudah dibuat (16 karakter)
- `MAIL_FROM_ADDRESS`: Email Gmail Anda (sama dengan MAIL_USERNAME)

### 4. Generate App Key

Jalankan perintah berikut untuk generate app key:

```bash
php artisan key:generate
```

### 5. Test Konfigurasi

Untuk memastikan konfigurasi berhasil, jalankan:

```bash
php artisan tinker
```

Kemudian test email:

```php
Mail::raw('Test email dari Astera', function($message) {
    $message->to('your-test-email@gmail.com')
            ->subject('Test Email Astera');
});
```

### 6. Fitur OTP yang Sudah Diimplementasi

✅ **Email Template Modern**: Template email OTP yang responsif dan menarik
✅ **Mail Class**: `OtpVerificationMail` untuk handling email OTP
✅ **Error Handling**: Logging untuk debugging jika email gagal
✅ **Resend OTP**: Fitur untuk mengirim ulang kode OTP
✅ **Session Management**: OTP disimpan di session dengan expiry time
✅ **Validation**: Validasi OTP 4 digit dengan timeout 1 menit

### 7. Cara Kerja OTP

1. User mendaftar → OTP dikirim ke email
2. User memasukkan OTP → Validasi dilakukan
3. Jika benar → Email terverifikasi, user bisa login
4. Jika salah/expired → Pesan error, bisa resend

### 8. Troubleshooting

**Email tidak terkirim:**
- Pastikan App Password benar
- Pastikan 2-Step Verification aktif
- Cek log di `storage/logs/laravel.log`

**OTP tidak masuk email:**
- Cek folder Spam/Junk
- Pastikan email address benar
- Cek konfigurasi SMTP

### 9. Keamanan

- OTP berlaku 1 menit
- App Password tidak boleh dibagikan
- Email OTP tidak boleh dibalas
- Log OTP hanya untuk debugging

### 10. File yang Telah Dibuat/Dimodifikasi

- ✅ `resources/views/emails/otp-verification.blade.php` - Template email
- ✅ `app/Mail/OtpVerificationMail.php` - Mail class
- ✅ `app/Http/Controllers/Auth/RegisteredUserController.php` - Updated
- ✅ `routes/web.php` - Updated resend functionality
- ✅ `GMAIL_SMTP_SETUP.md` - Setup guide ini

Sekarang aplikasi Astera sudah siap menggunakan Gmail SMTP untuk mengirim kode OTP ke email pengguna!
