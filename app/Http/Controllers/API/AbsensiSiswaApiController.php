<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbsensiSiswa;

class AbsensiSiswaApiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'devices_id' => 'required|exists:devices,id',
            'waktu' => 'required|date_format:Y-m-d H:i:s',
            'status' => 'required|in:hadir,tidak_hadir',
        ]);

        $tanggal = date('Y-m-d', strtotime($validated['waktu']));

        $sudahAbsen = AbsensiSiswa::where('id_siswa', $validated['id_siswa'])
            ->whereDate('waktu', $tanggal)
            ->first();

        if ($sudahAbsen) {
            return response()->json([
                'message' => 'Siswa sudah absen pada tanggal ini'
            ], 409);
        }

        $absensi = AbsensiSiswa::create([
            'id_siswa' => $validated['id_siswa'],
            'waktu' => $validated['waktu'],
            'status' => $validated['status'],
            'id_devices' => $validated['devices_id'],
        ]);

        return response()->json([
            'message' => 'Absensi berhasil dicatat',
            'data' => $absensi
        ], 201);
    }
}
