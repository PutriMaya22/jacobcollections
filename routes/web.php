<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PredictController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== PUBLIC ROUTES ====================
Route::get('/', function () {
    return view('auth.login');
});

// ==================== AUTH ROUTES (Bawaan Laravel) ====================
require __DIR__.'/auth.php';

// ==================== AUTHENTICATED ROUTES ====================
Route::middleware(['auth'])->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/penjualan-periode', [DashboardController::class, 'getPenjualanPerPeriode'])
        ->name('dashboard.penjualan.periode');

    Route::get('/dashboard/prediksi-data', [DashboardController::class, 'getPrediksiData'])
        ->name('dashboard.prediksi.data');

    Route::get('/prediksi/live-tracking', [DashboardController::class, 'getLiveTracking'])
        ->name('prediksi.live_tracking');

    Route::post('/prediksi/{id}/update-realisasi', [DashboardController::class, 'updateRealisasi'])
        ->name('prediksi.update_realisasi');
});
    
    // ==================== LIVE TRACKING PREDIKSI ====================
    Route::get('/prediksi/live-tracking', [DashboardController::class, 'getLiveTracking'])->name('prediksi.live-tracking');
    Route::post('/prediksi/{id}/update-realisasi', [DashboardController::class, 'updateRealisasi'])->name('prediksi.update-realisasi');
    // Route untuk live tracking (tanpa filter = semua data)
Route::get('/prediksi/live-tracking-all', [DashboardController::class, 'getLiveTracking'])->name('prediksi.live-tracking-all');
    
    // ==================== PROFILE ====================
    Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

    
    // ==================== USERS (MANAJEMEN USER) - ADMIN ONLY ====================
    Route::middleware(['admin'])->group(function () {
        Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
        Route::resource('users', UserController::class);
    });
    
    // ==================== DATA BARANG ====================
Route::get('data_barang', [BarangController::class, 'index'])->name('data_barang.index');
Route::post('data_barang/import', [BarangController::class, 'import'])->name('data_barang.import');

// 🔥 REDIRECT untuk akses GET langsung ke ID (solusi error)
Route::get('data_barang/{id}', function ($id) {
    return redirect()->route('data_barang.index');
})->whereNumber('id');

// Owner only untuk create, edit, delete barang
Route::middleware(['owner'])->group(function () {
    Route::get('data_barang/create', [BarangController::class, 'create'])->name('data_barang.create');
    Route::post('data_barang', [BarangController::class, 'store'])->name('data_barang.store');
    Route::get('data_barang/{data_barang}/edit', [BarangController::class, 'edit'])->name('data_barang.edit');
    Route::put('data_barang/{data_barang}', [BarangController::class, 'update'])->name('data_barang.update');
    Route::delete('data_barang/{data_barang}', [BarangController::class, 'destroy'])->name('data_barang.destroy');
});
    
    // ==================== DATA PENJUALAN ====================
    // Semua role bisa melihat
    Route::get('/data_penjualan', [PenjualanController::class, 'index'])->name('data_penjualan.index');
    
    // Owner only untuk create, edit, delete, import, export
    Route::middleware(['owner'])->group(function () {
        Route::get('/data_penjualan/create', [PenjualanController::class, 'create'])->name('data_penjualan.create');
        Route::post('/data_penjualan', [PenjualanController::class, 'store'])->name('data_penjualan.store');
        Route::get('/data_penjualan/{tanggal}/edit', [PenjualanController::class, 'edit'])->name('data_penjualan.edit');
        Route::put('/data_penjualan/{tanggal}', [PenjualanController::class, 'update'])->name('data_penjualan.update');
        Route::delete('/data_penjualan/{tanggal}', [PenjualanController::class, 'destroy'])->name('data_penjualan.destroy');
        Route::post('/data_penjualan/destroy-multiple', [PenjualanController::class, 'destroyMultiple'])->name('data_penjualan.destroy-multiple');
        Route::post('/data_penjualan/import', [PenjualanController::class, 'import'])->name('data_penjualan.import');
    });
    
    
    // ==================== PREDIKSI ====================
    // Semua role bisa melihat daftar prediksi
    Route::get('/prediksi', [PredictController::class, 'index'])->name('prediksi');
    
    // Owner only untuk membuat prediksi baru
    Route::middleware(['owner'])->group(function () {
        Route::post('/prediksi/store', [PredictController::class, 'store'])->name('prediksi.store');
        Route::post('/prediksi/proses', [PredictController::class, 'store'])->name('prediksi.proses');
    });
    
    // Update realisasi prediksi (hanya owner, sudah dicek di controller)
    Route::post('/prediksi/{id}/realisasi', [PredictController::class, 'updateRealisasiDashboard'])->name('prediksi.update-realisasi');
    Route::get('/prediksi/{id}/detail', [PredictController::class, 'getDetail'])->name('prediksi.detail');
    
    // Export PDF & Excel
    Route::middleware(['owner'])->group(function () {
        Route::get('/prediksi/export-pdf', [PredictController::class, 'exportPDF'])->name('prediksi.export.pdf');
        Route::get('/prediksi/export-rekomendasi', [PredictController::class, 'exportRekomendasiPDF'])->name('prediksi.export.rekomendasi');
    });
});

// ==================== GRAFIK (Owner & Admin) ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/grafik', [PredictController::class, 'grafik'])->name('admin.grafik')->middleware(['role:owner,admin']);
});

// ==================== STOK MONITORING API ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/stok_monitoQring', [App\Http\Controllers\DashboardController::class, 'stokMonitoring'])->name('stok.monitoring');
    Route::get('/evaluasi_history', [App\Http\Controllers\DashboardController::class, 'evaluasiHistory'])->name('evaluasi.history');
    Route::get('/popular_products', [App\Http\Controllers\DashboardController::class, 'popularProducts'])->name('popular.products');
});