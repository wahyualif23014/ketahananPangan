<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function index() {
        return view( 'admin.data-utama.wilayah.index' );
    }

    public function updateLokasi(Request $request) {
        $request->validate([
            'id_wilayah' => 'required',
            'latitude'   => 'required|numeric',
            'longitude'  => 'required|numeric',
        ]);

        $old = \Illuminate\Support\Facades\DB::table('wilayah')
            ->where('id_wilayah', $request->id_wilayah)
            ->first();

        \Illuminate\Support\Facades\DB::table('wilayah')
            ->where('id_wilayah', $request->id_wilayah)
            ->update([
                'Latitude'  => $request->latitude,
                'longitude' => $request->longitude,
            ]);

        AktivitasLog::catat('update', 'wilayah', [
            'record_id'   => $request->id_wilayah,
            'label_modul' => 'Wilayah: ' . ($old->nama_wilayah ?? $request->id_wilayah),
            'data_lama'   => $old ? ['Latitude' => $old->Latitude ?? null, 'longitude' => $old->longitude ?? null] : null,
            'data_baru'   => ['Latitude' => $request->latitude, 'longitude' => $request->longitude],
            'keterangan'  => 'Update koordinat peta wilayah ' . ($old->nama_wilayah ?? $request->id_wilayah) . ' → Lat: ' . $request->latitude . ', Lng: ' . $request->longitude,
        ]);

        return redirect()->back()->with('success', 'Lokasi peta berhasil diperbarui!');
    }
}
