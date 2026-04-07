<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Untuk query manual jika perlu

class RekapitulasiController extends Controller
{
    public function index()
    {
        // Mengirim data status panen untuk widget dashboard
        $rekap = [
            'panen_normal' => 1250, // Contoh data count
            'gagal_panen' => 45,
            'panen_dini' => 12,
            'panen_tahunan' => 300,
            'total_titik' => '5,498',
            'polsek_aktif' => 659
        ];

        return view('admin.rekapitulasi.index', compact('rekap'));
    }
}