@extends('layouts.dashboard')

@section('title', 'Manajemen Jadwal Bel Sekolah')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manajemen Jadwal Bel Sekolah</h1>
            <p class="text-sm text-gray-600">Jadwal Bel Otomatis dan Kontrol Sistem</p>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <!-- Live Clock -->
            <div class="hidden md:flex items-center gap-2 text-gray-600 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                <span id="liveClock" class="font-medium">{{ now()->format('H:i:s') }}</span>
                <span class="text-gray-500">{{ now()->format('d M Y') }}</span>
            </div>

            <!-- Action Buttons -->
            <a href="{{ route('bel.history.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                Riwayat
            </a>
            
            <a href="{{ route('bel.create') }}" class="btn-blue">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah Jadwal
            </a>
        </div>
    </div>

    <!-- System Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @include('admin.bel.partials.status-card', [
            'id' => 'mqttCard',
            'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01',
            'title' => 'MQTT Connection',
            'statusId' => 'mqttStatusText',
            'status' => 'Connected',
            'detailsId' => 'mqttStatusDetails',
            'details' => 'Terakhir diperbarui: '.now()->format('H:i:s')
        ])

        @include('admin.bel.partials.status-card', [
            'id' => 'rtcCard',
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'title' => 'ESP Module',
            'statusId' => 'rtcStatusText',
            'status' => 'Connected',
            'detailsId' => 'rtcTimeText',
            'details' => now()->format('Y-m-d H:i:s')
        ])

        <!-- Next Bell Countdown -->
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500 hover:shadow-md transition duration-200">
            <div class="flex items-start">
                <div class="p-3 rounded-full bg-blue-100 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Jadwal Bel Berikutnya</h3>
                    <p class="text-lg font-semibold text-gray-700" id="nextScheduleCountdown">
                        {{ $nextSchedule ? 'Menghitung ...' : 'Tidak Ada Jadwal Bel Yang Akan Datang' }}
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

    <!-- Today's Schedule Section -->
    @if($todaySchedules->count() > 0)
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-6 mb-8 rounded-r-xl shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
                Jadwal Hari Ini ({{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }})
            </h3>
            <span class="bg-blue-600 text-white text-xs font-semibold px-4 py-1.5 rounded-full shadow-sm">
                {{ $todaySchedules->count() }} {{ $todaySchedules->count() > 1 ? 'Jadwal' : 'Jadwal' }}
            </span>
        </div>
        
        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($todaySchedules as $schedule)
            <div class="bg-white p-4 rounded-lg shadow-sm border border-blue-100 hover:shadow-md transition duration-200 flex justify-between items-center">
                <div>
                    <span class="font-medium text-gray-800">{{ $schedule->formatted_time }}</span>
                    <span class="text-xs text-gray-500 block mt-1">File {{ $schedule->file_number }}</span>
                </div>
                <div class="flex items-center">
                    <span class="px-2 py-1 text-xs rounded-full font-medium {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($schedule->is_now)
                    <span class="ml-2 px-2 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-800 animate-pulse">
                        SEKARANG
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Main Content Section -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Table Header with Filters -->
        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <!-- Search and Filters -->
                <form action="{{ route('bel.index') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                    <!-- Day Filter -->
                    <div class="w-full md:w-auto">
                        <label for="hari" class="block text-sm font-medium text-gray-700 mb-1">Filter Hari</label>
                        <select name="hari" id="hari" onchange="this.form.submit()" class="select-input">
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
                            <input type="text" id="search" name="search" placeholder="Cari Jadwal..." 
                                   value="{{ request('search') }}"
                                   class="search-input">
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
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Manual Ring Button -->
                    <button onclick="showRingModal()" class="btn-yellow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Bunyikan Bell
                    </button>

                    <!-- Sync Button -->
                    <button onclick="syncSchedules()" class="btn-green">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Sync Sekarang
                    </button>
                </div>
            </div>
        </div>

        <!-- Schedule Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-blue-50 to-indigo-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider w-16">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Hari</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">File Audio</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($schedules as $index => $schedule)
                    <tr class="hover:bg-blue-50 transition duration-150" id="schedule-{{ $schedule->id }}">
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">
                                {{ $schedules->firstItem() + $index }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $schedule->hari }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-semibold text-gray-900">{{ $schedule->formatted_time }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gradient-to-r from-purple-100 to-pink-100 border border-purple-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" />
                                </svg>
                                <span class="text-sm font-bold text-purple-700">{{ $schedule->file_number }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center items-center space-x-2">
                                <a href="{{ route('bel.edit', $schedule->id) }}" 
                                   class="inline-flex items-center justify-center p-2 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 hover:text-blue-800 transition duration-150" 
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </a>
                                <form action="{{ route('bel.delete', $schedule->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            class="inline-flex items-center justify-center p-2 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-800 transition duration-150 delete-btn" 
                                            title="Hapus">
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
                        <td colspan="5" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                <h3 class="text-lg font-medium mb-1">Tidak Ada Jadwal Tersedia</h3>
                                <p class="max-w-md text-center mb-4">Anda Belum Membuat Jadwal Bel. Klik Button Diatas Untuk Menambahkan Jadwal Bel</p>
                                <a href="{{ route('bel.create') }}" class="btn-blue">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Tambah Jadwal
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($schedules->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $schedules->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Include JavaScript -->
@include('admin.bel.partials.scripts')
@endsection
@push('styles')
    @vite('resources/css/app.css')
@endpush