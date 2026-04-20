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
use App\Http\Controllers\Admin\KomoditiController;
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
            Route::post('/jabatan', [JabatanController::class, 'store'])->name('jabatan.store');
            Route::delete('/admin/jabatan/batch-delete', [JabatanController::class, 'batchDelete'])
            ->name('jabatan.batch-delete');
            Route::put('/jabatan/{id}', [JabatanController::class, 'update'])->name('jabatan.update');

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
        Route::get('/anggota', function () {
            $personels = User::all(); 
            $jabatans = \Illuminate\Support\Facades\DB::table('jabatan')->get();
            return view('admin.anggota.index', compact('personels', 'jabatans'));
        })->name('anggota.index');

        Route::post('/anggota', function (Illuminate\Http\Request $request) {
            $request->validate([
                'id_anggota' => ['required', 'integer', 'unique:anggota,id_anggota'],
                'id_jabatan' => ['required', 'exists:jabatan,id_jabatan'],
                'nama_anggota' => ['required', 'string', 'max:100'],
                'username' => ['required', 'string', 'max:255', 'unique:anggota,username'],
                'no_telp_anggota' => ['nullable', 'string', 'max:15'],
                'id_tugas' => ['nullable', 'string', 'max:13'],
                'role' => ['required', 'in:view,admin,operator'],
                'password' => ['required', 'confirmed'],
            ]);

            User::create([
                'id_anggota' => $request->id_anggota,
                'id_jabatan' => $request->id_jabatan,
                'id_tugas' => $request->id_tugas ?? '0',
                'nama_anggota' => $request->nama_anggota,
                'username' => $request->username,
                'no_telp_anggota' => $request->no_telp_anggota,
                'role' => $request->role,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            ]);

            return redirect()->route('admin.anggota.index')->with('success', 'Akun personel berhasil disimpan!');
        })->name('anggota.store');

        // Kelola Lahan
        Route::prefix('kelola-lahan')->name('kelola-lahan.')->group(function () {
            Route::prefix('potensi')->name('potensi.')->group(function () {
                Route::get('/', [PotensiLahanController::class, 'index'])->name('index');
                Route::post('/store', [PotensiLahanController::class, 'store'])->name('store');
                Route::put('/verify/{id}', [PotensiLahanController::class, 'verify'])->name('verify');
                Route::put('/update/{id}', [PotensiLahanController::class, 'update'])->name('update');
                Route::delete('/destroy/{id}', [PotensiLahanController::class, 'destroy'])->name('destroy');
            });
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

        Route::prefix('kelola-lahan')->name('kelola-lahan.')->group(function () {
            Route::get('/potensi', function () {
                $lahans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
                return view('operator.kelola-lahan.operator_potensi.operator_kelola_index', compact('lahans'));
            })->name('potensi.index');

            Route::get('/daftar', function () {
                return view('operator.kelola-lahan.operator_kelola.operator_kelola_index');
            })->name('daftar.index');
        });

        Route::get('/rekapitulasi', function () {
            return view('operator.rekapitulasi.operator_rekap');
        })->name('rekapitulasi.index');
    });

    // 3. Group Khusus View
    Route::middleware(['checkrole:view'])->prefix('view')->name('view.')->group(function () {
        Route::get('/dashboard', function () {
            return view('view.dashboard');
        })->name('dashboard');

        Route::prefix('kelola-lahan')->name('kelola-lahan.')->group(function () {
            Route::get('/', function () {
                return view('view.kelola-lahan.view_kelola');
            })->name('index');
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
