<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        // Ambil semua data jabatan untuk ditampilkan di dropdown
        $jabatans = DB::table('jabatan')->get();
        return view('auth.register', compact('jabatans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'id_anggota' => ['required', 'integer', 'unique:anggota,id_anggota'],
            'id_jabatan' => ['required', 'exists:jabatan,id_jabatan'], // Pastikan ID jabatan valid
            'nama_anggota' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:anggota'],
            'role' => ['required', 'in:view,admin,operator'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'id_anggota' => $request->id_anggota,
            'id_jabatan' => $request->id_jabatan, // Simpan ID hasil pilihan user
            'id_tugas' => $request->id_tugas ?? 0,
            'nama_anggota' => $request->nama_anggota,
            'username' => $request->username,
            'no_telp_anggota' => $request->no_telp_anggota,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
