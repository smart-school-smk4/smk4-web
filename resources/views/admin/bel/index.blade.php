@extends('layouts.dashboard')

@section('title', 'Manajemen Bel Sekolah')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Jadwal Bel Sekolah</h1>
            <p class="text-sm text-gray-600">Kelola jadwal bel otomatis sekolah</p>
        </div>
        
        <div class="flex gap-3">
            <!-- Live Clock -->
            <div class="hidden md:flex items-center gap-2 text-gray-600 bg-white px-3 py-2 rounded-lg shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                <span id="liveClock">{{ now()->format('H:i:s') }}</span>
            </div>

            <!-- Tombol History -->
            <a href="{{ route('bel.history') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 shadow-md hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                Riwayat Bel
            </a>
            
            <!-- Action Buttons -->
            <a href="{{ route('bel.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 shadow-md hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah Jadwal
            </a>
            
            <div class="relative inline-block">
                <button onclick="toggleDropdown()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg inline-flex items-center transition duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                    </svg>
                    Aksi Massal
                </button>
                <div id="actionDropdown" class="hidden absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                    <div class="py-1">
                        <button onclick="activateAll()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                            Aktifkan Semua
                        </button>
                        <button onclick="deactivateAll()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                            Nonaktifkan Semua
                        </button>
                        <button onclick="confirmDeleteAll()" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 w-full text-left">
                            Hapus Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- MQTT Status Card -->
        <div id="mqttCard" class="bg-white rounded-xl shadow-md p-5 border-l-4 border-green-500">
            <div class="flex items-center">
                <div id="mqttIconBg" class="p-3 rounded-full bg-green-100">
                    <svg id="mqttIconSvg" class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Koneksi MQTT</h3>
                    <p id="mqttStatusText" class="text-lg font-semibold text-green-600">Tersambung</p>
                </div>
            </div>
        </div>

        <!-- RTC Status Card -->
        <div id="rtcCard" class="bg-white rounded-xl shadow-md p-5 border-l-4 border-green-500">
            <div class="flex items-center">
                <div id="rtcIcon" class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Modul RTC</h3>
                    <p id="rtcStatusText" class="text-lg font-semibold text-green-600">Tersambung</p>
                    <p id="rtcTimeText" class="text-xs text-gray-500 mt-1">2023-10-01T12:34:56</p>
                </div>
            </div>
        </div>

        <!-- DFPlayer Status Card -->
        <div id="dfplayerCard" class="bg-white rounded-xl shadow-md p-5 border-l-4 border-green-500">
            <div class="flex items-center">
                <div id="dfplayerIcon" class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">DFPlayer</h3>
                    <p id="dfplayerStatusText" class="text-lg font-semibold text-green-600">Tersambung</p>
                </div>
            </div>
        </div>
    

        <!-- Next Bell Countdown - Tetap bekerja tanpa MQTT -->
        <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Bel Berikutnya</h3>
                    <p class="text-lg font-semibold text-gray-700" id="nextScheduleCountdown">
                        @if($nextSchedule)
                            Menghitung...
                        @else
                            Tidak ada jadwal
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-1" id="nextScheduleTime">
                        @if($nextSchedule)
                            {{ $nextSchedule->hari }}, {{ $nextSchedule->waktu }} (File {{ $nextSchedule->file_number }})
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Schedule -->
    @if($todaySchedules->count() > 0)
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                Jadwal Hari Ini ({{ \Carbon\Carbon::now()->isoFormat('dddd') }})
            </h3>
            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                {{ $todaySchedules->count() }} Jadwal
            </span>
        </div>
        
        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($todaySchedules as $schedule)
            <div class="bg-white p-3 rounded-lg shadow-sm border border-blue-100 flex justify-between items-center">
                <div>
                    <span class="font-medium">{{ $schedule->formatted_time }}</span>
                    <span class="text-xs text-gray-500 block">{{ $schedule->file_number }}</span>
                </div>
                <span class="px-2 py-1 text-xs rounded-full {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Filter and Table Section -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <form action="{{ route('bel.index') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                    <!-- Day Filter -->
                    <div class="w-full md:w-auto">
                        <label for="hari" class="block text-sm font-medium text-gray-700 mb-1">Filter Hari</label>
                        <select name="hari" id="hari" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Hari</option>
                            @foreach (\App\Models\JadwalBel::DAYS as $day)
                                <option value="{{ $day }}" {{ request('hari') === $day ? 'selected' : '' }}>
                                    {{ $day }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Search Box -->
                    <div class="w-full md:w-auto flex-grow">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <div class="relative">
                            <input type="text" id="search" name="search" placeholder="Cari jadwal..." 
                                   value="{{ request('search') }}"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    @if(request('hari') || request('search'))
                        <div class="flex items-end">
                            <a href="{{ route('bel.index') }}" class="text-sm text-blue-600 hover:underline flex items-center mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </form>
                
                <!-- Quick Actions -->
                <div class="flex items-center gap-2">
                    <button onclick="showRingModal()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Bunyikan Bel
                    </button>

                    <!-- Sync Button -->
                    <button onclick="syncSchedules()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm12 8a1 1 0 100-2h-3a1 1 0 100 2h3zm-7 0a1 1 0 100-2H5a1 1 0 100 2h3z" clip-rule="evenodd" />
                        </svg>
                        Sinkronisasi Jadwal
                    </button>
                    
                    <!-- <button onclick="getLiveStatus()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center transition duration-300 shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Refresh Status
                    </button> -->
                </div>
            </div>
        </div>

        <!-- Schedule Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-gray-50 transition duration-150" id="schedule-{{ $schedule->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $schedule->hari }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $schedule->formatted_time }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $schedule->file_number }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form id="toggle-form-{{ $schedule->id }}" class="toggle-form" 
                                action="{{ route('api.bel.toggle-status', $schedule->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" 
                                        onchange="this.closest('form').submit()"
                                        {{ $schedule->is_active ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">
                                        {{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </label>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('bel.edit', $schedule->id) }}" class="text-blue-600 hover:text-blue-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </a>
                                <form action="{{ route('bel.delete', $schedule->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="text-red-600 hover:text-red-900 delete-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada jadwal</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($schedules->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $schedules->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

function showLoading(title = 'Memproses...') {
    Swal.fire({
        title: title,
        html: 'Harap tunggu sebentar...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading(); // Menampilkan animasi loading
        }
    });
}

// Live Clock
function updateClock() {
    const now = new Date();
    document.getElementById('liveClock').textContent = 
        now.getHours().toString().padStart(2, '0') + ':' + 
        now.getMinutes().toString().padStart(2, '0') + ':' + 
        now.getSeconds().toString().padStart(2, '0');
}
setInterval(updateClock, 1000);

// Toggle Dropdown
function toggleDropdown() {
    document.getElementById('actionDropdown').classList.toggle('hidden');
}

// Close dropdown when clicking outside
window.addEventListener('click', function(e) {
    if (!e.target.closest('.relative.inline-block')) {
        document.getElementById('actionDropdown').classList.add('hidden');
    }
});

// SweetAlert Toast
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// Delete confirmation - MODIFIED VERSION
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Hapus Jadwal?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading('Menghapus...');
                    form.submit();
                }
            });
        });
    });
});

