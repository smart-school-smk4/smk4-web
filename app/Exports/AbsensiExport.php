<?php
namespace App\Exports;

use App\Models\AbsensiSiswa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;

class AbsensiExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = AbsensiSiswa::with(['siswa.kelas', 'siswa.jurusan']);

        // Filter berdasarkan kelas
        if ($this->request->filled('id_kelas')) {
            $query->whereHas('siswa', function ($q) {
                $q->where('id_kelas', $this->request->id_kelas);
            });
        }

        // Filter berdasarkan jurusan
        if ($this->request->filled('id_jurusan')) {
            $query->whereHas('siswa', function ($q) {
                $q->where('id_jurusan', $this->request->id_jurusan);
            });
        }

        // Filter berdasarkan bulan dan tahun
        if ($this->request->filled('bulan') && $this->request->filled('tahun')) {
            $query->whereYear('tanggal', $this->request->tahun)
                ->whereMonth('tanggal', $this->request->bulan);
        } elseif ($this->request->filled('bulan')) {
            $query->whereMonth('tanggal', $this->request->bulan);
        } elseif ($this->request->filled('tahun')) {
            $query->whereYear('tanggal', $this->request->tahun);
        }

        // Filter berdasarkan range tanggal
        if ($this->request->filled('tanggal_mulai') && $this->request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal', [
                $this->request->tanggal_mulai,
                $this->request->tanggal_selesai,
            ]);
        } elseif ($this->request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $this->request->tanggal_mulai);
        } elseif ($this->request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $this->request->tanggal_selesai);
        }

        // Filter tunggal tanggal (untuk backward compatibility)
        if ($this->request->filled('tanggal')) {
            $query->whereDate('tanggal', $this->request->tanggal);
        }

        // Filter periode (untuk backward compatibility)
        if ($this->request->filled('periode')) {
            $periode = $this->request->periode;
            if ($periode === 'mingguan') {
                $query->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($periode === 'bulanan') {
                $query->whereMonth('tanggal', now()->month);
            }
        }

        $absensi = $query->latest('tanggal')->latest('waktu_masuk')->get();

        return view('admin.laporan.absensi_export', [
            'absensi' => $absensi,
            'request' => $this->request,
        ]);
    }
}
