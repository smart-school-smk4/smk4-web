@extends('layouts.dashboard')

@section('title', 'Smart School | Laporan Absensi')
@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                                <i class="fas fa-clipboard-list text-blue-500 mr-3"></i>
                                Laporan Absensi Siswa
                            </h1>
                            <p class="text-gray-600">Kelola dan pantau data absensi siswa secara real-time</p>
                        </div>
                        <div class="text-right">
                            <a href="{{ route('admin.laporan') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-filter text-purple-500 mr-2"></i>
                        Filter Data Absensi
                    </h2>
                    <p class="text-gray-600 text-sm">Gunakan filter di bawah untuk menyaring data sesuai kebutuhan</p>
                </div>

                <form method="GET" action="{{ route('admin.laporan.absensi') }}" class="space-y-6">
                    <!-- Filter Row 1 -->
                    <div class="grid md:grid-cols-4 gap-4">
                        <div class="space-y-2">
                            <label for="id_kelas" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-users mr-1 text-blue-500"></i>
                                Filter Kelas
                            </label>
                            <select name="id_kelas" id="id_kelas"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="">-- Semua Kelas --</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}"
                                        {{ request('id_kelas') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="id_jurusan" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-graduation-cap mr-1 text-emerald-500"></i>
                                Filter Jurusan
                            </label>
                            <select name="id_jurusan" id="id_jurusan"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                                <option value="">-- Semua Jurusan --</option>
                                @foreach ($jurusan as $j)
                                    <option value="{{ $j->id }}"
                                        {{ request('id_jurusan') == $j->id ? 'selected' : '' }}>
                                        {{ $j->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="bulan" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-calendar mr-1 text-orange-500"></i>
                                Filter Bulan
                            </label>
                            <select name="bulan" id="bulan"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                                <option value="">-- Semua Bulan --</option>
                                <option value="01" {{ request('bulan') == '01' ? 'selected' : '' }}>Januari</option>
                                <option value="02" {{ request('bulan') == '02' ? 'selected' : '' }}>Februari</option>
                                <option value="03" {{ request('bulan') == '03' ? 'selected' : '' }}>Maret</option>
                                <option value="04" {{ request('bulan') == '04' ? 'selected' : '' }}>April</option>
                                <option value="05" {{ request('bulan') == '05' ? 'selected' : '' }}>Mei</option>
                                <option value="06" {{ request('bulan') == '06' ? 'selected' : '' }}>Juni</option>
                                <option value="07" {{ request('bulan') == '07' ? 'selected' : '' }}>Juli</option>
                                <option value="08" {{ request('bulan') == '08' ? 'selected' : '' }}>Agustus</option>
                                <option value="09" {{ request('bulan') == '09' ? 'selected' : '' }}>September</option>
                                <option value="10" {{ request('bulan') == '10' ? 'selected' : '' }}>Oktober</option>
                                <option value="11" {{ request('bulan') == '11' ? 'selected' : '' }}>November</option>
                                <option value="12" {{ request('bulan') == '12' ? 'selected' : '' }}>Desember</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="tahun" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-calendar-alt mr-1 text-purple-500"></i>
                                Filter Tahun
                            </label>
                            <select name="tahun" id="tahun"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                <option value="">-- Semua Tahun --</option>
                                @for ($year = date('Y'); $year >= 2020; $year--)
                                    <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Filter Row 2 -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-calendar-day mr-1 text-cyan-500"></i>
                                Tanggal Mulai
                            </label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                value="{{ request('tanggal_mulai') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
                        </div>

                        <div class="space-y-2">
                            <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-calendar-check mr-1 text-pink-500"></i>
                                Tanggal Selesai
                            </label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                value="{{ request('tanggal_selesai') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                        <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-search mr-2"></i>
                            Filter Data
                        </button>
                        <a href="{{ route('admin.laporan.absensi') }}"
                            class="inline-flex items-center px-6 py-3 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-refresh mr-2"></i>
                            Reset Filter
                        </a>
                        <a href="{{ route('admin.laporan.export') }}?{{ http_build_query(request()->all()) }}"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-medium rounded-lg hover:from-emerald-600 hover:to-emerald-700 transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-file-excel mr-2"></i>
                            Export Excel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div
                    class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">Total Absensi</p>
                            <p class="text-3xl font-bold">{{ number_format($totalAbsensi) }}</p>
                        </div>
                        <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                            <i class="fas fa-clipboard-check text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm">Total Hadir</p>
                            <p class="text-3xl font-bold">{{ number_format($totalHadir) }}</p>
                        </div>
                        <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm">Total Terlambat</p>
                            <p class="text-3xl font-bold">{{ number_format($totalTerlambat) }}</p>
                        </div>
                        <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-100 text-sm">Total Alpha</p>
                            <p class="text-3xl font-bold">{{ number_format($totalAlpha) }}</p>
                        </div>
                        <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                            <i class="fas fa-times-circle text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-table mr-2 text-indigo-500"></i>
                        Data Absensi Siswa
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">No</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">NIS</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Nama Siswa</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Kelas</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Jurusan</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Tanggal</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Waktu Masuk</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Waktu Keluar</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Status Pulang</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($absensi as $index => $data)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $absensi->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $data->siswa->nisn ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $data->siswa->nama_siswa ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $data->siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $data->siswa->jurusan->nama_jurusan ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($data->waktu)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $data->waktu_masuk ? \Carbon\Carbon::parse($data->waktu_masuk)->format('H:i:s') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $data->waktu_keluar ? \Carbon\Carbon::parse($data->waktu_keluar)->format('H:i:s') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($data->status == 'hadir')
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Hadir
                                            </span>
                                        @elseif($data->status == 'terlambat')
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-clock mr-1"></i>
                                                Terlambat
                                            </span>
                                        @elseif($data->status == 'sakit')
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-heartbeat mr-1"></i>
                                                Sakit
                                            </span>
                                        @elseif($data->status == 'izin')
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-hand-paper mr-1"></i>
                                                Izin
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Alpha
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($data->waktu_keluar)
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-check-double mr-1"></i>
                                                Sudah Pulang
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-hourglass-half mr-1"></i>
                                                Belum Pulang
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $data->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data absensi</h3>
                                            <p class="text-gray-500">Tidak ada data absensi yang ditemukan dengan filter
                                                yang dipilih</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($absensi->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Menampilkan {{ $absensi->firstItem() ?? 0 }} sampai {{ $absensi->lastItem() ?? 0 }}
                                dari {{ number_format($absensi->total()) }} data
                            </div>
                            <div class="flex space-x-1">
                                {{ $absensi->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto submit form when filter changes (optional)
            $('#id_kelas, #id_jurusan, #bulan, #tahun').change(function() {
                // Uncomment the line below if you want auto-submit on filter change
                // $(this).closest('form').submit();
            });

            // Add loading animation to filter button
            $('form').on('submit', function() {
                $(this).find('button[type="submit"]').html(
                    '<i class="fas fa-spinner fa-spin mr-2"></i>Memuat...');
            });
        });
    </script>
@endpush
