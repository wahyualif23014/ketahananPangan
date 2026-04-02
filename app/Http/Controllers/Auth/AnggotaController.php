<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AnggotaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nrp' => ['required', 'string', 'unique:users,nrp'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,operator,anggota'],
        ]);

        // Mapping Role ke statusadmin sesuai skema SQL Anda
        $statusMap = [
            'admin' => '1',
            'operator' => '2',
            'anggota' => '3',
        ];

        $user = User::create([
            'name' => $request->name,
            'nrp' => $request->nrp,
            'password' => Hash::make($request->password),
            'statusadmin' => $statusMap[$request->role],
        ]);

        $user->assignRole($request->role);

        return back()->with('success', 'Personil berhasil didaftarkan.');
    }
}