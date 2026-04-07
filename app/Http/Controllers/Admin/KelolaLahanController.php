<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelolaLahan;
use Illuminate\Http\Request;

class KelolaLahanController extends Controller
{
    public function index()
    {
        // Data Stats Tanam & Panen
        $stats = [
            'tanam' => [
                'total' => '242.74',
                'detail' => [
                    'Milik Polri' => '0',
                    'Produktif (Poktan)' => '107.08',
                    'Masyarakat' => '39.31'
                ]
            ],
            'panen' => [
                'total' => '243.72',
                'detail' => [
                    'Milik Polri' => '0.98',
                    'Produktif (Poktan)' => '107.08',
                    'Masyarakat' => '39.31'
                ]
            ]
        ];

        return view('admin.kelola-lahan.lahan.index', compact('stats'));
    }
}