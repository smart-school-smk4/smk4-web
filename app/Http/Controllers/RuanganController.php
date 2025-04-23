<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index(){
        $ruangan = Ruangan::select('id', 'nama_ruangan')->with(['kelas:id,nama_kelas', 'jurusan:id,nama_jurusan'])->get();
        return view('admin.ruangan', compact('ruangan'));
    }
}
