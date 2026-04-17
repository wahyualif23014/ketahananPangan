<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PotensiLahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PotensiLahanController extends Controller {
    public function index() {
        // ===========================
        // DATA STATISTIK (di blade)
        // ===========================
        $summary = [ 'total_ha' => '0' ];
        $cats    = [];

        // ===========================
        // DATA TABEL: DAFTAR LAHAN
        // Grouped per Kabupaten
        // ===========================

        // 1. Ambil semua anggota untuk lookup nama
        $anggotaMap = DB::table('anggota')->pluck('nama_anggota', 'id_anggota');

        // 3. Ambil semua wilayah untuk lookup nama kab/kec/desa
        $wilayahMap = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');

        // 2. Ambil semua data lahan (aktif) dipaginasi
        $lahanList = DB::table('lahan')
            ->where('deletestatus', '!=', '0')
            ->orderBy('id_wilayah')
            ->paginate(25);

        // 4. Transform data untuk view
        $lahanList->getCollection()->transform(function ($lahan) use ($wilayahMap, $anggotaMap) {
            $parts = explode('.', $lahan->id_wilayah);

            // Resolve Kabupaten (format: XX.XX)
            $kabId   = count($parts) >= 2 ? $parts[0] . '.' . $parts[1] : $lahan->id_wilayah;
            $kabNama = $wilayahMap[$kabId] ?? ('Wilayah ' . $kabId);

            // Resolve Kecamatan (format: XX.XX.XXX)
            $kecId   = count($parts) >= 3 ? $parts[0] . '.' . $parts[1] . '.' . $parts[2] : ($kabId . '.000');
            $kecNama = $wilayahMap[$kecId] ?? ('Kec. ' . $kecId);

            // Resolve Desa (full id_wilayah)
            $desaNama = $wilayahMap[$lahan->id_wilayah] ?? $lahan->id_wilayah;

            // Resolve edit_oleh & valid_oleh
            $editNama  = $lahan->edit_oleh  ? ($anggotaMap[$lahan->edit_oleh]  ?? $lahan->edit_oleh)  : null;
            $validNama = $lahan->valid_oleh ? ($anggotaMap[$lahan->valid_oleh] ?? $lahan->valid_oleh) : null;

            return [
                'id_lahan'       => $lahan->id_lahan,
                'cp_lahan'       => $lahan->cp_lahan,      // Polisi Penggerak
                'no_cp_lahan'    => $lahan->no_cp_lahan,
                'cp_polisi'      => $lahan->cp_polisi,     // Penanggung Jawab
                'no_cp_polisi'   => $lahan->no_cp_polisi,
                'alamat_lahan'   => $lahan->alamat_lahan,
                'longitude'      => $lahan->longitude,
                'latitude'       => $lahan->latitude,
                'luas_lahan'     => $lahan->luas_lahan,
                'id_jenis_lahan' => $lahan->id_jenis_lahan,
                'status_lahan'   => $lahan->status_lahan,
                'edit_oleh'      => $editNama,
                'tgl_edit'       => $lahan->tgl_edit,
                'valid_oleh'     => $validNama,
                'tgl_valid'      => $lahan->tgl_valid,
                'kec_nama'       => $kecNama,
                'desa_nama'      => $desaNama,
                'kab_nama'       => $kabNama,
            ];
        });

        return view('admin.kelola-lahan.potensi.index', compact('summary', 'cats', 'lahanList'));
    }
}