<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    Mail::raw('Test email dari Astera - ' . date('Y-m-d H:i:s'), function($message) {
        $message->to('test@example.com')
                ->subject('Test Email Astera - ' . date('Y-m-d H:i:s'));
    });
    
    echo "✅ Test email berhasil dikirim!\n";
    echo "📧 Cek folder Spam/Junk jika email tidak masuk inbox\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "🔧 Pastikan konfigurasi Gmail SMTP sudah benar di file .env\n";
}
