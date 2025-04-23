<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index(){
        $jurusan = Jurusan::select('id', 'nama_jurusan')->get();
        return view('admin.jurusan', compact('jurusan'));
    }
}
