@extends('layouts.dashboard')

@section('title', 'Smart School | Dashboard')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <h1 class="mb-6 text-3xl font-bold text-gray-800">Dashboard</h1>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
        
        <div class="flex items-center p-4 bg-white border-l-4 border-blue-500 rounded-lg shadow-lg">
            <div class="flex-grow">
                <p class="text-xs font-bold text-blue-500 uppercase">Jumlah Siswa</p>
                <p class="text-2xl font-bold text-gray-800">{{ $jumlahSiswa }}</p>
            </div>
            <i class="fas fa-user-graduate text-4xl text-gray-300"></i>
        </div>

        <div class="flex items-center p-4 bg-white border-l-4 border-green-500 rounded-lg shadow-lg">
            <div class="flex-grow">
                <p class="text-xs font-bold text-green-500 uppercase">Jumlah Guru</p>
                <p class="text-2xl font-bold text-gray-800">{{ $jumlahGuru }}</p>
            </div>
            <i class="fas fa-chalkboard-teacher text-4xl text-gray-300"></i>
        </div>

        <div class="flex items-center p-4 bg-white border-l-4 border-cyan-500 rounded-lg shadow-lg">
            <div class="flex-grow">
                <p class="text-xs font-bold text-cyan-500 uppercase">Jumlah Kelas</p>
                <p class="text-2xl font-bold text-gray-800">{{ $jumlahKelas }}</p>
            </div>
            <i class="fas fa-school text-4xl text-gray-300"></i>
        </div>
        
        <div class="flex items-center p-4 bg-white border-l-4 border-yellow-500 rounded-lg shadow-lg">
            <div class="flex-grow">
                <p class="text-xs font-bold text-yellow-500 uppercase">Jumlah Jurusan</p>
                <p class="text-2xl font-bold text-gray-800">{{ $jumlahJurusan }}</p>
            </div>
            <i class="fas fa-book-reader text-4xl text-gray-300"></i>
        </div>

        <div class="flex items-center p-4 bg-white border-l-4 border-gray-500 rounded-lg shadow-lg">
            <div class="flex-grow">
                <p class="text-xs font-bold text-gray-500 uppercase">Jumlah Ruangan</p>
                <p class="text-2xl font-bold text-gray-800">{{ $jumlahRuangan }}</p>
            </div>
            <i class="fas fa-door-open text-4xl text-gray-300"></i>
        </div>

        <div class="flex items-center p-4 bg-white border-l-4 border-gray-800 rounded-lg shadow-lg">
            <div class="flex-grow">
                <p class="text-xs font-bold text-gray-800 uppercase">Jumlah Device</p>
                <p class="text-2xl font-bold text-gray-800">{{ $jumlahDevice }}</p>
            </div>
            <i class="fas fa-server text-4xl text-gray-300"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mt-8 lg:grid-cols-12">
        
        <div class="lg:col-span-7">
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 border-b">
                    <h6 class="font-bold text-gray-800">📊 Pendaftaran Siswa Baru (Tahunan)</h6>
                </div>
                <div class="p-4">
                    <div class="relative h-72">
                         <canvas id="studentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 border-b">
                    <h6 class="font-bold text-gray-800">📢 Pengumuman Terbaru</h6>
                </div>
                <div class="p-4">
                    <ul class="space-y-4">
                        <li class="flex items-start justify-between">
                            <div>
                                <p class="font-bold">Rapat Wali Murid</p>
                                <p class="text-sm text-gray-600">Rapat akan dilaksanakan pada tanggal 25 Juli 2025.</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold text-white bg-blue-500 rounded-full">Penting</span>
                        </li>
                        <li class="flex items-start justify-between">
                           <div>
                                <p class="font-bold">Libur Idul Adha</p>
                                <p class="text-sm text-gray-600">Sekolah libur pada tanggal 28-30 Juli 2025.</p>
                            </div>
                        </li>
                         <li class="flex items-start justify-between">
                           <div>
                                <p class="font-bold">Pendaftaran Ekstrakurikuler</p>
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