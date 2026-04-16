<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::all()->map(function ($item) {
            $item->created_at_formatted = $item->created_at ? $item->created_at->format('d-m-Y H:i') : '-';
            return $item;
        });
        
        return view('admin.data-utama.jabatan.index', compact('jabatans'));
    }

    public function batchDelete(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:jabatans,id_jabatan', 
        ]);

        try {
            $count = count($validated['ids']);

            Jabatan::whereIn('id_jabatan', $validated['ids'])->delete();
            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil menghapus $count data jabatan."
            ], 200);

        } catch (\Exception $e) {
            Log::error("Gagal Batch Delete: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }
        public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
        ]);

        try {
            $jabatan = Jabatan::findOrFail($id);
            $jabatan->update([
                'nama_jabatan' => $validated['nama_jabatan']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data jabatan berhasil diperbarui.',
                'data' => $jabatan
            ], 200);

        } catch (\Exception $e) {
            Log::error("Gagal Update Jabatan: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data.'
            ], 500);
        }
    }
}