<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboard;
use App\Http\Controllers\Operator\RekapitulasiController as OperatorRekapitulasi;
use App\Http\Controllers\Admin\TingkatKesatuanController;
use App\Http\Controllers\Admin\JabatanController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Admin\PotensiLahanController;
use App\Http\Controllers\Admin\KelolaLahanController;
use App\Http\Controllers\Admin\RekapitulasiController;
use App\Http\Controllers\Admin\KomoditiController;
use App\Http\Controllers\Admin\AnggotaController;
use App\Http\Controllers\Admin\AktivitasController;
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
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Grouping Data Utama
        Route::prefix('data-utama')->group(function () {
            Route::get('/tingkat-kesatuan', [TingkatKesatuanController::class, 'index'])->name('tingkat-kesatuan.index');
            // jabatan
            Route::get('/jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
            Route::post('/jabatan', [JabatanController::class, 'store'])->name('jabatan.store');
            Route::put('/jabatan/{id}', [JabatanController::class, 'update'])->name('jabatan.update');
            Route::delete('/jabatan/{id}', [JabatanController::class, 'destroy'])->name('jabatan.destroy');

            Route::get('/wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
            Route::put('/wilayah/update-lokasi', [WilayahController::class, 'updateLokasi'])->name('wilayah.update-lokasi');
            
            Route::prefix('komoditi')->name('komoditi.')->group(function () {
                Route::get('/', [KomoditiController::class, 'index'])->name('index');
                Route::post('/store', [KomoditiController::class, 'store'])->name('store');
                Route::put('/update', [KomoditiController::class, 'update'])->name('update');
                Route::delete('/destroy', [KomoditiController::class, 'destroy'])->name('destroy');
            });
        });

        // Data Anggota/Personel
        Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota.index');
        Route::post('/anggota', [AnggotaController::class, 'store'])->name('anggota.store');
        Route::put('/anggota/{id}', [AnggotaController::class, 'update'])->name('anggota.update');
        Route::delete('/anggota/{id}', [AnggotaController::class, 'destroy'])->name('anggota.destroy');


        // Kelola Lahan
        Route::prefix('kelola-lahan')->name('kelola-lahan.')->group(function () {
            Route::prefix('potensi')->name('potensi.')->group(function () {
                Route::get('/', [PotensiLahanController::class, 'index'])->name('index');
                Route::post('/store', [PotensiLahanController::class, 'store'])->name('store');
                Route::put('/verify/{id}', [PotensiLahanController::class, 'verify'])->name('verify');
                Route::put('/validasi/{id}', [PotensiLahanController::class, 'validasi'])->name('validasi');
                Route::put('/unvalidasi/{id}', [PotensiLahanController::class, 'unvalidasi'])->name('unvalidasi');
                Route::put('/update/{id}', [PotensiLahanController::class, 'update'])->name('update');
                Route::delete('/destroy/{id}', [PotensiLahanController::class, 'destroy'])->name('destroy');
            });
            Route::get('/daftar', [KelolaLahanController::class, 'index'])->name('daftar.index');
            Route::post('/tanam', [KelolaLahanController::class, 'storeTanam'])->name('tanam.store');
            Route::put('/tanam/{id}', [KelolaLahanController::class, 'updateTanam'])->name('tanam.update');
            Route::delete('/tanam/{id}', [KelolaLahanController::class, 'destroyTanam'])->name('tanam.destroy');
            Route::post('/panen', [KelolaLahanController::class, 'storePanen'])->name('panen.store');
            Route::put('/panen/{id}', [KelolaLahanController::class, 'updatePanen'])->name('panen.update');
            Route::delete('/panen/{id}', [KelolaLahanController::class, 'destroyPanen'])->name('panen.destroy');
            Route::post('/serapan', [KelolaLahanController::class, 'storeSerapan'])->name('serapan.store');
            Route::put('/serapan/{id}', [KelolaLahanController::class, 'updateSerapan'])->name('serapan.update');
            Route::delete('/serapan/{id}', [KelolaLahanController::class, 'destroySerapan'])->name('serapan.destroy');
            Route::put('/serapan/{id}/validasi', [KelolaLahanController::class, 'validasiSerapan'])->name('serapan.validasi');
            Route::get('/lahan/{id}/validasi-data', [KelolaLahanController::class, 'getValidasiData'])->name('lahan.validasi-data');
            Route::put('/lahan/{id}/validasi', [KelolaLahanController::class, 'validasiLahan'])->name('lahan.validasi');
        });

        // Rekapitulasi
        Route::get('/rekapitulasi', [RekapitulasiController::class, 'index'])->name('rekapitulasi.index');
        Route::get('/rekapitulasi/polsek', [RekapitulasiController::class, 'getPolsek'])->name('rekapitulasi.polsek');
        Route::get('/rekapitulasi/export', [RekapitulasiController::class, 'export'])->name('rekapitulasi.export');

        // Aktivitas Log
        Route::get('/aktivitas', [AktivitasController::class, 'index'])->name('aktivitas.index');
        Route::get('/aktivitas/{id}', [AktivitasController::class, 'show'])->name('aktivitas.show');
    });

    // 2. Group Khusus Operator
    Route::middleware(['checkrole:operator'])->prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'indexOperator'])->name('dashboard');

        Route::prefix('kelola-lahan')->name('kelola-lahan.')->group(function () {
            Route::prefix('potensi')->name('potensi.')->group(function () {
                Route::get('/', [PotensiLahanController::class, 'indexOperator'])->name('index');
                Route::post('/store', [PotensiLahanController::class, 'store'])->name('store');
                Route::put('/validasi/{id}', [PotensiLahanController::class, 'validasi'])->name('validasi');
                Route::put('/unvalidasi/{id}', [PotensiLahanController::class, 'unvalidasi'])->name('unvalidasi');
                Route::put('/update/{id}', [PotensiLahanController::class, 'update'])->name('update');
                Route::delete('/destroy/{id}', [PotensiLahanController::class, 'destroy'])->name('destroy');
            });

            Route::get('/daftar', [KelolaLahanController::class, 'indexOperator'])->name('daftar.index');
            Route::post('/tanam', [KelolaLahanController::class, 'storeTanam'])->name('tanam.store');
            Route::put('/tanam/{id}', [KelolaLahanController::class, 'updateTanam'])->name('tanam.update');
            Route::delete('/tanam/{id}', [KelolaLahanController::class, 'destroyTanam'])->name('tanam.destroy');
            Route::post('/panen', [KelolaLahanController::class, 'storePanen'])->name('panen.store');
            Route::put('/panen/{id}', [KelolaLahanController::class, 'updatePanen'])->name('panen.update');
            Route::delete('/panen/{id}', [KelolaLahanController::class, 'destroyPanen'])->name('panen.destroy');
            Route::post('/serapan', [KelolaLahanController::class, 'storeSerapan'])->name('serapan.store');
            Route::put('/serapan/{id}', [KelolaLahanController::class, 'updateSerapan'])->name('serapan.update');
            Route::delete('/serapan/{id}', [KelolaLahanController::class, 'destroySerapan'])->name('serapan.destroy');
            Route::get('/lahan/{id}/validasi-data', [KelolaLahanController::class, 'getValidasiData'])->name('lahan.validasi-data');
        });

        Route::get('/rekapitulasi', [OperatorRekapitulasi::class, 'index'])->name('rekapitulasi.index');
        Route::get('/rekapitulasi/polsek', [OperatorRekapitulasi::class, 'getPolsek'])->name('rekapitulasi.polsek');
        Route::get('/rekapitulasi/export', [OperatorRekapitulasi::class, 'export'])->name('rekapitulasi.export');
    });

    // 3. Group Khusus View
    Route::middleware(['checkrole:view'])->prefix('view')->name('view.')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'indexView'])->name('dashboard');

        Route::prefix('kelola-lahan')->name('kelola-lahan.')->group(function () {
            Route::prefix('potensi')->name('potensi.')->group(function () {
                Route::get('/', [PotensiLahanController::class, 'indexView'])->name('index');
                Route::post('/store', [PotensiLahanController::class, 'store'])->name('store');
                Route::put('/validasi/{id}', [PotensiLahanController::class, 'validasi'])->name('validasi');
                Route::put('/unvalidasi/{id}', [PotensiLahanController::class, 'unvalidasi'])->name('unvalidasi');
                Route::put('/update/{id}', [PotensiLahanController::class, 'update'])->name('update');
                Route::delete('/destroy/{id}', [PotensiLahanController::class, 'destroy'])->name('destroy');
            });

            Route::get('/', [KelolaLahanController::class, 'indexView'])->name('index');
            Route::post('/tanam', [KelolaLahanController::class, 'storeTanam'])->name('tanam.store');
            Route::put('/tanam/{id}', [KelolaLahanController::class, 'updateTanam'])->name('tanam.update');
            Route::post('/panen', [KelolaLahanController::class, 'storePanen'])->name('panen.store');
            Route::put('/panen/{id}', [KelolaLahanController::class, 'updatePanen'])->name('panen.update');
            Route::post('/serapan', [KelolaLahanController::class, 'storeSerapan'])->name('serapan.store');
            Route::put('/serapan/{id}', [KelolaLahanController::class, 'updateSerapan'])->name('serapan.update');
        });

        Route::get('/rekapitulasi', function () {
            return view('admin.rekapitulasi.index');
        })->name('rekapitulasi.index');
    });

    // Rute Profil Standar
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
