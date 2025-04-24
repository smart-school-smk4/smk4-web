<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index(){
        $ruangan = Ruangan::with('jurusan', 'kelas')->get();
        return view('admin.ruangan.index', compact('ruangan'));
    }

    public function create(){
        $kelas = Kelas::all();
        $jurusan = Jurusan::all();
        return view('admin.ruangan.create', compact('kelas', 'jurusan'));
    }

    public function store(Request $request){
        $request->validate([
            'nama_ruangan' => 'required|string|max:255',
            'id_kelas' => 'required|exists:kelas,id',
            'id_jurusan' => 'required|exists:jurusan,id',
        ]);

        Ruangan::create($request->all());
        
        return redirect()->route('admin.ruangan.index')->with('success', 'Data ruangan berhasil ditambahkan');
    }

    public function edit(Ruangan $ruangan){
        $kelas = Kelas::all();
        $jurusan = Jurusan::all();
        return view('admin.ruangan.edit', compact('ruangan', 'kelas', 'jurusan'));
    }
    
    public function update(Request $request, Ruangan $ruangan){
        $request->validate([
            'nama_ruangan' => 'required|string|max:255',
            'id_kelas' => 'required|exists:kelas,id',
            'id_jurusan' => 'required|exists:jurusan,id',
        ]);

        $ruangan->update($request->all());

        return redirect()->route('admin.ruangan.index')->with('success', 'Data ruangan berhasil diperbarui');
    }

    public function destroy(Ruangan $ruangan){
        $ruangan->delete();
        return redirect()->route('admin.ruangan.index')->with('success', 'Data ruangan berhasil dihapus');
    }
}