<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\SettingPresensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbsensiApiController extends Controller
{
    /**
     * API untuk menerima absensi dari Flask AI system
     */
    public function store(Request $request)
    {
        try {
            Log::info('Received attendance data:', $request->all());

            $request->validate([
                'id_siswa'   => 'required|integer|exists:siswa,id',
                'devices_id' => 'required|integer|exists:devices,id',
                'type'       => 'sometimes|string|in:masuk,keluar',
            ]);

            $siswaId   = $request->id_siswa;
            $devicesId = $request->devices_id;
            $type      = $request->type ?? 'masuk'; // Default ke masuk jika tidak ada type
            $tanggal   = Carbon::today();
            $now       = Carbon::now();

            // Cek apakah sudah ada record absensi untuk siswa hari ini
            $absensi = AbsensiSiswa::where('id_siswa', $siswaId)
                ->whereDate('tanggal', $tanggal)
                ->first();

            $settingPresensi = SettingPresensi::first();

            if (! $absensi) {
                // Buat record baru untuk absensi masuk
                if ($type === 'masuk') {
                    $status = $this->determineStatus($now, $settingPresensi);

                    $absensi = AbsensiSiswa::create([
                        'id_siswa'    => $siswaId,
                        'id_devices'  => $devicesId,
                        'tanggal'     => $tanggal,
                        'waktu_masuk' => $now,
                        'status'      => $status,
                    ]);

                    Log::info('New attendance record created:', $absensi->toArray());

                    return response()->json([
                        'success' => true,
                        'message' => 'Absensi masuk berhasil dicatat',
                        'data'    => [
                            'id'          => $absensi->id,
                            'siswa'       => $absensi->siswa->nama,
                            'waktu_masuk' => $absensi->waktu_masuk->format('Y-m-d H:i:s'),
                            'status'      => $absensi->status,
                        ],
                    ]);
                } else {
                    // Tidak bisa absen keluar tanpa absen masuk
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat absen keluar tanpa absen masuk terlebih dahulu',
                    ], 400);
                }
            } else {
                // Update record yang sudah ada
                if ($type === 'masuk') {
                    if ($absensi->waktu_masuk) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Siswa sudah melakukan absensi masuk hari ini',
                            'data'    => [
                                'waktu_masuk_sebelumnya' => $absensi->waktu_masuk->format('Y-m-d H:i:s'),
                            ],
                        ], 400);
                    }

                    $status = $this->determineStatus($now, $settingPresensi);
                    $absensi->update([
                        'waktu_masuk' => $now,
                        'status'      => $status,
                        'id_devices'  => $devicesId, // Update device yang digunakan
                    ]);

                    Log::info('Attendance check-in updated:', $absensi->toArray());

                    return response()->json([
                        'success' => true,
                        'message' => 'Absensi masuk berhasil dicatat',
                        'data'    => [
                            'id'          => $absensi->id,
                            'siswa'       => $absensi->siswa->nama,
                            'waktu_masuk' => $absensi->waktu_masuk->format('Y-m-d H:i:s'),
                            'status'      => $absensi->status,
                        ],
                    ]);
                } else {
                    // Absen keluar
                    if ($absensi->waktu_keluar) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Siswa sudah melakukan absensi keluar hari ini',
                            'data'    => [
                                'waktu_keluar_sebelumnya' => $absensi->waktu_keluar->format('Y-m-d H:i:s'),
                            ],
                        ], 400);
                    }

                    if (! $absensi->waktu_masuk) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Tidak dapat absen keluar tanpa absen masuk terlebih dahulu',
                        ], 400);
                    }

                    $absensi->update([
                        'waktu_keluar' => $now,
                    ]);

                    Log::info('Attendance check-out updated:', $absensi->toArray());

                    return response()->json([
                        'success' => true,
                        'message' => 'Absensi keluar berhasil dicatat',
                        'data'    => [
                            'id'           => $absensi->id,
                            'siswa'        => $absensi->siswa->nama,
                            'waktu_masuk'  => $absensi->waktu_masuk->format('Y-m-d H:i:s'),
                            'waktu_keluar' => $absensi->waktu_keluar->format('Y-m-d H:i:s'),
                            'status'       => $absensi->status,
                        ],
                    ]);
                }
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing attendance:', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menentukan status berdasarkan waktu masuk dan setting presensi
     */
    private function determineStatus(Carbon $waktuMasuk, $settingPresensi = null)
    {
        if (! $settingPresensi) {
            return 'hadir';
        }

        try {
            $jamMasuk       = Carbon::parse($settingPresensi->jam_masuk);
            $toleransi      = $settingPresensi->toleransi_terlambat ?? 15;
            $batasTerlambat = $jamMasuk->addMinutes($toleransi);

            if ($waktuMasuk->gt($batasTerlambat)) {
                return 'terlambat';
            }

            return 'hadir';
        } catch (\Exception $e) {
            Log::warning('Error determining status, defaulting to hadir:', $e->getMessage());
            return 'hadir';
        }
    }

    /**
     * API untuk mendapatkan status absensi siswa hari ini
     */
    public function getStatus(Request $request, $siswaId)
    {
        try {
            $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));

            $absensi = AbsensiSiswa::where('id_siswa', $siswaId)
                ->whereDate('tanggal', $tanggal)
                ->with('siswa')
                ->first();

            if (! $absensi) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'has_attendance' => false,
                        'can_check_in'   => true,
                        'can_check_out'  => false,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'has_attendance' => true,
                    'can_check_in'   => ! $absensi->waktu_masuk,
                    'can_check_out'  => $absensi->waktu_masuk && ! $absensi->waktu_keluar,
                    'waktu_masuk'    => $absensi->waktu_masuk?->format('Y-m-d H:i:s'),
                    'waktu_keluar'   => $absensi->waktu_keluar?->format('Y-m-d H:i:s'),
                    'status'         => $absensi->status,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting attendance status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
