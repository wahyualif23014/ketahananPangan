<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PotensiLahan;
// Pastikan model sudah di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PotensiLahanController extends Controller {
    public function index() {
        // Logika Real: $data = PotensiLahan::all();

        // Data Dummy untuk testing UI SIKAP PRESISI
        $summary = [
            'total_ha' => '170,715.11',
            'rincian' => [
                'Milik Polri' => '9.63',
                'Produktif (Poktan)' => '34,882.86',
                'Produktif (Masyarakat)' => '27,316.49',
                'Hutan (Perhutani)' => '22,573.23',
                'Luas Baku Sawah' => '64,792.29',
                'Lainnya' => '107.52'
            ]
        ];
        $cats = [
            [ 'label' => 'Milik Polri', 'val' => '9.63' ],
            [ 'label' => 'Poktan Binaan', 'val' => '34,903.96' ],
            [ 'label' => 'Masyarakat', 'val' => '27,320.94' ],
            [ 'label' => 'Hutan Sosial', 'val' => '20,690.15' ],
            [ 'label' => 'LBS (Sawah)', 'val' => '65,013.95' ],
            [ 'label' => 'Lainnya', 'val' => '108.02' ],
        ];

        return view( 'admin.kelola-lahan.potensi.index', compact( 'summary', 'cats' ) );
    }
}