<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(){
        $kelas = Kelas::with('jurusan')->get();
        $kelas = Kelas::paginate(10);
        return view('admin.kelas.index', compact('kelas'));
    }

    public function create(){
        $jurusan = Jurusan::all();
        return view('admin.kelas.create', compact('jurusan'));
    }

    public function store(Request $request){
        $request->validate([
            'nama_kelas' => 'required',
            'id_jurusan' => 'required|exists:jurusan,id',
        ]);
    
        Kelas::create($request->all());

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan');
    }

    public function edit(Kelas $kelas)
    {
        $jurusan = Jurusan::all();
        return view('admin.kelas.edit', compact('kelas', 'jurusan'));
    }

    public function update(Request $request, Kelas $kelas){
        $request->validate([
            'nama_kelas' => 'required',
            'id_jurusan' => 'required|exists:jurusan,id',
        ]);
        
        $kelas->update($request->all());

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus');
    }
}
