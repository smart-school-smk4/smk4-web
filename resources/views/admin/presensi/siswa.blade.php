@extends('layouts.dashboard')

@section('title', 'Smart School | Presensi Siswa')

@section('content')
    <div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen p-4 sm:p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">

            <!-- Enhanced Header Section -->
            <div class="mb-8 text-center">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                    <h1
                        class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                        Presensi Siswa Real-time
                    </h1>
                    <p class="text-gray-600 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Face Recognition Technology Active
                    </p>
                </div>
            </div>

            <!-- Grid Layout Utama -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Kolom Kiri: Filter & Tabel Presensi -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Enhanced Filter Card -->
                    <div
                        class="bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-xl border border-white/50 hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z">
                                    </path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800">Filter Data</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Filter Jurusan -->
                            <div class="group">
                                <label for="jurusan"
                                    class="block text-sm font-semibold text-gray-700 mb-2 group-hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    Jurusan
                                </label>
                                <select id="jurusan"
                                    class="filter-input w-full p-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300">
                                    <option value="all">Semua Jurusan</option>
                                    @foreach ($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Filter Kelas -->
                            <div class="group">
                                <label for="kelas"
                                    class="block text-sm font-semibold text-gray-700 mb-2 group-hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                        </path>
                                    </svg>
                                    Kelas
                                </label>
                                <select id="kelas"
                                    class="filter-input w-full p-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300">
                                    <option value="all">Semua Kelas</option>
                                    @foreach ($kelases as $kelas)
                                        <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Filter Device -->
                            <div class="group">
                                <label for="device"
                                    class="block text-sm font-semibold text-gray-700 mb-2 group-hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z">
                                        </path>
                                    </svg>
                                    Device
                                </label>
                                <select id="device"
                                    class="filter-input w-full p-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300">
                                    <option value="all" data-ip="">Semua Device</option>
                                    @foreach ($devices as $device)
                                        <option value="{{ $device->id }}" data-ip="{{ $device->ip_address }}">
                                            {{ $device->nama_device }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Filter Tanggal -->
                            <div class="group">
                                <label for="tanggal"
                                    class="block text-sm font-semibold text-gray-700 mb-2 group-hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    Tanggal
                                </label>
                                <input type="date" id="tanggal" value="{{ date('Y-m-d') }}"
                                    class="filter-input w-full p-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white hover:border-gray-300">
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Table Card -->
                    <div
                        class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 overflow-hidden hover:shadow-2xl transition-all duration-300">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 p-6">
                            <h2 id="laporan-title" class="text-2xl font-bold text-white flex items-center gap-3">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Laporan Kehadiran
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead
                                        class="text-xs font-bold text-gray-700 uppercase bg-gradient-to-r from-gray-50 to-gray-100">
                                        <tr>
                                            <th class="px-6 py-4 rounded-l-lg">No</th>
                                            <th class="px-6 py-4">Nama Siswa</th>
                                            <th class="px-6 py-4">Jurusan</th>
                                            <th class="px-6 py-4">Kelas</th>
                                            <th class="px-6 py-4">Waktu Masuk</th>
                                            <th class="px-6 py-4">Waktu Keluar</th>
                                            <th class="px-6 py-4">Ruangan</th>
                                            <th class="px-6 py-4">Status</th>
                                            <th class="px-6 py-4 rounded-r-lg">Status Pulang</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendance-table-body">
                                        <tr class="animate-pulse">
                                            <td colspan="9" class="p-8 text-center">
                                                <div class="flex items-center justify-center gap-3 text-gray-500">
                                                    <div
                                                        class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin">
                                                    </div>
                                                    <span class="text-lg">Memuat data presensi...</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Camera Section -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 sticky top-6 overflow-hidden hover:shadow-2xl transition-all duration-300">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 p-6">
                            <h2 id="camera-title"
                                class="text-xl font-bold text-white text-center flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Live Camera Feed
                            </h2>
                        </div>
                        <div class="p-6">
                            <div id="cameraContainer"
                                class="w-full aspect-video rounded-xl bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center overflow-hidden border-4 border-gray-200 shadow-inner">
                                <img id="cameraFeed" src="" class="hidden w-full h-full object-cover rounded-lg">
                                <div id="cameraStatus" class="text-center text-gray-300 p-6">
                                    <div class="flex flex-col items-center justify-center h-full space-y-3">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <p class="text-lg font-medium">Pilih device untuk melihat live feed</p>
                                    </div>
                                </div>
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
                    cameraTitle.innerHTML = `
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Live Camera Feed
                    `;
                    cameraStatus.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full space-y-3">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-lg font-medium">Pilih device untuk melihat live feed</p>
                        </div>
                    `;
                    cameraStatus.style.display = 'block';
                    return;
                }

                cameraTitle.innerHTML = `
                    <div class="flex items-center justify-center gap-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Live: ${deviceName}
                    </div>
                `;
                cameraStatus.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full space-y-4">
                        <div class="relative">
                            <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-6 h-6 bg-blue-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-semibold text-blue-400">Menghubungkan...</p>
                            <p class="text-sm text-gray-400">${deviceIp}</p>
                        </div>
                    </div>
                `;
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
                cameraStatus.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full space-y-3">
                        <svg class="w-12 h-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-center">
                            <p class="text-lg font-semibold text-red-400">Kamera Tidak Aktif</p>
                            <p class="text-sm text-gray-400">Gagal terhubung ke device</p>
                        </div>
                    </div>
                `;
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
                const url =
                    `/admin/presensi/siswa/data?tanggal=${tanggal}&jurusan_id=${jurusanId}&kelas_id=${kelasId}&device_id=${deviceId}${cacheBuster}`;

                try {
                    const response = await fetch(url, {
                        cache: 'no-store'
                    }); // Menambahkan opsi untuk mencegah cache
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const data = await response.json();
                    renderTable(data);
                } catch (error) {
                    console.error('Error fetching data:', error);
                    tableBody.innerHTML =
                        `<tr><td colspan="8" class="p-4 text-center text-red-500">Gagal memuat data.</td></tr>`;
                }
            }

            function renderTable(data) {
                tableBody.innerHTML = '';
                if (data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="9" class="p-8 text-center">
                                <div class="flex flex-col items-center space-y-3 text-gray-500">
                                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data absensi</p>
                                    <p class="text-sm">untuk filter yang dipilih</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }
                data.forEach((item, index) => {
                    let statusBadge = '';
                    switch (item.status) {
                        case 'hadir':
                            statusBadge =
                                `<span class="inline-flex items-center px-3 py-1 font-semibold text-sm leading-tight text-green-800 bg-green-100 rounded-full border border-green-200"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>Hadir</span>`;
                            break;
                        case 'terlambat':
                            statusBadge =
                                `<span class="inline-flex items-center px-3 py-1 font-semibold text-sm leading-tight text-yellow-800 bg-yellow-100 rounded-full border border-yellow-200"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>Terlambat</span>`;
                            break;
                        case 'sakit':
                            statusBadge =
                                `<span class="inline-flex items-center px-3 py-1 font-semibold text-sm leading-tight text-blue-800 bg-blue-100 rounded-full border border-blue-200"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 11-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12z" clip-rule="evenodd"></path></svg>Sakit</span>`;
                            break;
                        case 'izin':
                            statusBadge =
                                `<span class="inline-flex items-center px-3 py-1 font-semibold text-sm leading-tight text-gray-800 bg-gray-100 rounded-full border border-gray-200"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>Izin</span>`;
                            break;
                        default:
                            statusBadge =
                                `<span class="inline-flex items-center px-3 py-1 font-semibold text-sm leading-tight text-red-800 bg-red-100 rounded-full border border-red-200"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>Alpha</span>`;
                    }

                    // Status Pulang Badge
                    let statusPulangBadge = '';
                    if (item.status_pulang === 'sudah_pulang') {
                        statusPulangBadge =
                            `<span class="inline-flex items-center px-3 py-1 font-semibold text-sm leading-tight text-purple-800 bg-purple-100 rounded-full border border-purple-200"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3 3a1 1 0 01-1.414 0l-1.5-1.5a1 1 0 011.414-1.414L10 10.586l2.293-2.293a1 1 0 011.414 1.414z" clip-rule="evenodd"></path></svg>Sudah Pulang</span>`;
                    } else {
                        statusPulangBadge =
                            `<span class="inline-flex items-center px-3 py-1 font-semibold text-sm leading-tight text-orange-800 bg-orange-100 rounded-full border border-orange-200"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>Belum Pulang</span>`;
                    }

                    const row = `
                        <tr class="bg-white border-b border-gray-100 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-200 ${index % 2 === 0 ? 'bg-gray-50/50' : ''}">
                            <td class="px-6 py-4 font-bold text-gray-900">${item.no}</td>
                            <td class="px-6 py-4 font-semibold text-gray-800">${item.nama_siswa}</td>
                            <td class="px-6 py-4 text-gray-600">${item.jurusan}</td>
                            <td class="px-6 py-4 text-gray-600">${item.kelas}</td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-800">${item.waktu_masuk}</td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-800">${item.waktu_keluar}</td>
                            <td class="px-6 py-4 text-gray-600">${item.ruangan}</td>
                            <td class="px-6 py-4">${statusBadge}</td>
                            <td class="px-6 py-4">${statusPulangBadge}</td>
                        </tr>
                    `;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });
            }

            function formatIndonesianDate(dateString) {
                if (!dateString) return '';
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                    'September', 'Oktober', 'November', 'Desember'
                ];
                const date = new Date(dateString + 'T00:00:00');
                return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()} (${days[date.getDay()]})`;
            }

            function startAutoRefresh() {
                clearInterval(fetchInterval);
                tableBody.innerHTML = `
                    <tr class="animate-pulse">
                        <td colspan="9" class="p-8 text-center">
                            <div class="flex items-center justify-center gap-3 text-gray-500">
                                <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-lg">Memuat data terbaru...</span>
                            </div>
                        </td>
                    </tr>
                `;
                fetchAttendanceData();
                fetchInterval = setInterval(fetchAttendanceData, 5000);
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
