<?php
namespace App\Http\Controllers;

use App\Exports\AbsensiExport;
use App\Models\AbsensiSiswa;
use App\Models\Jurusan;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $kelas   = Kelas::all();
        $jurusan = Jurusan::all();

        $query = AbsensiSiswa::with(['siswa.kelas', 'siswa.jurusan']);

        // Filter berdasarkan kelas
        if ($request->filled('id_kelas')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_kelas', $request->id_kelas);
            });
        }

        // Filter berdasarkan jurusan
        if ($request->filled('id_jurusan')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_jurusan', $request->id_jurusan);
            });
        }

        // Filter berdasarkan bulan dan tahun
        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun)
                ->whereMonth('tanggal', $request->bulan);
        } elseif ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        } elseif ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        // Filter berdasarkan range tanggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal', [
                $request->tanggal_mulai,
                $request->tanggal_selesai,
            ]);
        } elseif ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        } elseif ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        // Filter tunggal tanggal (untuk backward compatibility)
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Filter periode (untuk backward compatibility)
        if ($request->filled('periode')) {
            $periode = $request->periode;
            if ($periode === 'mingguan') {
                $query->whereBetween('waktu', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($periode === 'bulanan') {
                $query->whereMonth('waktu', now()->month);
            }
        }

        // Clone query untuk statistik
        $statistikQuery = clone $query;

        // Hitung statistik
        $totalAbsensi   = $statistikQuery->count();
        $totalHadir     = $statistikQuery->where('status', 'hadir')->count();
        $totalTerlambat = $statistikQuery->where('status', 'terlambat')->count();
        $totalAlpha     = $statistikQuery->where('status', 'alpha')->count();

        $absensi = $query->latest('tanggal')->latest('waktu_masuk')->paginate(20);

        // Append query parameters to pagination links
        $absensi->appends($request->query());

        return view('admin.laporan.absensi', compact(
            'absensi',
            'kelas',
            'jurusan',
            'totalAbsensi',
            'totalHadir',
            'totalTerlambat',
            'totalAlpha'
        ));
    }

    public function export(Request $request)
    {
        $filename = 'laporan_absensi_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new AbsensiExport($request), $filename);
    }
}
