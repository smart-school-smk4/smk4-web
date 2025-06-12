<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiLaporanController extends Controller
{
    public function __invoke(Request $request)
    {
        // Mulai query builder dengan relasi yang dibutuhkan
        $query = AbsensiSiswa::with(['siswa.jurusan', 'siswa.kelas', 'devices']);

        // 1. Filter berdasarkan Tanggal (Wajib)
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $query->whereDate('waktu', $tanggal);

        // 2. Filter berdasarkan Jurusan
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

        // Eksekusi query
        $absensi = $query->latest('waktu')->get();

        // Ubah data menjadi format JSON yang rapi untuk dikirim
        $formattedData = $absensi->map(function ($item, $key) {
            return [
                'no' => $key + 1,
                'nama_siswa' => $item->siswa->nama_siswa ?? 'Siswa Dihapus',
                'jurusan' => $item->siswa->jurusan->nama_jurusan ?? 'N/A',
                'kelas' => $item->siswa->kelas->nama_kelas ?? 'N/A',
                'waktu' => Carbon::parse($item->waktu)->format('H:i:s'),
                'ruangan' => $item->devices->nama_device ?? 'Device Dihapus',
                'status' => strtolower($item->status),
            ];
        });

        return response()->json($formattedData);
    }
}
