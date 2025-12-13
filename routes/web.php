<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/* ================= CONTROLLERS ================= */
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\PemeliharaanController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PenjualanController;

/* ================= HALAMAN AWAL ================= */
Route::get('/', function () {
    return view('welcome');
});

/* ================= REGISTER (ADMIN) ================= */
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

/* ================= AUTH ================= */
Route::middleware(['auth'])->group(function () {

    /* ===== DASHBOARD ===== */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* ===== USERS (ADMIN) ===== */
    Route::middleware(['admin'])->group(function () {
        Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
        Route::resource('users', UserController::class);
    });

    /* ================= BARANG ================= */
    Route::middleware(['admin_or_user'])->group(function () {

        // Read Only
        Route::get('data_barang', [BarangController::class, 'index'])->name('data_barang.index');
        Route::get('data_barang/{data_barang}', [BarangController::class, 'show'])
            ->whereNumber('data_barang')
            ->name('data_barang.show');

        // Admin Only
        Route::middleware(['admin'])->group(function () {
            Route::get('data_barang/create', [BarangController::class, 'create'])->name('data_barang.create');
            Route::post('data_barang', [BarangController::class, 'store'])->name('data_barang.store');
            Route::get('data_barang/{data_barang}/edit', [BarangController::class, 'edit'])->name('data_barang.edit');
            Route::put('data_barang/{data_barang}', [BarangController::class, 'update'])->name('data_barang.update');
            Route::delete('data_barang/{data_barang}', [BarangController::class, 'destroy'])->name('data_barang.destroy');
        });
    });

    /* ================= PROFILE ================= */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* ================= NOTIFICATIONS ================= */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    /* ================= DATA PENJUALAN ================= */
    Route::resource('data_penjualan', PenjualanController::class);

});

/* ================= OPERASIONAL ================= */
Route::prefix('operasional')->middleware(['auth', 'admin_or_user_operasional'])->group(function () {

    /* ===== BARANG MASUK ===== */
    Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang_masuk.index');
    Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->name('barang_masuk.store');
    Route::delete('/barang-masuk/{id}', [BarangMasukController::class, 'destroy'])->name('barang_masuk.destroy');

    /* ===== BARANG KELUAR ===== */
    Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])->name('barang_keluar.index');
    Route::post('/barang-keluar', [BarangKeluarController::class, 'store'])->name('barang_keluar.store');
    Route::delete('/barang-keluar/{id}', [BarangKeluarController::class, 'destroy'])->name('barang_keluar.destroy');
    Route::get('/barang-keluar/get-barang/{ruanganId}', [BarangKeluarController::class, 'getBarangByRuangan'])
        ->name('barang_keluar.get_barang');

    /* ===== PEMELIHARAAN ===== */
    Route::get('/pemeliharaan', [PemeliharaanController::class, 'index'])->name('pemeliharaan.index');
    Route::get('/pemeliharaan/create', [PemeliharaanController::class, 'create'])->name('pemeliharaan.create');
    Route::post('/pemeliharaan', [PemeliharaanController::class, 'store'])->name('pemeliharaan.store');
    Route::get('/pemeliharaan/{pemeliharaan}/edit', [PemeliharaanController::class, 'edit'])->name('pemeliharaan.edit');
    Route::put('/pemeliharaan/{pemeliharaan}', [PemeliharaanController::class, 'update'])->name('pemeliharaan.update');
    Route::delete('/pemeliharaan/{pemeliharaan}', [PemeliharaanController::class, 'destroy'])->name('pemeliharaan.destroy');
    Route::get('/pemeliharaan/get-barang/{ruanganId}', [PemeliharaanController::class, 'getBarangByRuangan'])
        ->name('pemeliharaan.get_barang');

    /* ===== PEMINJAMAN ===== */
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::post('/peminjaman/{id}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjaman.kembali');
    Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy'])->name('peminjaman.destroy');
    Route::get('/peminjaman/get-barang/{ruanganId}', [PeminjamanController::class, 'getBarangByRuangan'])
        ->name('peminjaman.get_barang');

});

/* ================= API NOTIFICATIONS ================= */
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/count', [NotificationController::class, 'count']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead']);
});

/* ================= AUTH DEFAULT ================= */
require __DIR__.'/auth.php';
