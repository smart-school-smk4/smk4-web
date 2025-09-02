@extends('layouts.dashboard')

@section('title', 'Smart School | Laporan')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                                <i class="fas fa-chart-bar text-blue-500 mr-3"></i>
                                Dashboard Laporan
                            </h1>
                            <p class="text-gray-600">Kelola dan pantau laporan sistem sekolah</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">{{ date('l, d F Y') }}</div>
                            <div class="text-xs text-gray-400">{{ date('H:i:s') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Cards -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Laporan Absensi Card -->
                <div class="group hover:scale-105 transition-all duration-300">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg overflow-hidden">
                        <div class="p-6 text-white">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                                    <i class="fas fa-clipboard-check text-2xl"></i>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm opacity-80">Status</div>
                                    <div class="font-bold">Aktif</div>
                                </div>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Laporan Absensi</h3>
                            <p class="text-blue-100 mb-4">Data absensi siswa dan guru lengkap</p>
                            <div class="bg-white bg-opacity-10 rounded-lg p-3 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Progress</span>
                                    <span>100%</span>
                                </div>
                                <div class="w-full bg-white bg-opacity-20 rounded-full h-2 mt-2">
                                    <div class="bg-white h-2 rounded-full" style="width: 100%"></div>
                                </div>
                            </div>
                            <a href="{{ route('admin.laporan.absensi') }}"
                                class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium">
                                <i class="fas fa-arrow-right mr-2"></i>
                                Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Laporan Lainnya Card -->
                <div class="group hover:scale-105 transition-all duration-300">
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-lg overflow-hidden">
                        <div class="p-6 text-white">
                            <div class="flex items-center justify-between mb-4">
                                <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                                    <i class="fas fa-chart-line text-2xl"></i>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm opacity-80">Status</div>
                                    <div class="font-bold">Coming Soon</div>
                                </div>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Laporan Lainnya</h3>
                            <p class="text-emerald-100 mb-4">Fitur laporan tambahan sedang dikembangkan</p>
                            <div class="bg-white bg-opacity-10 rounded-lg p-3 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Progress</span>
                                    <span>60%</span>
                                </div>
                                <div class="w-full bg-white bg-opacity-20 rounded-full h-2 mt-2">
                                    <div class="bg-white h-2 rounded-full" style="width: 60%"></div>
                                </div>
                            </div>
                            <button
                                class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 text-white rounded-lg cursor-not-allowed font-medium">
                                <i class="fas fa-clock mr-2"></i>
                                Dalam Pengembangan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-rocket text-orange-500 mr-3"></i>
                        Aksi Cepat
                    </h2>
                    <p class="text-gray-600">Akses cepat ke laporan yang sering digunakan</p>
                </div>

                <div class="grid md:grid-cols-4 gap-4">
                    <!-- Absensi Bulan Ini -->
                    <a href="{{ route('admin.laporan.absensi') }}?bulan={{ date('m') }}&tahun={{ date('Y') }}"
                        class="group block p-4 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105">
                        <div class="text-white">
                            <div class="flex items-center justify-between mb-3">
                                <i class="fas fa-calendar-month text-2xl"></i>
                                <span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">Hot</span>
                            </div>
                            <h4 class="font-bold mb-1">Bulan Ini</h4>
                            <p class="text-sm text-purple-100">Absensi {{ date('F Y') }}</p>
                        </div>
                    </a>

                    <!-- Absensi Hari Ini -->
                    <a href="{{ route('admin.laporan.absensi') }}?tanggal={{ date('Y-m-d') }}"
                        class="group block p-4 bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105">
                        <div class="text-white">
                            <div class="flex items-center justify-between mb-3">
                                <i class="fas fa-calendar-day text-2xl"></i>
                                <span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">New</span>
                            </div>
                            <h4 class="font-bold mb-1">Hari Ini</h4>
                            <p class="text-sm text-cyan-100">{{ date('d M Y') }}</p>
                        </div>
                    </a>

                    <!-- Export Excel -->
                    <a href="{{ route('admin.laporan.export') }}?bulan={{ date('m') }}&tahun={{ date('Y') }}"
                        class="group block p-4 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105">
                        <div class="text-white">
                            <div class="flex items-center justify-between mb-3">
                                <i class="fas fa-file-excel text-2xl"></i>
                                <span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">Excel</span>
                            </div>
                            <h4 class="font-bold mb-1">Export</h4>
                            <p class="text-sm text-emerald-100">Download Excel</p>
                        </div>
                    </a>

                    <!-- Filter Custom -->
                    <a href="{{ route('admin.laporan.absensi') }}"
                        class="group block p-4 bg-gradient-to-r from-gray-500 to-gray-600 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105">
                        <div class="text-white">
                            <div class="flex items-center justify-between mb-3">
                                <i class="fas fa-filter text-2xl"></i>
                                <span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">Pro</span>
                            </div>
                            <h4 class="font-bold mb-1">Filter Custom</h4>
                            <p class="text-sm text-gray-100">Sesuaikan filter</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Statistics Preview -->
            <div class="mt-8 grid md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg mr-4">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Total Siswa Aktif</p>
                            <p class="text-2xl font-bold text-gray-800">1,250</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-emerald-100 rounded-lg mr-4">
                            <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Kehadiran Hari Ini</p>
                            <p class="text-2xl font-bold text-gray-800">89.5%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-100 rounded-lg mr-4">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Rata-rata Terlambat</p>
                            <p class="text-2xl font-bold text-gray-800">12 menit</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
