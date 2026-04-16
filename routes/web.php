<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboard;
use App\Http\Controllers\Admin\TingkatKesatuanController;
use App\Http\Controllers\Admin\JabatanController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Admin\PotensiLahanController;
use App\Http\Controllers\Admin\KelolaLahanController;
use App\Http\Controllers\Admin\RekapitulasiController;
use App\Http\Controllers\Admin\KomoditasController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect halaman utama ke login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // 1. Group Khusus Admin
    Route::middleware(['checkrole:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard Admin
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // Grouping Data Utama
        Route::prefix('data-utama')->group(function () {
            Route::get('/tingkat-kesatuan', [TingkatKesatuanController::class, 'index'])->name('tingkat-kesatuan.index');
            // jabatan
            Route::get('/jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
            Route::delete('/admin/jabatan/batch-delete', [JabatanController::class, 'batchDelete'])
            ->name('jabatan.batch-delete');
            Route::put('/jabatan/{id}', [JabatanController::class, 'update'])->name('jabatan.update');

            Route::get('/wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
            Route::get('/komoditas', [KomoditasController::class, 'index'])->name('komoditas.index');
        });

        // Data Anggota/Personel
        Route::get('/anggota', function () {
        // Ambil data dari tabel anggota melalui Model User
        $personels = User::all(); 
        
        // Kirim data ke view index
        return view('admin.anggota.index', compact('personels'));
    })->name('anggota.index');

        // Kelola Lahan
        Route::prefix('kelola-lahan')->name('kelola-lahan.')->group(function () {
            Route::get('/potensi', [PotensiLahanController::class, 'index'])->name('potensi.index');
            Route::get('/daftar', [KelolaLahanController::class, 'index'])->name('daftar.index');
        });

        // Rekapitulasi
        Route::get('/rekapitulasi', [RekapitulasiController::class, 'index'])->name('rekapitulasi.index');
    });

    // 2. Group Khusus Operator
    Route::middleware(['checkrole:operator'])->prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', function () {
            return view('operator.dashboard');
        })->name('dashboard');
    });

    // 3. Group Khusus View
    Route::middleware(['checkrole:view'])->prefix('view')->name('view.')->group(function () {
        Route::get('/dashboard', function () {
            return view('view.dashboard');
        })->name('dashboard');
    });

    // Rute Profil Standar
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
