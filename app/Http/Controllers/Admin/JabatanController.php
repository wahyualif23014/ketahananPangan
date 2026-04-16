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
            $item->created_at_formatted = $item->created_at
                ? $item->created_at->format('d M Y')
                : null;

            return $item;
        });

        return view('admin.data-utama.jabatan.index', compact('jabatans'));
    }
}
