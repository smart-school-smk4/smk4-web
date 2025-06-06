<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use Illuminate\Http\Request;

class AbsensiSiswaController extends Controller
{
    public function index()
    {
        $absensi = AbsensiSiswa::with('siswa')->latest()->get();
        return view('admin.presensi.siswa', compact('absensi'));
    }
}
