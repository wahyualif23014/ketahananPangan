<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\AktivitasLog;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $query = Anggota::with('jabatan')->orderBy('nama_anggota');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_anggota', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%")
                  ->orWhereHas('jabatan', function ($q2) use ($search) {
                      $q2->where('nama_jabatan', 'like', "%{$search}%");
                  });
            });
        }

        $personels = $query->paginate(250)->appends(['search' => $search]);
        $jabatans = Jabatan::all();

        return view('admin.anggota.index', compact('personels', 'jabatans', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_anggota' => 'required|integer|unique:anggota,id_anggota',
            'username' => 'required|unique:anggota,username',
            'nama_anggota' => 'required|string|max:255',
            'id_jabatan' => 'required|exists:jabatan,id_jabatan',
            'role' => 'required|in:admin,operator,view',
            'id_tugas' => 'nullable|string|max:255',
            'no_telp_anggota' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
        ]);

        Anggota::create([
            'id_anggota' => $request->id_anggota,
            'id_jabatan' => $request->id_jabatan,
            'id_tugas' => $request->id_tugas ?? '0',
            'nama_anggota' => $request->nama_anggota,
            'no_telp_anggota' => $request->no_telp_anggota,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'deletestatus' => '2',
            'datetransaction' => Carbon::now(),
        ]);

        AktivitasLog::catat('create', 'anggota', [
            'record_id'   => $request->id_anggota,
            'label_modul' => 'Personel: ' . $request->nama_anggota,
            'data_baru'   => $request->only(['id_anggota','nama_anggota','username','role','id_jabatan','no_telp_anggota']),
            'keterangan'  => 'Tambah data personel baru: ' . $request->nama_anggota . ' (' . $request->role . ')',
        ]);

        return redirect()->route('admin.anggota.index')->with('success', 'Data personel berhasil ditambahkan.');
    }

    public function update(Request $request, $id_anggota)
    {
        $anggota = Anggota::findOrFail($id_anggota);

        $request->validate([
            'username' => 'required|unique:anggota,username,' . $id_anggota . ',id_anggota',
            'nama_anggota' => 'required|string|max:255',
            'id_jabatan' => 'required|exists:jabatan,id_jabatan',
            'role' => 'required|in:admin,operator,view',
            'id_tugas' => 'nullable|string|max:255',
            'no_telp_anggota' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $dataUpdate = [
            'id_jabatan' => $request->id_jabatan,
            'id_tugas' => $request->id_tugas ?? '0',
            'nama_anggota' => $request->nama_anggota,
            'no_telp_anggota' => $request->no_telp_anggota,
            'username' => $request->username,
            'role' => $request->role,
            'datetransaction' => Carbon::now(),
        ];

        if ($request->filled('password')) {
            $dataUpdate['password'] = Hash::make($request->password);
        }

        $anggota->update($dataUpdate);

        AktivitasLog::catat('update', 'anggota', [
            'record_id'   => $id_anggota,
            'label_modul' => 'Personel: ' . $request->nama_anggota,
            'data_baru'   => $request->only(['nama_anggota','username','role','id_jabatan','no_telp_anggota']),
            'keterangan'  => 'Edit data personel: ' . $request->nama_anggota . ' (ID #' . $id_anggota . ')',
        ]);

        return redirect()->route('admin.anggota.index')->with('success', 'Data personel berhasil diperbarui.');
    }

    public function destroy($id_anggota)
    {
        $anggota = Anggota::findOrFail($id_anggota);
        AktivitasLog::catat('delete', 'anggota', [
            'record_id'   => $id_anggota,
            'label_modul' => 'Personel: ' . $anggota->nama_anggota,
            'data_lama'   => ['id_anggota'=>$anggota->id_anggota,'nama_anggota'=>$anggota->nama_anggota,'username'=>$anggota->username,'role'=>$anggota->role],
            'keterangan'  => 'Hapus data personel: ' . $anggota->nama_anggota . ' (ID #' . $id_anggota . ')',
        ]);
        $anggota->delete();

        return redirect()->route('admin.anggota.index')->with('success', 'Data personel berhasil dihapus.');
    }
}