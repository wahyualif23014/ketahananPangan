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
            // Kita sesuaikan dengan kolom datetransaction, jika formatnya bisa diubah ke object Carbon
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
            'id_jabatan' => 'required|numeric|unique:jabatan,id_jabatan',
            'nama_jabatan' => 'required|string|max:255',
        ]);

        $jabatan = new Jabatan();
        $jabatan->id_jabatan = $request->id_jabatan;
        $jabatan->nama_jabatan = $request->nama_jabatan;
        $jabatan->deletestatus = 2;
        $jabatan->datetransaction = now();
        $jabatan->save();

        return redirect()->route('admin.jabatan.index')->with('success', 'Data Jabatan berhasil ditambahkan');
    }
}