// Bulk delete confirmation - MODIFIED VERSION
function confirmDeleteAll() {
    Swal.fire({
        title: 'Hapus Semua Jadwal?',
        text: "Anda akan menghapus semua jadwal bel!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            showLoading('Menghapus semua...');
            return fetch("{{ route('bel.delete-all') }}", {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText);
                }
                return response.json();
            })
            .catch(error => {
                Swal.hideLoading();
                Swal.showValidationMessage(
                    `Gagal menghapus: ${error}`
                );
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: result.value.message || 'Semua jadwal berhasil dihapus',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    });
}

// Toggle schedule status
function toggleScheduleStatus(checkbox, id) {
    const isActive = checkbox.checked;
    showLoading('Memperbarui status...');
    
    fetch(`/admin/bel/${id}/toggle-status`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ is_active: isActive })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        Swal.close();
        if (!data.success) {
            checkbox.checked = !isActive;
            throw new Error(data.message || 'Failed to update status');
        }
        Toast.fire({
            icon: 'success',
            title: 'Status berhasil diperbarui'
        });
        updateStatusBadge(id, data.is_active);
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal memperbarui status',
            text: error.message
        });
    });
}

function updateStatusBadge(id, isActive) {
    const badge = document.querySelector(`#schedule-${id} .status-badge`);
    if (badge) {
        badge.className = isActive 
            ? 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-800' 
            : 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800';
        badge.textContent = isActive ? 'Aktif' : 'Nonaktif';
    }
}

// Activate all schedules - MODIFIED VERSION
function activateAll() {
    showLoading('Mengaktifkan semua...');
    fetch("{{ route('api.bel.activate-all') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Toast.fire({
                icon: 'success',
                title: data.message || 'Semua jadwal diaktifkan'
            });
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            Toast.fire({
                icon: 'error',
                title: data.message || 'Gagal mengaktifkan semua jadwal'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi kesalahan',
            text: error.message
        });
    });
}

