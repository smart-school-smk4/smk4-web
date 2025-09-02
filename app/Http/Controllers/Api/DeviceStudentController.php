<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiswaResource;
use App\Models\Devices;
use App\Models\Siswa;

class DeviceStudentController extends Controller
{
    public function index(Devices $device)
    {
        // Eager load relasi 'kelas' untuk memastikan data tersedia.
        // Ini akan mencegah error jika relasi belum didefinisikan di model.
        // Jika Anda melihat error di sini, berarti relasi di model Devices salah.
        $device->loadMissing('kelas');

        // Jika device tidak terhubung dengan kelas, kembalikan pesan error yang jelas.
        if (! $device->kelas) {
            return response()->json([
                'success' => false,
                'message' => 'Device ini tidak terhubung dengan kelas manapun.',
            ], 404);
        }

        // Ambil semua siswa dari kelas yang sama dengan device tersebut
        // dan pastikan kita juga memuat relasi 'fotos' untuk setiap siswa.
        $students = Siswa::where('id_kelas', $device->id_kelas)
            ->with('fotos') // Eager load relasi 'fotos' dari model Siswa
            ->get();

        // Kembalikan data siswa menggunakan SiswaResource untuk format yang rapi.
        return SiswaResource::collection($students)
            ->additional([
                'success' => true,
                'message' => 'Berhasil mengambil data siswa untuk device ' . $device->nama_device,
                'device'  => [
                    'id'    => $device->id,
                    'nama'  => $device->nama_device,
                    'kelas' => $device->kelas->nama_kelas,
                ],
            ]);
    }
}