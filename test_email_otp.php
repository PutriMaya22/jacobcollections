<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/bootstrap/commands.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test email OTP
use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

// Buat user dummy
$user = new User();
$user->name = 'Test User';
$user->email = 'test@example.com';

// Buat email OTP
$otp = '1234';
$mail = new OtpVerificationMail($user, $otp);

// Tampilkan informasi envelope
echo "=== TEST EMAIL OTP ===\n";
echo "From Address: " . config('mail.from.address') . "\n";
echo "From Name: " . config('mail.from.name') . "\n";
echo "Mail Driver: " . config('mail.default') . "\n";
echo "SMTP Host: " . config('mail.mailers.smtp.host') . "\n";
echo "SMTP Port: " . config('mail.mailers.smtp.port') . "\n";
echo "SMTP Username: " . config('mail.mailers.smtp.username') . "\n";

// Tampilkan envelope email
$envelope = $mail->envelope();
echo "\n=== EMAIL ENVELOPE ===\n";
echo "Subject: " . $envelope->subject . "\n";
echo "From: " . $envelope->from[0]->address . "\n";
echo "From Name: " . $envelope->from[0]->name . "\n";

echo "\n=== KONFIGURASI .env ===\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT') . "\n";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION') . "\n";

echo "\nTest selesai!\n";

