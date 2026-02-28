<?php

use Illuminate\Support\Facades\Route;

/* ================= CONTROLLERS ================= */
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PredictController;

/* ================= HALAMAN AWAL ================= */
Route::redirect('/', '/login');


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
    Route::get('data_barang', [BarangController::class, 'index'])->name('data_barang.index');
    Route::get('data_barang/{data_barang}', [BarangController::class, 'show'])
        ->whereNumber('data_barang')
        ->name('data_barang.show');

    // HANYA OWNER BOLEH TAMBAH/EDIT/HAPUS
    Route::middleware(['owner'])->group(function () {
        Route::get('data_barang/create', [BarangController::class, 'create'])->name('data_barang.create');
        Route::post('data_barang', [BarangController::class, 'store'])->name('data_barang.store');
        Route::get('data_barang/{data_barang}/edit', [BarangController::class, 'edit'])->name('data_barang.edit');
        Route::put('data_barang/{data_barang}', [BarangController::class, 'update'])->name('data_barang.update');
        Route::delete('data_barang/{data_barang}', [BarangController::class, 'destroy'])->name('data_barang.destroy');
    });

    /* ================= PROFILE ================= */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* ================= DATA PENJUALAN ================= */
    Route::resource('data_penjualan', PenjualanController::class);
    Route::post('/data-penjualan/import', [PenjualanController::class, 'import'])->name('data_penjualan.import');

    //* ================= PREDIKSI ================= */
Route::get('/prediksi', [PredictController::class, 'index'])->name('prediksi');
Route::post('/prediksi', [PredictController::class, 'store'])->name('prediksi.store');
Route::post('/prediksi/proses', [PredictController::class, 'store'])->name('prediksi.proses');

    /* ================= API DASHBOARD ================= */
    Route::get('/dashboard/prediksi-data', [DashboardController::class, 'getPrediksiData'])
        ->name('dashboard.prediksi.data');

    /* ================= EXPORT PDF ================= */
    Route::middleware(['auth','owner'])->group(function () {
    Route::get('/prediksi/export-pdf', [PredictController::class, 'exportPDF'])
        ->name('prediksi.export.pdf');
});

});

/* ================= AUTH DEFAULT ================= */
require __DIR__.'/auth.php';