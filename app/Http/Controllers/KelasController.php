<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(){
        $kelas = Kelas::select('id', 'nama_kelas')->with(['jurusan:id,nama_jurusan'])->get();
        return view('admin.kelas', compact('kelas'));
    }
}
