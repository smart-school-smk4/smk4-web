<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index()
    {
        $siswa = Siswa::select('id', 'nama_siswa', 'nisn', 'jenis_kelamin', 'no_hp', 'email')
                    ->with(['jurusan:id,nama_jurusan', 'kelas:id,nama_kelas'])
                    ->get();

        return view('admin.siswa', compact('siswa'));
    }
    public function create()
    {
        $jurusan = Jurusan::all();
        $kelas = Kelas::all();
        return view('admin.siswa.create', compact('jurusan', 'kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'required|integer|unique:siswa,nisn',
            'tanggal_lahir' => 'required|date',
            'foto_siswa' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'no_hp' => 'required|numeric',
            'email' => 'required|email|unique:siswa,email',
            'id_jurusan' => 'required|exists:jurusan,id',
            'id_kelas' => 'required|exists:kelas,id',
        ]);

        // Simpan gambar
        $fotoPath = $request->file('foto_siswa')->store('public/foto_siswa');
        $fotoName = basename($fotoPath);

        // Simpan data siswa
        Siswa::create([
            'nama_siswa' => $request->nama_siswa,
            'nisn' => $request->nisn,
            'tanggal_lahir' => $request->tanggal_lahir,
            'foto_siswa' => $fotoName,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'id_jurusan' => $request->id_jurusan,
            'id_kelas' => $request->id_kelas,
        ]);

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan');
    }
}
