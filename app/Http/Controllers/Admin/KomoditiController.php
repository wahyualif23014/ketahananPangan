<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KomoditiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $query = DB::table('komoditi')->where('deletestatus', '!=', '0');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_komoditi', 'like', "%{$search}%")
                  ->orWhere('jenis_komoditi', 'like', "%{$search}%");
            });
        }

        $allKomoditi = $query->get();

        // Semua jenis untuk datalist di modal (tidak difilter)
        $allKomoditiForList = DB::table('komoditi')
            ->where('deletestatus', '!=', '0')
            ->get();

        return view('admin.data-utama.komoditi.index', compact('allKomoditi', 'allKomoditiForList', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_komoditi' => 'required|string|max:255',
            'nama_komoditi' => 'required|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::table('komoditi')->insert([
            'jenis_komoditi' => $request->jenis_komoditi,
            'nama_komoditi'  => $request->nama_komoditi,
            'datetransaction' => now(),
            'deletestatus'   => '2',
        ]);

        $newId = DB::table('komoditi')->latest('id_komoditi')->value('id_komoditi');
        AktivitasLog::catat('create', 'komoditi', [
            'record_id'   => $newId,
            'label_modul' => $request->jenis_komoditi . ' - ' . $request->nama_komoditi,
            'data_baru'   => ['jenis_komoditi' => $request->jenis_komoditi, 'nama_komoditi' => $request->nama_komoditi],
            'keterangan'  => 'Tambah komoditi baru: ' . $request->nama_komoditi . ' (kategori: ' . $request->jenis_komoditi . ')',
        ]);

        return redirect()->back()->with('success', 'Komoditi berhasil ditambahkan!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_komoditi' => 'required',
            'jenis_komoditi' => 'required|string|max:255',
            'nama_komoditi' => 'required|string|max:255',
        ]);

        $old = DB::table('komoditi')->where('id_komoditi', $request->id_komoditi)->first();

        \Illuminate\Support\Facades\DB::table('komoditi')
            ->where('id_komoditi', $request->id_komoditi)
            ->update([
                'jenis_komoditi' => $request->jenis_komoditi,
                'nama_komoditi'  => $request->nama_komoditi,
                'datetransaction' => now(),
            ]);

        AktivitasLog::catat('update', 'komoditi', [
            'record_id'   => $request->id_komoditi,
            'label_modul' => $request->jenis_komoditi . ' - ' . $request->nama_komoditi,
            'data_lama'   => $old ? ['jenis_komoditi' => $old->jenis_komoditi, 'nama_komoditi' => $old->nama_komoditi] : null,
            'data_baru'   => ['jenis_komoditi' => $request->jenis_komoditi, 'nama_komoditi' => $request->nama_komoditi],
            'keterangan'  => 'Edit komoditi #' . $request->id_komoditi . ': ' . $request->nama_komoditi,
        ]);

        return redirect()->back()->with('success', 'Data Komoditi berhasil diperbarui!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_komoditi' => 'nullable',
            'jenis_komoditi' => 'nullable|string'
        ]);

        if ($request->filled('id_komoditi')) {
            // Soft delete specific komoditi
            $old = DB::table('komoditi')->where('id_komoditi', $request->id_komoditi)->first();

            \Illuminate\Support\Facades\DB::table('komoditi')
                ->where('id_komoditi', $request->id_komoditi)
                ->update(['deletestatus' => '0']);

            AktivitasLog::catat('delete', 'komoditi', [
                'record_id'   => $request->id_komoditi,
                'label_modul' => $old ? $old->jenis_komoditi . ' - ' . $old->nama_komoditi : 'ID #' . $request->id_komoditi,
                'data_lama'   => $old ? ['jenis_komoditi' => $old->jenis_komoditi, 'nama_komoditi' => $old->nama_komoditi] : null,
                'keterangan'  => 'Hapus komoditi: ' . ($old ? $old->nama_komoditi : '#' . $request->id_komoditi),
            ]);

            return redirect()->back()->with('success', 'Data Komoditi berhasil dihapus!');
        } elseif ($request->filled('jenis_komoditi')) {
            // Soft delete entire jenis komoditi
            $items = DB::table('komoditi')->where('jenis_komoditi', $request->jenis_komoditi)->where('deletestatus', '!=', '0')->get();
            
            if ($items->count() > 0) {
                \Illuminate\Support\Facades\DB::table('komoditi')
                    ->where('jenis_komoditi', $request->jenis_komoditi)
                    ->update(['deletestatus' => '0']);
                    
                AktivitasLog::catat('delete', 'komoditi', [
                    'record_id'   => $items->first()->id_komoditi,
                    'label_modul' => $request->jenis_komoditi,
                    'data_lama'   => ['count' => $items->count(), 'items' => $items->pluck('nama_komoditi')->toArray()],
                    'keterangan'  => 'Hapus kategori komoditi beserta ' . $items->count() . ' item di dalamnya: ' . $request->jenis_komoditi,
                ]);
            }

            return redirect()->back()->with('success', 'Kategori Komoditi beserta isinya berhasil dihapus!');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data: ID atau Jenis Komoditi tidak valid.');
    }
}
