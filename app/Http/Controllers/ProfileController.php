<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $jabatan = \Illuminate\Support\Facades\DB::table('jabatan')->where('id_jabatan', $user->id_jabatan)->value('nama_jabatan') ?? 'Tidak Ada Jabatan';
        $tingkat = \Illuminate\Support\Facades\DB::table('tingkat')->where('id_tingkat', $user->id_tugas)->first();
        $wilayah = $tingkat ? $tingkat->id_tingkat . ' - ' . $tingkat->nama_tingkat : ($user->id_tugas ? $user->id_tugas . ' - Data Lokasi Tidak Ditemukan' : 'Semua Wilayah');

        return view('profile.edit', [
            'user' => $user,
            'jabatan' => $jabatan,
            'wilayah' => $wilayah,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->update(['deletestatus' => '1']);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
