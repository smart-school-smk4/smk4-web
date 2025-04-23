<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function indexSiswa(){
        return view ('admin.presensi.siswa');
    }

    public function indexGuru(){
        return view('admin.presensi.guru');
    }
    public function store(Request $request){
        $validated = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'waktu_absen' => 'required|date_format:Y-m-d H:i:s',
            'status' => 'required|in:hadir,tidak_hadir',
            'device_id' => 'required|exists:devices,id'
        ]);

        $tanggal = date('Y-m-d', strtotime($validated['waktu_absen']));

        $sudahAbsen = AbsensiSiswa::where('id_siswa', $validated['id_siswa'])
            ->whereDate('waktu_absen', $tanggal)
            ->first();

        if($sudahAbsen){
            return response()->json([
                'message' => 'Siswa sudah absen pada tanggal ini'
            ]);
        }

        $absensi = AbsensiSiswa::create([
            'id_siswa' => $validated['id_siswa'],
            'waktu_absen' => $validated['waktu_absen'],
            'status' => $validated['status'],
            'devices_id' => $validated['device_id'],
        ]);

        return response()->json([
            'message' => 'Absensi berhasil dicatat',
            'data' => $absensi
        ], 200);
    }
}