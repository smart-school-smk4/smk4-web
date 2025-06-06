<?php

namespace App\Http\Controllers;

use App\Models\AbsensiGuru;
use Illuminate\Http\Request;

class AbsensiGuruController extends Controller
{
    public function index()
    {
        $absensi = AbsensiGuru::with('guru')->latest()->get();
        return view('admin.presensi.guru', compact('absensi'));
    }
}
