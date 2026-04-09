<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        // Fetch semua data dari tabel jabatan
        $jabatans = Jabatan::all()->map(function ($item) {
        $item->created_at_formatted = $item->created_at->format('d-m-Y H:i');
        return $item;
    });
        
        return view('admin.data-utama.jabatan.index', compact('jabatans'));
    }
}