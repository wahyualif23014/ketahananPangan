<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect halaman utama ke login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // 1. Route Khusus Admin
    Route::middleware(['checkrole:admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Tambahkan rute khusus admin lainnya di sini
    });

    // 2. Route Khusus Operator
    Route::middleware(['checkrole:operator'])->group(function () {
        Route::get('/operator/dashboard', function () {
            return view('operator.dashboard');
        })->name('operator.dashboard');

        // Tambahkan rute khusus operator lainnya di sini
    });

    // 3. Route Khusus View (Anggota)
    // Pastikan baris ini ada di web.php
    Route::middleware(['checkrole:view'])->group(function () {
        Route::get('/view/dashboard', function () {
            return view('view.dashboard');
        })->name('view.dashboard'); // Nama ini harus ada!
    });

    // Rute profil standar (opsional)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
