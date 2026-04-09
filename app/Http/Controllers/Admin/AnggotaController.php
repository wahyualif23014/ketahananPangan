<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
// Menggunakan Model User yang sudah kita sinkronkan ke tabel anggota
use Illuminate\Http\Request;

class AnggotaController extends Controller {
    public function index() {
        $personels = User::with( [ 'jabatan' ] )->get();
        return view( 'admin.anggota.index', compact( 'personels' ) );
    }
}