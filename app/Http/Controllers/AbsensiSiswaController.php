<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Devices;
use App\Models\SettingPresensi;
use Illuminate\Http\Request; // Import Request
use Carbon\Carbon;

class AbsensiSiswaController extends Controller
{
    /**
     * Menampilkan halaman presensi siswa dengan filter dinamis.
     */
    public function index(Request $request) // Tambahkan Request
    {
        // Mulai query builder
        $query = AbsensiSiswa::with(['siswa.jurusan', 'siswa.kelas', 'devices']);

        // 1. Filter berdasarkan Tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('waktu', $request->tanggal);
        } else {
            // Default jika tidak ada filter tanggal: hari ini
            $query->whereDate('waktu', Carbon::today());
        }

        // 2. Filter berdasarkan Jurusan
        // 'whereHas' digunakan untuk filter berdasarkan relasi
        if ($request->filled('jurusan_id') && $request->jurusan_id != 'all') {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_jurusan', $request->jurusan_id);
            });
        }
        
        // 3. Filter berdasarkan Kelas
        if ($request->filled('kelas_id') && $request->kelas_id != 'all') {
             $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_kelas', $request->kelas_id);
            });
        }

        // 4. Filter berdasarkan Device
        if ($request->filled('device_id') && $request->device_id != 'all') {
            $query->where('id_devices', $request->device_id);
        }

        // Eksekusi query setelah semua filter diterapkan
        $absensi = $query->latest('waktu')->get();
            
        // Data untuk mengisi dropdown filter tetap sama
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $kelases = Kelas::orderBy('nama_kelas')->get();
        $devices = Devices::orderBy('nama_device')->get();

        return view('admin.presensi.siswa', compact('absensi', 'jurusans', 'kelases', 'devices'));
    }
}