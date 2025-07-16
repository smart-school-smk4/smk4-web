<?php

namespace App\Http\Controllers;

use App\Models\Devices;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Ruangan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- 1. Tambahkan ini untuk menggunakan query builder

class AdminController extends Controller
{
    public function index(){
        session(['admin_active' => true]);

        // Menghitung data statistik total
        $jumlahSiswa = Siswa::count();
        $jumlahGuru = Guru::count();
        $jumlahKelas = Kelas::count();
        $jumlahJurusan = Jurusan::count();
        $jumlahRuangan = Ruangan::count();
        $jumlahDevice = Devices::count();

        // --- 2. Tambahkan logika untuk mengambil data grafik ---
        // Mengambil data jumlah siswa yang dibuat per tahun
        $siswaPerTahun = Siswa::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        // Memisahkan data untuk label dan nilai grafik
        $chartLabels = $siswaPerTahun->pluck('year');
        $chartData = $siswaPerTahun->pluck('count');
        // --- Akhir bagian data grafik ---


        // 3. Kirim semua data (termasuk data chart) ke view
        return view('admin.dashboard', compact(
            'jumlahSiswa', 'jumlahGuru', 'jumlahKelas', 
            'jumlahJurusan', 'jumlahRuangan', 'jumlahDevice',
            'chartLabels', 'chartData' // <-- Tambahkan variabel chart di sini
        ));
    }
}