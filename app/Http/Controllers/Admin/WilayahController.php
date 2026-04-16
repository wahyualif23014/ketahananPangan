<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function index() {
        return view( 'admin.data-utama.wilayah.index' );
    }

    public function updateLokasi(Request $request) {
        $request->validate([
            'id_wilayah' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        \Illuminate\Support\Facades\DB::table('wilayah')
            ->where('id_wilayah', $request->id_wilayah)
            ->update([
                'Latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

        return redirect()->back()->with('success', 'Lokasi peta berhasil diperbarui!');
    }
}
