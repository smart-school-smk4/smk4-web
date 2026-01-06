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
            // Log raw request untuk debugging
            Log::info('=== ABSENSI REQUEST START ===');
            Log::info('Request Method: ' . $request->method());
            Log::info('Request URL: ' . $request->fullUrl());
            Log::info('Request Headers: ', $request->headers->all());
            Log::info('Request Body: ', $request->all());
            Log::info('Raw Input: ' . $request->getContent());

            // Support kedua format: id_devices (dari Python) dan devices_id (legacy)
            $request->validate([
                'id_siswa'   => 'required|integer|exists:siswa,id',
                'id_devices' => 'required_without:devices_id|integer|exists:devices,id',
                'devices_id' => 'required_without:id_devices|integer|exists:devices,id',
                'type'       => 'sometimes|string|in:masuk,keluar',
            ]);

            $siswaId   = $request->id_siswa;
            // Support kedua format field device ID
            $devicesId = $request->id_devices ?? $request->devices_id;
            $type      = $request->type ?? 'masuk'; // Default ke masuk jika tidak ada type
            
            Log::info('Validated Data:', [
                'siswa_id' => $siswaId,
                'devices_id' => $devicesId,
                'type' => $type
            ]);
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

                    $response = response()->json([
                        'success' => true,
                        'message' => 'Absensi masuk berhasil dicatat',
                        'data'    => [
                            'id'          => $absensi->id,
                            'siswa'       => $absensi->siswa->nama_siswa,
                            'waktu_masuk' => $absensi->waktu_masuk->format('Y-m-d H:i:s'),
                            'status'      => $absensi->status,
                        ],
                    ]);
                    
                    Log::info('=== ABSENSI SUCCESS ===');
                    Log::info('Response: ', $response->getData(true));
                    
                    return $response;
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
                            'siswa'       => $absensi->siswa->nama_siswa,
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
                            'siswa'        => $absensi->siswa->nama_siswa,
                            'waktu_masuk'  => $absensi->waktu_masuk->format('Y-m-d H:i:s'),
                            'waktu_keluar' => $absensi->waktu_keluar->format('Y-m-d H:i:s'),
                            'status'       => $absensi->status,
                        ],
                    ]);
                }
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('=== VALIDATION ERROR ===');
            Log::error('Validation errors:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('=== SERVER ERROR ===');
            Log::error('Error storing attendance:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
            // Gunakan cutoff waktu_masuk_selesai sebagai batas hadir on-time
            if (!empty($settingPresensi->waktu_masuk_selesai)) {
                $batasOnTime = Carbon::today()->setTimeFromTimeString($settingPresensi->waktu_masuk_selesai);
                return $waktuMasuk->gt($batasOnTime) ? 'terlambat' : 'hadir';
            }
            // Fallback aman bila field kosong
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
