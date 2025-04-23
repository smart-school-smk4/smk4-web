<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index(){
        $guru = Guru::select('id', 'nama_guru', 'jabatan', 'no_hp_guru', 'email_guru')->get();
        return view('admin.guru', compact('guru'));
    }
}
