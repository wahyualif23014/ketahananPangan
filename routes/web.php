<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboard;
use App\Http\Controllers\Admin\TingkatKesatuanController;
use App\Http\Controllers\Admin\JabatanController;
use App\Http\Controllers\Admin\WilayahController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PotensiLahanController;
use App\Http\Controllers\Admin\KelolaLahanController;
use App\Http\Controllers\Admin\RekapitulasiController;
use App\Http\Controllers\Admin\KomoditasController;

// Halaman Utama

Route::get('/', function () {
    return redirect()->route('login');
});

// 1. Dashboard Standar (Anggota/User Biasa)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// 2. Dashboard & Data Utama Khusus Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Route: admin.dashboard
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    
    // Grouping Data Utama (admin/data-utama/...)
    Route::prefix('data-utama')->group(function () {
        Route::get('/tingkat-kesatuan', [TingkatKesatuanController::class, 'index'])->name('tingkat-kesatuan.index');
        Route::get('/jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
        Route::get('/wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
        Route::get('/komoditas', [KomoditasController::class, 'index'])->name('komoditas.index');
    });

    Route::get('/anggota', function() {
        return view('admin.anggota.index');
    })->name('anggota.index');

    Route::prefix('kelola-lahan')->name('kelola-lahan.')->group(function () {
        Route::get('/potensi', [PotensiLahanController::class, 'index'])->name('potensi.index');
        Route::get('/daftar', [KelolaLahanController::class, 'index'])->name('daftar.index');
    });
    Route::get('/rekapitulasi', [RekapitulasiController::class, 'index'])->name('rekapitulasi.index');
});

// 3. Dashboard Khusus Operator
Route::middleware(['auth', 'role:operator'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboard::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';