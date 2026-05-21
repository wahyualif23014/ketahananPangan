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
        $jabatans = DB::table('jabatan')->get();
        return view('auth.register', compact('jabatans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'id_anggota'      => ['required', 'integer', 'unique:anggota,id_anggota'],
            'id_jabatan'      => ['required', 'exists:jabatan,id_jabatan'],
            'nama_anggota'    => ['required', 'string', 'max:100'],
            'username'        => ['required', 'string', 'max:255', 'unique:anggota,username'],
            'no_telp_anggota' => ['nullable', 'string', 'max:15'],
            'id_tugas'        => ['nullable', 'string', 'max:13'],
            'password'        => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            // Ambil ID terakhir dari id_pengguna dengan lockForUpdate untuk mencegah Race Condition
            $lastUser = DB::table('anggota')->lockForUpdate()->orderBy('id_pengguna', 'desc')->first();
            $newIdPengguna = $lastUser ? $lastUser->id_pengguna + 1 : 1;

            return User::create([
                'id_anggota'      => $request->id_anggota,
                'id_jabatan'      => $request->id_jabatan,
                'id_pengguna'     => $newIdPengguna,
                'id_tugas'        => $request->id_tugas ?? '0',
                'nama_anggota'    => $request->nama_anggota,
                'username'        => $request->username,
                'no_telp_anggota' => $request->no_telp_anggota,
                'role'            => 'view', // Dihardcode untuk mencegah Privilege Escalation
                'password'        => Hash::make($request->password),
            ]);
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}

