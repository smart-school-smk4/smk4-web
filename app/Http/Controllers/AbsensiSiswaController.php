<?php
namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\Devices;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\SettingPresensi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiSiswaController extends Controller
{
    /**
     * Menampilkan halaman presensi siswa dengan filter dinamis.
     */
    public function index(Request $request)
    {
        // Mulai query builder
        $query = AbsensiSiswa::with(['siswa.jurusan', 'siswa.kelas', 'devices']);

        // Filter berdasarkan Tanggal: Selalu menggunakan hari ini
        $query->whereDate('tanggal', Carbon::today());

        // Filter berdasarkan Kelas
        if ($request->filled('kelas_id') && $request->kelas_id != 'all') {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_kelas', $request->kelas_id);
            });
        }

        // Filter berdasarkan Device (Ruangan)
        if ($request->filled('device_id') && $request->device_id != 'all') {
            $query->where('id_devices', $request->device_id);
        }

        // Filter berdasarkan status absensi
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Eksekusi query setelah semua filter diterapkan
        $absensi = $query->latest('waktu_masuk')->get();

        // Data untuk mengisi dropdown filter
        $kelases  = Kelas::orderBy('nama_kelas')->get();
        $devices  = Devices::orderBy('nama_device')->get();

        return view('admin.presensi.siswa', compact('absensi', 'kelases', 'devices'));
    }

    /**
     * API untuk mendapatkan data absensi (digunakan oleh JavaScript)
     */
    public function getAbsensiData(Request $request)
    {
        $query = AbsensiSiswa::with(['siswa.jurusan', 'siswa.kelas', 'devices']);

        // Filter berdasarkan tanggal: Selalu menggunakan hari ini
        $query->whereDate('tanggal', Carbon::today());

        // Filter berdasarkan Kelas
        if ($request->filled('kelas_id') && $request->kelas_id != 'all') {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_kelas', $request->kelas_id);
            });
        }

        // Filter berdasarkan Device (Ruangan)
        if ($request->filled('device_id') && $request->device_id != 'all') {
            $query->where('id_devices', $request->device_id);
        }

        $absensi = $query->latest('waktu_masuk')->get();

        // Format data untuk frontend
        $result = $absensi->map(function ($item, $index) {
            return [
                'no'           => $index + 1,
                'nama_siswa'   => $item->siswa->nama_siswa ?? '-',
                'jurusan'      => $item->siswa->jurusan->nama_jurusan ?? '-',
                'kelas'        => $item->siswa->kelas->nama_kelas ?? '-',
                'tanggal'      => $item->tanggal ? Carbon::parse($item->tanggal)->format('Y-m-d') : Carbon::today()->format('Y-m-d'),
                'waktu_masuk'  => $item->waktu_masuk ? Carbon::parse($item->waktu_masuk)->format('H:i:s') : '-',
                'waktu_keluar' => $item->waktu_keluar ? Carbon::parse($item->waktu_keluar)->format('H:i:s') : '-',
                'ruangan'      => $item->devices->nama_device ?? '-',
                'status'       => $item->status,
                'status_pulang' => $item->waktu_keluar ? 'sudah_pulang' : 'belum_pulang',
                'keterangan'   => $item->keterangan ?? '-',
                'foto_wajah'   => $item->foto_wajah ? asset('storage/' . $item->foto_wajah) : null,
                'foto_wajah_keluar' => $item->foto_wajah_keluar ? asset('storage/' . $item->foto_wajah_keluar) : null,
            ];
        });

        return response()->json([
            'current_date' => Carbon::today()->format('Y-m-d'),
            'data' => $result
        ]);
    }

    /**
     * API untuk menerima absensi dari device
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_siswa'   => 'required|exists:siswa,id',
            'id_devices' => 'required|exists:devices,id',
            'type'       => 'required|in:masuk,keluar', // masuk atau keluar
            'foto_wajah' => 'nullable|string', // Base64 string dari Python
        ]);

        $tanggal   = Carbon::today();
        $siswaId   = $request->id_siswa;
        $devicesId = $request->id_devices;
        $type      = $request->type;

        // Process foto wajah jika ada
        $fotoPath = null;
        if ($request->has('foto_wajah') && !empty($request->foto_wajah)) {
            try {
                // Decode base64 (handle both with and without data:image prefix)
                $imageData = $request->foto_wajah;
                if (strpos($imageData, 'data:image') === 0) {
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                }
                $imageData = base64_decode($imageData);
                
                // Validasi apakah hasil decode valid
                if ($imageData === false) {
                    throw new \Exception('Invalid base64 string');
                }
                
                // Generate unique filename
                $filename = sprintf(
                    'face_%d_%s_%s.jpg',
                    $siswaId,
                    $type,
                    now()->format('Ymd_His')
                );
                
                // Simpan ke storage/app/public/faces
                \Storage::disk('public')->put('faces/' . $filename, $imageData);
                
                $fotoPath = 'faces/' . $filename;
                
                \Log::info("Foto wajah disimpan: {$fotoPath}");
                
            } catch (\Exception $e) {
                \Log::error('Gagal simpan foto wajah: ' . $e->getMessage());
                // Tetap lanjut simpan absensi tanpa foto
            }
        }

        // Cek apakah sudah ada record absensi untuk siswa hari ini
        $absensi = AbsensiSiswa::where('id_siswa', $siswaId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        $now = Carbon::now();

        if (! $absensi) {
            // Buat record baru jika belum ada
            if ($type === 'masuk') {
                $settingPresensi = SettingPresensi::first();
                $status          = 'hadir';

                // Tentukan status berdasarkan waktu masuk: jika lewat dari waktu_masuk_selesai maka "terlambat"
                if ($settingPresensi && !empty($settingPresensi->waktu_masuk_selesai)) {
                    $batasOnTime = Carbon::today()->setTimeFromTimeString($settingPresensi->waktu_masuk_selesai);
                    if ($now->gt($batasOnTime)) {
                        $status = 'terlambat';
                    }
                }

                $absensi = AbsensiSiswa::create([
                    'id_siswa'    => $siswaId,
                    'id_devices'  => $devicesId,
                    'tanggal'     => $tanggal,
                    'waktu_masuk' => $now,
                    'status'      => $status,
                    'foto_wajah'  => $fotoPath,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Absensi masuk berhasil dicatat',
                    'data'    => $absensi,
                    'foto_saved' => $fotoPath !== null,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat absen keluar tanpa absen masuk terlebih dahulu',
                ], 400);
            }
        } else {
            // Update record yang sudah ada
            if ($type === 'masuk' && $absensi->waktu_masuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa sudah melakukan absensi masuk hari ini',
                ], 400);
            }

            if ($type === 'keluar' && $absensi->waktu_keluar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa sudah melakukan absensi keluar hari ini',
                ], 400);
            }

            if ($type === 'masuk') {
                $settingPresensi = SettingPresensi::first();
                $status          = 'hadir';

                if ($settingPresensi && !empty($settingPresensi->waktu_masuk_selesai)) {
                    $batasOnTime = Carbon::today()->setTimeFromTimeString($settingPresensi->waktu_masuk_selesai);
                    if ($now->gt($batasOnTime)) {
                        $status = 'terlambat';
                    }
                }

                $absensi->update([
                    'waktu_masuk' => $now,
                    'status'      => $status,
                    'foto_wajah'  => $fotoPath,
                ]);
            } else {
                $absensi->update([
                    'waktu_keluar' => $now,
                    'foto_wajah_keluar' => $fotoPath,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Absensi {$type} berhasil dicatat",
                'data'    => $absensi,
                'foto_saved' => $fotoPath !== null,
            ]);
        }
    }
}