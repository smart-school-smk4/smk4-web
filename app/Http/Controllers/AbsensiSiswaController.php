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

        // 1. Filter berdasarkan Tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        } else {
            // Default jika tidak ada filter tanggal: hari ini
            $query->whereDate('tanggal', Carbon::today());
        }

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

        // 5. Filter berdasarkan status absensi
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Eksekusi query setelah semua filter diterapkan
        $absensi = $query->latest('waktu_masuk')->get();

        // Data untuk mengisi dropdown filter
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $kelases  = Kelas::orderBy('nama_kelas')->get();
        $devices  = Devices::orderBy('nama_device')->get();

        return view('admin.presensi.siswa', compact('absensi', 'jurusans', 'kelases', 'devices'));
    }

    /**
     * API untuk mendapatkan data absensi (digunakan oleh JavaScript)
     */
    public function getAbsensiData(Request $request)
    {
        $query = AbsensiSiswa::with(['siswa.jurusan', 'siswa.kelas', 'devices']);

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        } else {
            $query->whereDate('tanggal', Carbon::today());
        }

        // Filter lainnya
        if ($request->filled('jurusan_id') && $request->jurusan_id != 'all') {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_jurusan', $request->jurusan_id);
            });
        }

        if ($request->filled('kelas_id') && $request->kelas_id != 'all') {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_kelas', $request->kelas_id);
            });
        }

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
                'waktu_masuk'  => $item->waktu_masuk ? Carbon::parse($item->waktu_masuk)->format('H:i:s') : '-',
                'waktu_keluar' => $item->waktu_keluar ? Carbon::parse($item->waktu_keluar)->format('H:i:s') : '-',
                'ruangan'      => $item->devices->nama_device ?? '-',
                'status'       => $item->status,
                'keterangan'   => $item->keterangan ?? '-',
            ];
        });

        return response()->json($result);
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
        ]);

        $tanggal   = Carbon::today();
        $siswaId   = $request->id_siswa;
        $devicesId = $request->id_devices;
        $type      = $request->type;

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
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Absensi masuk berhasil dicatat',
                    'data'    => $absensi,
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
                ]);
            } else {
                $absensi->update([
                    'waktu_keluar' => $now,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Absensi {$type} berhasil dicatat",
                'data'    => $absensi,
            ]);
        }
    }
}