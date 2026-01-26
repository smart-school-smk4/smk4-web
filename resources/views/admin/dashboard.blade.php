@extends('layouts.dashboard')

@section('title', 'Smart School | Dashboard')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h1>
        <p class="text-gray-600">Selamat datang kembali! Berikut ringkasan data sekolah.</p>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 mb-8">
        <!-- Siswa Card -->
        <div class="bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-100 uppercase tracking-wide">Jumlah Siswa</p>
                    <p class="text-4xl font-bold mt-2">{{ $jumlahSiswa }}</p>
                </div>
                <div class="bg-white/20 rounded-xl p-4 backdrop-blur-sm">
                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Guru Card -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-100 uppercase tracking-wide">Jumlah Guru</p>
                    <p class="text-4xl font-bold mt-2">{{ $jumlahGuru }}</p>
                </div>
                <div class="bg-white/20 rounded-xl p-4 backdrop-blur-sm">
                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Kelas Card -->
        <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-cyan-100 uppercase tracking-wide">Jumlah Kelas</p>
                    <p class="text-4xl font-bold mt-2">{{ $jumlahKelas }}</p>
                </div>
                <div class="bg-white/20 rounded-xl p-4 backdrop-blur-sm">
                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Jurusan Card -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-amber-100 uppercase tracking-wide">Jumlah Jurusan</p>
                    <p class="text-4xl font-bold mt-2">{{ $jumlahJurusan }}</p>
                </div>
                <div class="bg-white/20 rounded-xl p-4 backdrop-blur-sm">
                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Ruangan Card -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-100 uppercase tracking-wide">Jumlah Ruangan</p>
                    <p class="text-4xl font-bold mt-2">{{ $jumlahRuangan }}</p>
                </div>
                <div class="bg-white/20 rounded-xl p-4 backdrop-blur-sm">
                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Device Card -->
        <div class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-200 uppercase tracking-wide">Jumlah Device</p>
                    <p class="text-4xl font-bold mt-2">{{ $jumlahDevice }}</p>
                </div>
                <div class="bg-white/20 rounded-xl p-4 backdrop-blur-sm">
                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Announcements Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Chart Section -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4">
                    <h6 class="font-bold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        Pendaftaran Siswa Baru (Tahunan)
                    </h6>
                </div>
                <div class="p-6">
                    <div class="relative h-72">
                         <canvas id="studentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcements Section -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4">
                    <h6 class="font-bold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                        </svg>
                        Pengumuman Terbaru
                    </h6>
                </div>
                <div class="p-6">
                    <ul class="space-y-4">
                        <li class="p-4 bg-blue-50 rounded-xl border-l-4 border-primary-500">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-bold text-gray-800 mb-1">Rapat Wali Murid</p>
                                    <p class="text-sm text-gray-600">Rapat akan dilaksanakan pada tanggal 25 Juli 2025.</p>
                                </div>
                                <span class="ml-3 px-3 py-1 text-xs font-semibold text-white bg-primary-500 rounded-full whitespace-nowrap">Penting</span>
                            </div>
                        </li>
                        <li class="p-4 bg-gray-50 rounded-xl border-l-4 border-gray-300">
                           <div>
                                <p class="font-bold text-gray-800 mb-1">Libur Idul Adha</p>
                                <p class="text-sm text-gray-600">Sekolah libur pada tanggal 28-30 Juli 2025.</p>
                            </div>
                        </li>
                         <li class="p-4 bg-gray-50 rounded-xl border-l-4 border-gray-300">
                           <div>
                                <p class="font-bold text-gray-800 mb-1">Pendaftaran Ekstrakurikuler</p>
                                <p class="text-sm text-gray-600">Dibuka hingga akhir bulan ini. Segera daftar!</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- CDN untuk Chart.js tetap sama --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = @json($chartLabels);
    const dataValues = @json($chartData);

    const data = {
        labels: labels,
        datasets: [{
            label: 'Jumlah Pendaftar',
            backgroundColor: 'rgba(59, 130, 246, 0.5)', // Warna biru Tailwind
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            hoverBackgroundColor: 'rgba(59, 130, 246, 0.7)',
            data: dataValues,
        }]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    };
    
    // Render grafik
    const studentChart = new Chart(
        document.getElementById('studentChart'),
        config
    );
</script>
@endpush