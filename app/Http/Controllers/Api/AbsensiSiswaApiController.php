<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbsensiSiswa;
use App\Models\SettingPresensi;
use Illuminate\Support\Carbon;

class AbsensiSiswaApiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'devices_id' => 'required|exists:devices,id',
        ]);

        $now = Carbon::now();
        $tanggal = $now->toDateString();
        $jamSekarang = $now->format('H:i');

        $setting = SettingPresensi::first();
        if (!$setting) {
            return response()->json(['message' => 'Pengaturan presensi belum diatur.'], 500);
        }

        // Tentukan status berdasarkan waktu
        if ($jamSekarang >= $setting->waktu_masuk_mulai && $jamSekarang <= $setting->waktu_masuk_selesai) {
            $status = 'Masuk';
        } elseif ($jamSekarang >= $setting->waktu_pulang_mulai && $jamSekarang <= $setting->waktu_pulang_selesai) {
            $status = 'Pulang';
        } else {
            return response()->json(['message' => 'Diluar waktu presensi yang diizinkan.'], 403);
        }

        // Cek apakah siswa sudah presensi Masuk/Pulang hari ini
        $sudahAbsen = AbsensiSiswa::where('id_siswa', $validated['id_siswa'])
            ->whereDate('waktu', $tanggal)
            ->where('status', $status)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'message' => "Siswa sudah melakukan presensi $status hari ini"
            ], 409);
        }

        // Simpan presensi
        $absensi = AbsensiSiswa::create([
            'id_siswa' => $validated['id_siswa'],
            'id_devices' => $validated['devices_id'],
            'waktu' => $now,
            'status' => $status,
        ]);

        return response()->json([
            'message' => "Presensi $status berhasil dicatat",
            'data' => $absensi
        ], 201);
    }
}