<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TingkatKesatuanController extends Controller
{
    public function index() {
        return view( 'admin.data-utama.tingkat-kesatuan.index' );
    }
}
