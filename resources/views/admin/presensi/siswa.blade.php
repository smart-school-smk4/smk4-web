@extends('layouts.dashboard')

@section('title', 'Smart School | Presensi Siswa')

@section('content')
<div class="bg-gray-50 min-h-screen p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">

        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">Halaman Presensi Siswa</h1>
            <span class="text-sm font-medium text-gray-500 mt-2 sm:mt-0">Real-time Face Recognition</span>
        </div>

        <!-- Grid Layout Utama -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Kolom Kiri: Filter & Tabel Presensi -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Card untuk Filter -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Filter Data</h2>
                    
                    {{-- Bungkus filter dalam form GET agar bisa dibaca controller --}}
                    <form id="filterForm" action="{{ url()->current() }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Filter Jurusan -->
                            <div>
                                <label for="jurusan" class="block text-sm font-medium text-gray-600 mb-1">Pilih Jurusan</label>
                                {{-- Tambahkan atribut 'name' agar bisa dibaca oleh Request --}}
                                <select id="jurusan" name="jurusan_id" class="filter-input w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="all">Semua Jurusan</option>
                                    @foreach($jurusans as $jurusan)
                                        {{-- Jaga agar nilai filter tetap terpilih setelah halaman di-refresh --}}
                                        <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                            {{ $jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Filter Kelas -->
                            <div>
                                <label for="kelas" class="block text-sm font-medium text-gray-600 mb-1">Pilih Kelas</label>
                                <select id="kelas" name="kelas_id" class="filter-input w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="all">Semua Kelas</option>
                                    @foreach($kelases as $kelas)
                                        <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Filter Ruangan/Device -->
                            <div>
                                <label for="device" class="block text-sm font-medium text-gray-600 mb-1">Pilih Device</label>
                                <select id="device" name="device_id" class="filter-input w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="all">Semua Device</option>
                                    @foreach($devices as $device)
                                        <option value="{{ $device->id }}" {{ request('device_id') == $device->id ? 'selected' : '' }}>
                                            {{ $device->nama_device }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Filter Tanggal -->
                            <div>
                                <label for="tanggal" class="block text-sm font-medium text-gray-600 mb-1">Pilih Tanggal</label>
                                <input type="date" id="tanggal" name="tanggal" value="{{ request('tanggal', date('Y-m-d')) }}" class="filter-input w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Card untuk Tabel Presensi -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                     <h2 id="laporan-title" class="text-xl font-semibold text-gray-700 mb-4">Laporan Kehadiran</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 rounded-l-lg">No</th>
                                    <th scope="col" class="px-6 py-3">Nama Siswa</th>
                                    <th scope="col" class="px-6 py-3">Jurusan</th>
                                    <th scope="col" class="px-6 py-3">Kelas</th>
                                    <th scope="col" class="px-6 py-3">Waktu Presensi</th>
                                    <th scope="col" class="px-6 py-3">Ruangan</th>
                                    <th scope="col" class="px-6 py-3 rounded-r-lg">Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-table-body">
                                @forelse ($absensi as $key => $item)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $key + 1 }}</td>
                                    <td class="px-6 py-4">{{ $item->siswa->nama_siswa ?? 'Siswa Dihapus' }}</td>
                                    <td class="px-6 py-4">{{ $item->siswa->jurusan->nama_jurusan ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $item->siswa->kelas->nama_kelas ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($item->waktu)->format('H:i:s') }}</td>
                                    <td class="px-6 py-4">{{ $item->devices->nama_device ?? 'Device Dihapus' }}</td>
                                    <td class="px-6 py-4">
                                        @if(strtolower($item->status) == 'hadir')
                                            <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Hadir</span>
                                        @elseif(strtolower($item->status) == 'terlambat')
                                             <span class="px-2 py-1 font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full">Terlambat</span>
                                        @else
                                            <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">{{ ucfirst($item->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr class="bg-white border-b">
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data yang cocok dengan filter yang dipilih.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Live Camera -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 sticky top-6">
                    <h2 id="camera-title" class="text-xl font-semibold text-gray-700 mb-4 text-center">Live Camera Feed</h2>
                    <div id="cameraContainer" class="w-full aspect-video rounded-lg bg-gray-900 flex items-center justify-center overflow-hidden">
                        <img id="cameraFeed" src="" class="hidden w-full h-full object-cover">
                        <div id="cameraStatus" class="text-center text-gray-400 p-4">
                             <p class="flex items-center justify-center h-full">Pilih device untuk melihat live feed</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const filterInputs = document.querySelectorAll('.filter-input');
        const filterForm = document.getElementById('filterForm');

        // Otomatis submit form ketika nilai filter diubah
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
        });

        // Bagian untuk judul dan kamera
        const laporanTitle = document.getElementById("laporan-title");
        const tanggalInput = document.getElementById("tanggal");
        const cameraFeed = document.getElementById("cameraFeed");
        const cameraStatus = document.getElementById("cameraStatus");
        const cameraTitle = document.getElementById("camera-title");
        const deviceSelect = document.getElementById("device");
        const devicesData = @json($devices);

        function formatIndonesianDate(dateString) {
            if (!dateString) return 'Hari Ini';
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const parts = dateString.match(/(\d{4})-(\d{2})-(\d{2})/);
            if (!parts) return 'Hari Ini';
            const date = new Date(parts[1], parts[2] - 1, parts[3]);
            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()} (${days[date.getDay()]})`;
        }

        function updateDynamicElements() {
             laporanTitle.innerText = `Laporan Kehadiran ${formatIndonesianDate(tanggalInput.value)}`;
        }

        // Jalankan saat halaman pertama kali dimuat
        updateDynamicElements();

        // Sisa JavaScript untuk kamera...
        // ... (Kode untuk kamera sama seperti sebelumnya) ...
    });
</script>
@endsection
