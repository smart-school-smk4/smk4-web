<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 18pt;
            color: #1e40af;
            font-weight: bold;
        }

        .header h3 {
            margin: 5px 0;
            font-size: 14pt;
            color: #374151;
            font-weight: normal;
        }

        .filter-info {
            background-color: #f3f4f6;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        .filter-info p {
            margin: 4px 0;
            line-height: 1.5;
        }

        .filter-info strong {
            color: #1f2937;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        thead tr {
            background-color: #2563eb;
            color: white;
        }

        th {
            border: 2px solid #1e40af;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            vertical-align: middle;
        }

        td {
            border: 1px solid #9ca3af;
            padding: 8px;
            font-size: 10pt;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody tr:hover {
            background-color: #e0e7ff;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .status-hadir {
            background-color: #d1fae5;
            color: #065f46;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }

        .status-terlambat {
            background-color: #fef3c7;
            color: #92400e;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }

        .status-sakit {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }

        .status-izin {
            background-color: #e5e7eb;
            color: #374151;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }

        .status-alpha {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }

        .summary {
            margin-top: 25px;
            padding: 15px;
            background-color: #f0f9ff;
            border: 2px solid #3b82f6;
            border-radius: 8px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .summary-item {
            background-color: white;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .summary-item .label {
            font-size: 9pt;
            color: #64748b;
            margin-bottom: 5px;
        }

        .summary-item .value {
            font-size: 16pt;
            font-weight: bold;
            color: #1e40af;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            font-size: 9pt;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>📋 LAPORAN ABSENSI SISWA</h2>
        <h3>SMKN 4 JEMBER</h3>
    </div>

    <div class="filter-info">
        <p><strong>📌 Informasi Laporan:</strong></p>
        @if ($request->filled('id_kelas'))
            @php
                $kelas = \App\Models\Kelas::find($request->id_kelas);
            @endphp
            <p>• Kelas: <strong>{{ $kelas->nama_kelas ?? 'ID ' . $request->id_kelas }}</strong></p>
        @endif
        @if ($request->filled('id_jurusan'))
            @php
                $jurusan = \App\Models\Jurusan::find($request->id_jurusan);
            @endphp
            <p>• Jurusan: <strong>{{ $jurusan->nama_jurusan ?? 'ID ' . $request->id_jurusan }}</strong></p>
        @endif
        @if ($request->filled('bulan') && $request->filled('tahun'))
            @php
                $namaBulan = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
            @endphp
            <p>• Periode: <strong>{{ $namaBulan[$request->bulan] ?? $request->bulan }} {{ $request->tahun }}</strong></p>
        @elseif ($request->filled('bulan'))
            <p>• Bulan: <strong>{{ $request->bulan }}</strong></p>
        @elseif ($request->filled('tahun'))
            <p>• Tahun: <strong>{{ $request->tahun }}</strong></p>
        @endif
        @if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai'))
            <p>• Rentang Tanggal: <strong>{{ \Carbon\Carbon::parse($request->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($request->tanggal_selesai)->format('d/m/Y') }}</strong></p>
        @endif
        <p>• Tanggal Cetak: <strong>{{ date('d/m/Y H:i:s') }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 7%;">No.<br>Absen</th>
                <th style="width: 10%;">NIS</th>
                <th style="width: 18%;">Nama Siswa</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 13%;">Jurusan</th>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 7%;">Waktu<br>Masuk</th>
                <th style="width: 7%;">Waktu<br>Keluar</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 12%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalHadir = 0;
                $totalTerlambat = 0;
                $totalSakit = 0;
                $totalIzin = 0;
                $totalAlpha = 0;
            @endphp
            @forelse ($absensi as $index => $item)
                @php
                    // Hitung statistik
                    switch($item->status) {
                        case 'hadir': $totalHadir++; break;
                        case 'terlambat': $totalTerlambat++; break;
                        case 'sakit': $totalSakit++; break;
                        case 'izin': $totalIzin++; break;
                        case 'alpha': $totalAlpha++; break;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center"><strong>{{ $item->siswa->nomer_absen ?? '-' }}</strong></td>
                    <td class="text-center">{{ $item->siswa->nisn ?? '-' }}</td>
                    <td>{{ $item->siswa->nama_siswa ?? '-' }}</td>
                    <td class="text-center">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->siswa->jurusan->nama_jurusan ?? '-' }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $item->waktu_masuk ? \Carbon\Carbon::parse($item->waktu_masuk)->format('H:i:s') : '-' }}</td>
                    <td class="text-center">{{ $item->waktu_keluar ? \Carbon\Carbon::parse($item->waktu_keluar)->format('H:i:s') : '-' }}</td>
                    <td class="text-center">
                        @if($item->status == 'hadir')
                            <span class="status-hadir">✓ Hadir</span>
                        @elseif($item->status == 'terlambat')
                            <span class="status-terlambat">⏰ Terlambat</span>
                        @elseif($item->status == 'sakit')
                            <span class="status-sakit">🏥 Sakit</span>
                        @elseif($item->status == 'izin')
                            <span class="status-izin">📝 Izin</span>
                        @else
                            <span class="status-alpha">✗ Alpha</span>
                        @endif
                    </td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; background-color: #fef3c7;">
                        <strong>⚠ Tidak ada data absensi untuk filter yang dipilih</strong>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($absensi) > 0)
        <div class="summary">
            <p style="margin: 0 0 10px 0; font-weight: bold; font-size: 11pt; color: #1e40af;">📊 Ringkasan Statistik Absensi</p>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="label">Total Data</div>
                    <div class="value" style="color: #2563eb;">{{ count($absensi) }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">✓ Hadir</div>
                    <div class="value" style="color: #059669;">{{ $totalHadir }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">⏰ Terlambat</div>
                    <div class="value" style="color: #d97706;">{{ $totalTerlambat }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">✗ Alpha</div>
                    <div class="value" style="color: #dc2626;">{{ $totalAlpha }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">🏥 Sakit</div>
                    <div class="value" style="color: #3b82f6;">{{ $totalSakit }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">📝 Izin</div>
                    <div class="value" style="color: #6b7280;">{{ $totalIzin }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">Persentase Hadir</div>
                    <div class="value" style="color: #059669;">{{ count($absensi) > 0 ? round(($totalHadir / count($absensi)) * 100, 1) : 0 }}%</div>
                </div>
                <div class="summary-item">
                    <div class="label">Persentase Terlambat</div>
                    <div class="value" style="color: #d97706;">{{ count($absensi) > 0 ? round(($totalTerlambat / count($absensi)) * 100, 1) : 0 }}%</div>
                </div>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis dari Sistem Informasi Absensi SMKN 4 JEMBER</p>
        <p>© {{ date('Y') }} SMKN 4 JEMBER - Smart School Attendance System</p>
    </div>
</body>

</html>
