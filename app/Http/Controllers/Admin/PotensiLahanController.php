<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PotensiLahanController extends Controller
{
    public function index(Request $request)
    {
        // Parameter Pencarian dasar (opsional jika front-end mem-pass ?)
        $search = $request->get('search');

        // Query Utama
        // Status lahan = 1/2 sudah ditangani dan difilter pada Blade Dashboard (Aggregation).
        // Untuk tabel, kita panggil semua base Lahan yang sifatnya aktif.
        $query = DB::table('lahan')
            ->where('lahan.deletestatus', '!=', '0')
            ->where('lahan.status_lahan', '2') // Mengikuti instruksi terakhir filter status_lahan = 2
            ->leftJoin('anggota as editor', 'lahan.edit_oleh', '=', 'editor.id_anggota')
            ->leftJoin('anggota as validator', 'lahan.valid_oleh', '=', 'validator.id_anggota')
            ->leftJoin('wilayah', 'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
            ->select(
                'lahan.*', 
                'editor.nama_anggota as nama_editor', 
                'validator.nama_anggota as nama_validator',
                'wilayah.nama_wilayah as lokasi'
            );

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('lahan.cp_lahan', 'like', "%$search%")
                  ->orWhere('lahan.cp_polisi', 'like', "%$search%")
                  ->orWhere('wilayah.nama_wilayah', 'like', "%$search%");
            });
        }

        $lahans = $query->orderBy('lahan.id_lahan', 'desc')->paginate(15);

        return view('admin.kelola-lahan.potensi.index', compact('lahans'));
    }

    public function verify(Request $request, $id)
    {
        // Cek data lahan
        $lahan = DB::table('lahan')->where('id_lahan', $id)->first();
        if (!$lahan) {
            return back()->with('error', 'Data lahan tidak ditemukan.');
        }

        // Toggle Validation
        if (empty($lahan->tgl_valid)) {
            // Lakukan Validasi
            DB::table('lahan')->where('id_lahan', $id)->update([
                'tgl_valid' => Carbon::now(),
                'valid_oleh' => auth()->id() ?? 1 // Fallback id admin jika auth tidak sesuai format
            ]);
            $msg = 'Data lahan berhasil divalidasi.';
        } else {
            // Un-validasi
            DB::table('lahan')->where('id_lahan', $id)->update([
                'tgl_valid' => null,
                'valid_oleh' => null
            ]);
            $msg = 'Validasi lahan dicabut.';
        }

        return redirect()->back()->with('success', $msg);
    }

    public function update(Request $request, $id)
    {
        // Update logika standar sesuai request (luas_lahan, polisi, dll)
        DB::table('lahan')->where('id_lahan', $id)->update([
            'cp_lahan' => $request->cp_lahan,
            'no_cp_lahan' => $request->no_cp_lahan,
            'cp_polisi' => $request->cp_polisi,
            'no_cp_polisi' => $request->no_cp_polisi,
            'luas_lahan' => $request->luas_lahan,
            'id_jenis_lahan' => $request->id_jenis_lahan,
            'edit_oleh' => auth()->id() ?? 1,
            'tgl_edit' => Carbon::now()
        ]);

        return redirect()->back()->with('success', 'Data Lahan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        // Soft Delete
        DB::table('lahan')->where('id_lahan', $id)->update([
            'deletestatus' => '0'
        ]);

        return redirect()->back()->with('success', 'Data lahan berhasil dihapus.');
    }
}