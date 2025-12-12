<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PemeliharaanController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::middleware(['admin'])->group(function () {
        Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
        Route::resource('users', UserController::class);
    });
    
    Route::middleware(['admin_or_user'])->group(function () {
        // Read-only routes for non-admin (index & show)
        Route::get('data_barang', [BarangController::class, 'index'])->name('data_barang.index');
        Route::get('data_barang/{data_barang}', [BarangController::class, 'show'])
            ->whereNumber('data_barang')
            ->name('data_barang.show');
        Route::get('data_ruangan', [RuanganController::class, 'index'])->name('data_ruangan.index');
        Route::get('data_ruangan/{data_ruangan}', [RuanganController::class, 'show'])
            ->whereNumber('data_ruangan')
            ->name('data_ruangan.show');

        // Admin-only write routes
        Route::middleware(['admin'])->group(function () {
            Route::get('data_barang/create', [BarangController::class, 'create'])->name('data_barang.create');
            Route::post('data_barang', [BarangController::class, 'store'])->name('data_barang.store');
            Route::get('data_barang/{data_barang}/edit', [BarangController::class, 'edit'])->name('data_barang.edit');
            Route::put('data_barang/{data_barang}', [BarangController::class, 'update'])->name('data_barang.update');
            Route::delete('data_barang/{data_barang}', [BarangController::class, 'destroy'])->name('data_barang.destroy');

            Route::get('data_ruangan/create', [RuanganController::class, 'create'])->name('data_ruangan.create');
            Route::post('data_ruangan', [RuanganController::class, 'store'])->name('data_ruangan.store');
            Route::get('data_ruangan/{data_ruangan}/edit', [RuanganController::class, 'edit'])->name('data_ruangan.edit');
            Route::put('data_ruangan/{data_ruangan}', [RuanganController::class, 'update'])->name('data_ruangan.update');
            Route::delete('data_ruangan/{data_ruangan}', [RuanganController::class, 'destroy'])->name('data_ruangan.destroy');
        });
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Notification routes (Web)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Operasional Routes
Route::prefix('operasional')->middleware(['auth', 'admin_or_user_operasional'])->group(function () {
    // Barang Masuk
    Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang_masuk.index');
    Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->name('barang_masuk.store');
    Route::delete('/barang-masuk/{id}', [BarangMasukController::class, 'destroy'])->name('barang_masuk.destroy');
    
    // Pastikan routes untuk barang keluar seperti ini:
Route::prefix('operasional')->group(function () {
    Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])->name('barang_keluar.index');
    Route::post('/barang-keluar', [BarangKeluarController::class, 'store'])->name('barang_keluar.store');
    Route::delete('/barang-keluar/{id}', [BarangKeluarController::class, 'destroy'])->name('barang_keluar.destroy');
    Route::get('/barang-keluar/get-barang/{ruanganId}', [BarangKeluarController::class, 'getBarangByRuangan'])->name('barang_keluar.get_barang');
});
    // Pemeliharaan
Route::get('/pemeliharaan', [PemeliharaanController::class, 'index'])->name('pemeliharaan.index');
Route::get('/pemeliharaan/create', [PemeliharaanController::class, 'create'])->name('pemeliharaan.create');
Route::post('/pemeliharaan', [PemeliharaanController::class, 'store'])->name('pemeliharaan.store');
Route::get('/pemeliharaan/{pemeliharaan}/edit', [PemeliharaanController::class, 'edit'])->name('pemeliharaan.edit');
Route::put('/pemeliharaan/{pemeliharaan}', [PemeliharaanController::class, 'update'])->name('pemeliharaan.update');
Route::delete('/pemeliharaan/{pemeliharaan}', [PemeliharaanController::class, 'destroy'])->name('pemeliharaan.destroy');

// Route untuk AJAX get barang by ruangan
Route::get('/pemeliharaan/get-barang/{ruanganId}', [PemeliharaanController::class, 'getBarangByRuangan'])
    ->name('pemeliharaan.get_barang');
    // Peminjaman
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
Route::post('/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjaman.kembali');
Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy'])->name('peminjaman.destroy');

// Route untuk AJAX get barang by ruangan
Route::get('/peminjaman/get-barang/{ruanganId}', [PeminjamanController::class, 'getBarangByRuangan'])
    ->name('peminjaman.get_barang');});

// ✅ Notification routes dengan prefix "/api"
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/count', [NotificationController::class, 'count']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead']);
});

require __DIR__.'/auth.php';
