<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function indexSiswa(){
        return view ('admin.presensi.siswa');
    }

    public function indexGuru(){
        return view('admin.presensi.guru');
    }
}
