<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $siswa = Siswa::with(['kelas', 'jurusan'])->latest()->get();
        $siswa = Siswa::paginate(10);
        $kelas = Kelas::all(); 
        $jurusan = Jurusan::all(); 
        return view('admin.siswa.index', compact('siswa', 'kelas', 'jurusan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelas = Kelas::all();
        $jurusan = Jurusan::all();
        
        return view('admin.siswa.create', compact('kelas', 'jurusan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi data
        $validator = Validator::make($request->all(), [
            'foto_siswa' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'required|numeric|unique:siswa,nisn',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'email' => 'required|email|unique:siswa,email',
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'id_kelas' => 'required|exists:kelas,id',
            'id_jurusan' => 'required|exists:jurusan,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Upload foto siswa
        if ($request->hasFile('foto_siswa')) {
            $foto = $request->file('foto_siswa');
            $fotoName = time() . '_' . $foto->getClientOriginalName();
            $fotoPath = $foto->storeAs('siswa', $fotoName, 'public');
        }

        // Simpan data siswa
        $siswa = new Siswa();
        $siswa->foto_siswa = $fotoPath ?? null;
        $siswa->nama_siswa = $request->nama_siswa;
        $siswa->nisn = $request->nisn;
        $siswa->tanggal_lahir = $request->tanggal_lahir;
        $siswa->jenis_kelamin = $request->jenis_kelamin;
        $siswa->email = $request->email;
        $siswa->no_hp = $request->no_hp;
        $siswa->alamat = $request->alamat;
        $siswa->id_kelas = $request->id_kelas;
        $siswa->id_jurusan = $request->id_jurusan;
        $siswa->save();

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa baru berhasil didaftarkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelas = Kelas::all();
        $jurusan = Jurusan::all();
        
        return view('admin.siswa.edit', compact('siswa', 'kelas', 'jurusan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $siswa = Siswa::findOrFail($id);

        // Validasi data
        $validator = Validator::make($request->all(), [
            'foto_siswa' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'required|numeric|unique:siswa,nisn,' . $id,
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'email' => 'required|email|unique:siswa,email,' . $id,
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'id_kelas' => 'required|exists:kelas,id',
            'id_jurusan' => 'required|exists:jurusan,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update foto siswa jika ada
        if ($request->hasFile('foto_siswa')) {
            // Hapus foto lama jika ada
            if ($siswa->foto_siswa && Storage::disk('public')->exists($siswa->foto_siswa)) {
                Storage::disk('public')->delete($siswa->foto_siswa);
            }

            $foto = $request->file('foto_siswa');
            $fotoName = time() . '_' . $foto->getClientOriginalName();
            $fotoPath = $foto->storeAs('siswa', $fotoName, 'public');
            $siswa->foto_siswa = $fotoPath;
        }

        // Update data siswa
        $siswa->nama_siswa = $request->nama_siswa;
        $siswa->nisn = $request->nisn;
        $siswa->tanggal_lahir = $request->tanggal_lahir;
        $siswa->jenis_kelamin = $request->jenis_kelamin;
        $siswa->email = $request->email;
        $siswa->no_hp = $request->no_hp;
        $siswa->alamat = $request->alamat;
        $siswa->id_kelas = $request->id_kelas;
        $siswa->id_jurusan = $request->id_jurusan;
        $siswa->save();

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $siswa = Siswa::findOrFail($id);

        // Hapus foto siswa jika ada
        if ($siswa->foto_siswa && Storage::disk('public')->exists($siswa->foto_siswa)) {
            Storage::disk('public')->delete($siswa->foto_siswa);
        }

        $siswa->delete();

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil dihapus!');
    }
}