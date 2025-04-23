<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index2(Request $request)
    {
        $siswa = Siswa::with(['kelas', 'jurusan'])->get();
        return view('admin.siswa', compact('siswa'));
    }
    //GET api/admin/siswa
    public function index(Request $request)
    {
        // Ambil semua data siswa
        $siswa = Siswa::with(['kelas', 'jurusan'])->get();

        return response()->json([
            'message' => 'Siswa ditemukan',
            'data' => $siswa
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_siswa'    => 'required|string|max:255',
            'nisn'          => 'required|integer|unique:siswa,nisn',
            'tanggal_lahir' => 'required|date',
            'foto_siswa'    => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat'        => 'required|string',
            'no_hp'         => 'required|numeric',
            'email'         => 'required|email|unique:siswa,email',
            'id_jurusan'    => 'required|exists:jurusan,id',
            'id_kelas'      => 'required|exists:kelas,id',
        ]);

        // Cek apakah ada upload foto
        if ($request->hasFile('foto_siswa')) {
            $fotoPath = $request->file('foto_siswa')->store('public/foto_siswa');
            $fotoName = basename($fotoPath);
        } else {
            $fotoName = null;
        }

        // Simpan data siswa
        $siswa = Siswa::create([
            'nama_siswa'    => $request->nama_siswa,
            'nisn'          => $request->nisn,
            'tanggal_lahir' => $request->tanggal_lahir,
            'foto_siswa'    => $fotoName,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat'        => $request->alamat,
            'no_hp'         => $request->no_hp,
            'email'         => $request->email,
            'id_jurusan'    => $request->id_jurusan,
            'id_kelas'      => $request->id_kelas,
        ]);

        // Jika request dari API, kembalikan response JSON
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Siswa berhasil ditambahkan',
                'data'    => $siswa,
            ], 201);
        }

        // Jika request dari Web (Blade), redirect ke halaman daftar siswa
        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan');
    }
}
