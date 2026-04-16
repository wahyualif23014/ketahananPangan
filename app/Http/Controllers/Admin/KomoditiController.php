<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KomoditiController extends Controller
{
    public function index()
    {
        return view('admin.data-utama.komoditi.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_komoditi' => 'required|string|max:255',
            'nama_komoditi' => 'required|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::table('komoditi')->insert([
            'jenis_komoditi' => $request->jenis_komoditi,
            'nama_komoditi' => $request->nama_komoditi,
            'datetransaction' => now(),
            'deletestatus' => '1',
        ]);

        return redirect()->route('admin.komoditi.index')->with('success', 'Komoditi berhasil ditambahkan!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_komoditi' => 'required',
            'jenis_komoditi' => 'required|string|max:255',
            'nama_komoditi' => 'required|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::table('komoditi')
            ->where('id_komoditi', $request->id_komoditi)
            ->update([
                'jenis_komoditi' => $request->jenis_komoditi,
                'nama_komoditi' => $request->nama_komoditi,
                'datetransaction' => now(),
            ]);

        return redirect()->route('admin.komoditi.index')->with('success', 'Data Komoditi berhasil diperbarui!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_komoditi' => 'required'
        ]);

        // Soft delete assuming deletestatus 0 = deleted
        \Illuminate\Support\Facades\DB::table('komoditi')
            ->where('id_komoditi', $request->id_komoditi)
            ->update(['deletestatus' => '0', 'datetransaction' => now()]);

        return redirect()->route('admin.komoditi.index')->with('success', 'Data Komoditi berhasil dihapus!');
    }
}