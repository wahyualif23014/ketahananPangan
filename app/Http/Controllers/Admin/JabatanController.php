<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::where('deletestatus', '2')
            ->get()
            ->map(function ($item) {
                $item->created_at_formatted = $item->datetransaction
                    ? \Carbon\Carbon::parse($item->datetransaction)->format('d M Y')
                    : null;
                return $item;
            });

        return view('admin.data-utama.jabatan.index', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_jabatan' => 'required|numeric',
            'nama_jabatan' => 'required|string|max:255',
        ]);

        // Cek apakah ID sudah ada (aktif maupun terhapus)
        $existing = Jabatan::find($request->id_jabatan);

        if ($existing) {
            if ($existing->deletestatus == '2') {
                return back()->withErrors(['id_jabatan' => 'ID Jabatan ini sudah digunakan dan masih aktif.'])->withInput();
            }
            
            // Jika sudah ada tapi terhapus (deletestatus = '1'), kita pulihkan dan update namanya
            $oldNama = $existing->nama_jabatan;
            $existing->nama_jabatan = $request->nama_jabatan;
            $existing->deletestatus = '2'; // Aktifkan kembali
            $existing->datetransaction = now();
            $existing->save();

            try {
                AktivitasLog::catat('create', 'jabatan', [
                    'record_id'   => $request->id_jabatan,
                    'label_modul' => 'Jabatan: ' . $request->nama_jabatan,
                    'data_lama'   => ['nama_jabatan' => $oldNama, 'deletestatus' => '1'],
                    'data_baru'   => ['id_jabatan' => $request->id_jabatan, 'nama_jabatan' => $request->nama_jabatan, 'deletestatus' => '2'],
                    'keterangan'  => 'Memulihkan & mengubah jabatan terhapus: ' . $request->nama_jabatan,
                ]);
            } catch (\Exception $e) {
                Log::warning('AktivitasLog gagal (restore jabatan): ' . $e->getMessage());
            }

            return redirect()->route('admin.jabatan.index')->with('success', 'Data Jabatan (yang sebelumnya terhapus) berhasil dipulihkan dan diperbarui');
        }

        // Jika benar-benar baru
        $jabatan = new Jabatan();
        $jabatan->id_jabatan    = $request->id_jabatan;
        $jabatan->nama_jabatan  = $request->nama_jabatan;
        $jabatan->deletestatus  = '2';
        $jabatan->datetransaction = now();
        $jabatan->save();

        try {
            AktivitasLog::catat('create', 'jabatan', [
                'record_id'   => $request->id_jabatan,
                'label_modul' => 'Jabatan: ' . $request->nama_jabatan,
                'data_baru'   => ['id_jabatan' => $request->id_jabatan, 'nama_jabatan' => $request->nama_jabatan],
                'keterangan'  => 'Tambah jabatan baru: ' . $request->nama_jabatan,
            ]);
        } catch (\Exception $e) {
            Log::warning('AktivitasLog gagal (store jabatan): ' . $e->getMessage());
        }

        return redirect()->route('admin.jabatan.index')->with('success', 'Data Jabatan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:255',
        ]);

        $jabatan = Jabatan::findOrFail($id);
        $oldNama = $jabatan->nama_jabatan;
        $jabatan->nama_jabatan   = $request->nama_jabatan;
        $jabatan->datetransaction = now();
        $jabatan->save();

        try {
            AktivitasLog::catat('update', 'jabatan', [
                'record_id'   => $id,
                'label_modul' => 'Jabatan: ' . $request->nama_jabatan,
                'data_lama'   => ['nama_jabatan' => $oldNama],
                'data_baru'   => ['nama_jabatan' => $request->nama_jabatan],
                'keterangan'  => 'Edit jabatan #' . $id . ': ' . $oldNama . ' → ' . $request->nama_jabatan,
            ]);
        } catch (\Exception $e) {
            Log::warning('AktivitasLog gagal (update jabatan): ' . $e->getMessage());
        }

        return redirect()->route('admin.jabatan.index')->with('success', 'Data Jabatan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $jabatan = Jabatan::findOrFail($id);

        try {
            AktivitasLog::catat('delete', 'jabatan', [
                'record_id'   => $id,
                'label_modul' => 'Jabatan: ' . $jabatan->nama_jabatan,
                'data_lama'   => ['id_jabatan' => $jabatan->id_jabatan, 'nama_jabatan' => $jabatan->nama_jabatan],
                'keterangan'  => 'Hapus jabatan: ' . $jabatan->nama_jabatan,
            ]);
        } catch (\Exception $e) {
            Log::warning('AktivitasLog gagal (destroy jabatan): ' . $e->getMessage());
        }

        $jabatan->deletestatus = '1'; // ENUM('1','2'): '1' = soft-deleted, '2' = active
        $jabatan->save();

        return redirect()->route('admin.jabatan.index')->with('success', 'Data Jabatan berhasil dihapus');
    }
}