// Deactivate all schedules - MODIFIED VERSION
function deactivateAll() {
    showLoading('Menonaktifkan semua...');
    fetch("{{ route('api.bel.deactivate-all') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Toast.fire({
                icon: 'success',
                title: data.message || 'Semua jadwal dinonaktifkan'
            });
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            Toast.fire({
                icon: 'error',
                title: data.message || 'Gagal menonaktifkan semua jadwal'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi kesalahan',
            text: error.message
        });
    });
}


// Ring bell modal
function showRingModal() {
    Swal.fire({
        title: 'Bunyikan Bel Manual',
        html: `
            <div class="mb-4">
                <label for="swal-file-number" class="block text-sm font-medium text-gray-700 mb-1">File Bel</label>
                <select id="swal-file-number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih File</option>
                    @for($i = 1; $i <= 50; $i++)
                        <option value="{{ sprintf('%04d', $i) }}">File {{ sprintf('%04d', $i) }}</option>
                    @endfor
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Bunyikan',
        cancelButtonText: 'Batal',
        focusConfirm: false,
        preConfirm: () => {
            const fileNumber = document.getElementById('swal-file-number').value;
            if (!fileNumber) {
                Swal.showValidationMessage('Pilih file bel terlebih dahulu');
                return false;
            }
            return { file_number: fileNumber };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            ringBell(result.value.file_number);
        }
    });
}

// Sync schedules function
function syncSchedules() {
    showLoading('Menyinkronisasi jadwal...');
    fetch("{{ route('api.bel.sync') }}", {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Toast.fire({
                icon: 'success',
                title: data.message || 'Jadwal berhasil disinkronisasi'
            });
        } else {
            Toast.fire({
                icon: 'error',
                title: data.message || 'Gagal menyinkronisasi jadwal'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi kesalahan',
            text: error.message
        });
    });
}

// Ring bell function - MODIFIED VERSION
function ringBell(fileNumber) {
    showLoading('Membunyikan bel...');
    fetch("{{ route('api.bel.ring') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ file_number: fileNumber })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Toast.fire({
                icon: 'success',
                title: 'Bel berhasil dibunyikan'
            });
        } else {
            Toast.fire({
                icon: 'error',
                title: data.message || 'Gagal membunyikan bel'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi kesalahan',
            text: error.message
        });
    });
}

// Get live status - MODIFIED VERSION
function getLiveStatus() {
    // showLoading('Memperbarui status...');
    fetch("{{ route('api.bel.status') }}", {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Received status data:', data);
        Swal.close();
        if (data.success) {
            updateDeviceStatus(data.data);
            // Toast.fire({
            //     icon: 'success',
            //     title: 'Status perangkat diperbarui'
            // });
        }
    })
    .catch(error => {
        // Swal.fire({
        //     icon: 'error',
        //     title: 'Gagal memperbarui status',
        //     text: error.message
        // });
    });
}

// Update device status
function updateDeviceStatus(data) {
    // Update MQTT Status
    if (data.mqtt_status !== undefined) {
        const mqttCard = document.querySelector('#mqttCard');
        const mqttStatusText = document.querySelector('#mqttStatusText');
        
        if (data.mqtt_status === true || data.mqtt_status === 'Connected') {
            mqttCard.classList.replace('border-red-500', 'border-green-500');
            mqttCard.querySelector('div > div').classList.replace('bg-red-100', 'bg-green-100');
            mqttCard.querySelector('svg').classList.replace('text-red-600', 'text-green-600');
            mqttStatusText.textContent = 'Tersambung';
            mqttStatusText.classList.replace('text-red-600', 'text-green-600');
        } else {
            mqttCard.classList.replace('border-green-500', 'border-red-500');
            mqttCard.querySelector('div > div').classList.replace('bg-green-100', 'bg-red-100');
            mqttCard.querySelector('svg').classList.replace('text-green-600', 'text-red-600');
            mqttStatusText.textContent = 'Terputus';
            mqttStatusText.classList.replace('text-green-600', 'text-red-600');
        }
    }

    // Update RTC Status
    if (data.rtc !== undefined) {
        const rtcCard = document.querySelector('#rtcCard');
        const rtcIcon = document.querySelector('#rtcIcon');
        const rtcStatusText = document.querySelector('#rtcStatusText');
        const rtcTimeText = document.querySelector('#rtcTimeText');
        
        if (data.rtc === true) {
            rtcCard.classList.replace('border-red-500', 'border-green-500');
            rtcIcon.classList.replace('bg-red-100', 'bg-green-100');
            rtcIcon.querySelector('svg').classList.replace('text-red-600', 'text-green-600');
            rtcStatusText.textContent = 'Tersambung';
            rtcStatusText.classList.replace('text-red-600', 'text-green-600');
        } else {
            rtcCard.classList.replace('border-green-500', 'border-red-500');
            rtcIcon.classList.replace('bg-green-100', 'bg-red-100');
            rtcIcon.querySelector('svg').classList.replace('text-green-600', 'text-red-600');
            rtcStatusText.textContent = 'Terputus';
            rtcStatusText.classList.replace('text-green-600', 'text-red-600');
        }
        
        if (data.rtc_time) {
            rtcTimeText.textContent = data.rtc_time;
        }
    }

    // Update DFPlayer Status
    if (data.dfplayer !== undefined) {
        const dfplayerCard = document.querySelector('#dfplayerCard');
        const dfplayerIcon = document.querySelector('#dfplayerIcon');
        const dfplayerStatusText = document.querySelector('#dfplayerStatusText');
        
        if (data.dfplayer === true) {
            dfplayerCard.classList.replace('border-red-500', 'border-green-500');
            dfplayerIcon.classList.replace('bg-red-100', 'bg-green-100');
            dfplayerIcon.querySelector('svg').classList.replace('text-red-600', 'text-green-600');
            dfplayerStatusText.textContent = 'Tersambung';
            dfplayerStatusText.classList.replace('text-red-600', 'text-green-600');
        } else {
            dfplayerCard.classList.replace('border-green-500', 'border-red-500');
            dfplayerIcon.classList.replace('bg-green-100', 'bg-red-100');
            dfplayerIcon.querySelector('svg').classList.replace('text-green-600', 'text-red-600');
            dfplayerStatusText.textContent = 'Terputus';
            dfplayerStatusText.classList.replace('text-green-600', 'text-red-600');
        }
    }
}

function updateNextSchedule() {
    fetch("{{ route('api.bel.next-schedule') }}")
    .then(response => {
        if (!response.ok) throw new Error('Gagal memuat jadwal');
        return response.json();
    })
    .then(data => {
        const countdownEl = document.getElementById('nextScheduleCountdown');
        const timeEl = document.getElementById('nextScheduleTime');
        
        if (data.success && data.next_schedule && data.next_schedule.is_active) {
            const now = new Date();
            const [hours, minutes, seconds] = data.next_schedule.time.split(':').map(Number);
            
            // Create target time
            let targetTime = new Date();
            targetTime.setHours(hours, minutes, seconds || 0, 0);
            
            // Adjust day if needed
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const currentDayIndex = now.getDay(); // 0=Sunday, 6=Saturday
            const targetDayName = data.next_schedule.hari;
            const targetDayIndex = days.indexOf(targetDayName);
            
            let daysToAdd = (targetDayIndex - currentDayIndex + 7) % 7;
            if (daysToAdd === 0 && targetTime <= now) {
                daysToAdd = 7; // Next week same day
            }
            targetTime.setDate(now.getDate() + daysToAdd);
            
            // Update display
            timeEl.textContent = `${targetDayName}, ${data.next_schedule.time} (File ${data.next_schedule.file_number})`;
            
            // Countdown function
            const updateCountdown = () => {
                const diff = targetTime - new Date();
                
                if (diff > 0) {
                    const h = Math.floor(diff / 3600000);
                    const m = Math.floor((diff % 3600000) / 60000);
                    const s = Math.floor((diff % 60000) / 1000);
                    
                    countdownEl.innerHTML = `
                        <span class="text-amber-600">${h.toString().padStart(2, '0')}</span>h 
                        <span class="text-amber-600">${m.toString().padStart(2, '0')}</span>m 
                        <span class="text-amber-600">${s.toString().padStart(2, '0')}</span>s
                    `;
                } else {
                    countdownEl.innerHTML = '<span class="text-green-600 font-bold">SEDANG BERLANGSUNG</span>';
                    clearInterval(window.countdownInterval);
                    setTimeout(updateNextSchedule, 5000);
                }
            };
            
            // Clear previous interval and start new one
            if (window.countdownInterval) clearInterval(window.countdownInterval);
            updateCountdown();
            window.countdownInterval = setInterval(updateCountdown, 1000);
            
        } else {
            countdownEl.textContent = 'Tidak ada jadwal aktif';
            timeEl.textContent = '';
            if (window.countdownInterval) clearInterval(window.countdownInterval);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('nextScheduleCountdown').textContent = 'Error memuat jadwal';
        document.getElementById('nextScheduleTime').textContent = '';
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    updateNextSchedule();
    getLiveStatus();
    
    // Refresh every minute to stay accurate
    setInterval(updateNextSchedule, 60000);
    // Panggil getLiveStatus setiap 10 detik
    setInterval(getLiveStatus, 60000);
});

</script>
@endsection