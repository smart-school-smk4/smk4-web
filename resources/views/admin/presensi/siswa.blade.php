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
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Filter Jurusan -->
                        <div>
                            <label for="jurusan" class="block text-sm font-medium text-gray-600 mb-1">Pilih Jurusan</label>
                            <select id="jurusan" class="filter-input w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="all">Semua Jurusan</option>
                                @foreach($jurusans as $jurusan)
                                    <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Filter Kelas -->
                        <div>
                            <label for="kelas" class="block text-sm font-medium text-gray-600 mb-1">Pilih Kelas</label>
                            <select id="kelas" class="filter-input w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="all">Semua Kelas</option>
                                @foreach($kelases as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Filter Ruangan/Device -->
                        <div>
                            <label for="device" class="block text-sm font-medium text-gray-600 mb-1">Pilih Device</label>
                            <select id="device" class="filter-input w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="all" data-ip="">Semua Device</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}" data-ip="{{ $device->ip_address }}">{{ $device->nama_device }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Filter Tanggal -->
                        <div>
                            <label for="tanggal" class="block text-sm font-medium text-gray-600 mb-1">Pilih Tanggal</label>
                            <input type="date" id="tanggal" value="{{ date('Y-m-d') }}" class="filter-input w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>
                    </div>
                </div>

                <!-- Card untuk Tabel Presensi -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
                     <h2 id="laporan-title" class="text-xl font-semibold text-gray-700 mb-4">Laporan Kehadiran</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 rounded-l-lg">No</th>
                                    <th class="px-6 py-3">Nama Siswa</th>
                                    <th class="px-6 py-3">Jurusan</th>
                                    <th class="px-6 py-3">Kelas</th>
                                    <th class="px-6 py-3">Waktu Presensi</th>
                                    <th class="px-6 py-3">Ruangan</th>
                                    <th class="px-6 py-3 rounded-r-lg">Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-table-body">
                                <tr><td colspan="7" class="p-4 text-center text-gray-500">Memuat data...</td></tr>
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
    const tableBody = document.getElementById('attendance-table-body');
    const laporanTitle = document.getElementById('laporan-title');
    const deviceSelect = document.getElementById("device");
    const cameraFeed = document.getElementById("cameraFeed");
    const cameraStatus = document.getElementById("cameraStatus");
    const cameraTitle = document.getElementById("camera-title");

    let fetchInterval; 

    function updateCameraFeed() {
        const selectedOption = deviceSelect.options[deviceSelect.selectedIndex];
        const deviceIp = selectedOption.dataset.ip;
        const deviceName = selectedOption.text;
        
        console.log("Mencoba menghubungkan ke IP:", deviceIp);

        cameraFeed.classList.add('hidden');
        cameraFeed.src = '';

        if (!deviceIp || deviceSelect.value === 'all') {
            cameraTitle.innerText = "Live Camera Feed";
            cameraStatus.innerHTML = '<p class="flex items-center justify-center h-full">Pilih device untuk melihat live feed</p>';
            cameraStatus.style.display = 'block';
            return;
        }

        cameraTitle.innerText = `Live: ${deviceName}`;
        cameraStatus.innerHTML = `<div class="flex flex-col items-center justify-center h-full"><svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><p class="mt-2">Menghubungkan ke ${deviceIp}...</p></div>`;
        cameraStatus.style.display = 'block';

        cameraFeed.src = `http://${deviceIp}:5000/video_feed`;
    }

    cameraFeed.onload = function() {
        cameraFeed.classList.remove("hidden");
        cameraStatus.style.display = "none";
    };

    cameraFeed.onerror = function() {
        cameraFeed.classList.add("hidden");
        cameraStatus.style.display = 'block';
        cameraStatus.innerHTML = '<p class="flex items-center justify-center h-full text-red-500 font-semibold">Kamera tidak aktif atau gagal terhubung.</p>';
    };
    
    async function fetchAttendanceData() {
        const jurusanId = document.getElementById('jurusan').value;
        const kelasId = document.getElementById('kelas').value;
        const deviceId = deviceSelect.value;
        const tanggal = document.getElementById('tanggal').value;
        
        laporanTitle.innerText = `Laporan Kehadiran ${formatIndonesianDate(tanggal)}`;
        
        // === PERBAIKAN DI SINI ===
        // Menambahkan parameter acak (_=timestamp) untuk mencegah browser caching
        const cacheBuster = `&_=${new Date().getTime()}`;
        const url = `/api/laporan-absensi?tanggal=${tanggal}&jurusan_id=${jurusanId}&kelas_id=${kelasId}&device_id=${deviceId}${cacheBuster}`;

        try {
            const response = await fetch(url, { cache: 'no-store' }); // Menambahkan opsi untuk mencegah cache
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            renderTable(data);
        } catch (error) {
            console.error('Error fetching data:', error);
            tableBody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-red-500">Gagal memuat data.</td></tr>`;
        }
    }

    function renderTable(data) {
        tableBody.innerHTML = '';
        if (data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-gray-500">Tidak ada data absensi untuk filter ini.</td></tr>`;
            return;
        }
        data.forEach(item => {
            let statusBadge = '';
            switch (item.status) {
                case 'hadir': statusBadge = `<span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Hadir</span>`; break;
                case 'terlambat': statusBadge = `<span class="px-2 py-1 font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full">Terlambat</span>`; break;
                default: statusBadge = `<span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">${item.status}</span>`;
            }
            const row = `
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">${item.no}</td>
                    <td class="px-6 py-4">${item.nama_siswa}</td>
                    <td class="px-6 py-4">${item.jurusan}</td>
                    <td class="px-6 py-4">${item.kelas}</td>
                    <td class="px-6 py-4">${item.waktu}</td>
                    <td class="px-6 py-4">${item.ruangan}</td>
                    <td class="px-6 py-4">${statusBadge}</td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    function formatIndonesianDate(dateString) {
        if (!dateString) return '';
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const date = new Date(dateString + 'T00:00:00');
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()} (${days[date.getDay()]})`;
    }

    function startAutoRefresh() {
        clearInterval(fetchInterval);
        tableBody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-gray-500">Memuat data...</td></tr>`;
        fetchAttendanceData();
        // Mengubah interval menjadi lebih cepat untuk pengujian, misal 5 detik
        fetchInterval = setInterval(fetchAttendanceData, 5000); // 5000 ms = 5 detik
    }

    filterInputs.forEach(input => {
        input.addEventListener('change', startAutoRefresh);
    });

    deviceSelect.addEventListener('change', function() {
        updateCameraFeed(); 
    });

    startAutoRefresh();
});
</script>
@endsection