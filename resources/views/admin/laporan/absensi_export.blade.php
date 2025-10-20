<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Siswa</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .filter-info {
            margin-bottom: 15px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN ABSENSI SISWA</h2>
        <h3>SMK NEGERI 4 BENGKULU SELATAN</h3>
    </div>

    <div class="filter-info">
        <p><strong>Filter yang diterapkan:</strong></p>
        @if ($request->filled('id_kelas'))
            <p>Kelas: {{ $request->kelas_name ?? 'ID ' . $request->id_kelas }}</p>
        @endif
        @if ($request->filled('id_jurusan'))
            <p>Jurusan: {{ $request->jurusan_name ?? 'ID ' . $request->id_jurusan }}</p>
        @endif
        @if ($request->filled('bulan'))
            <p>Bulan: {{ $request->bulan }}</p>
        @endif
        @if ($request->filled('tahun'))
            <p>Tahun: {{ $request->tahun }}</p>
        @endif
        @if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai'))
            <p>Periode: {{ $request->tanggal_mulai }} s/d {{ $request->tanggal_selesai }}</p>
        @endif
        <p>Tanggal cetak: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>Tanggal</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($absensi as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->siswa->nisn ?? '-' }}</td>
                    <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                    <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->siswa->jurusan->nama_jurusan ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $item->waktu_masuk ? \Carbon\Carbon::parse($item->waktu_masuk)->format('H:i:s') : '-' }}
                    </td>
                    <td>{{ $item->waktu_keluar ? \Carbon\Carbon::parse($item->waktu_keluar)->format('H:i:s') : '-' }}
                    </td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data absensi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 12px;">
        <p><strong>Total Data:</strong> {{ count($absensi) }} record</p>
    </div>
</body>

</html>
