@extends('layouts.dashboard')

@section('title', 'School Bell Management System')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section with Breadcrumbs -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
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

                <!-- History Button -->
                <a href="{{ route('bel.history') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 shadow-md hover:shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    Riwayat
                </a>
                
                <!-- Create Button -->
                <a href="{{ route('bel.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 shadow-md hover:shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Jadwal
                </a>
                
                <!-- Bulk Actions Dropdown -->
                <div class="relative inline-block">
                    <button onclick="toggleDropdown()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg inline-flex items-center transition duration-300 border border-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                        Aksi Massal
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="actionDropdown" class="hidden absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-200">
                        <div class="py-1">
                            <button onclick="activateAll()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Aktifkan Semua
                            </button>
                            <button onclick="deactivateAll()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                NonAktifkan Semua
                            </button>
                            <button onclick="confirmDeleteAll()" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 w-full text-left flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Hapus Semua
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- MQTT Status Card -->
        <div id="mqttCard" class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition duration-200">
            <div class="flex items-start">
                <div id="mqttIconBg" class="p-3 rounded-full bg-green-100 flex-shrink-0">
                    <svg id="mqttIconSvg" class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">MQTT Connection</h3>
                    <p id="mqttStatusText" class="text-lg font-semibold text-green-600">Connected</p>
                    <p id="mqttStatusDetails" class="text-xs text-gray-500 mt-1">Terkahir diPerbarui: {{ now()->format('H:i:s') }}</p>
                </div>
            </div>
        </div>

        <!-- RTC Status Card -->
        <div id="rtcCard" class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition duration-200">
            <div class="flex items-start">
                <div id="rtcIcon" class="p-3 rounded-full bg-green-100 flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">RTC Module</h3>
                    <p id="rtcStatusText" class="text-lg font-semibold text-green-600">Connected</p>
                    <p id="rtcTimeText" class="text-xs text-gray-500 mt-1">{{ now()->format('Y-m-d\  H:i:s') }}</p>
                </div>
            </div>
        </div>

        <!-- DFPlayer Status Card -->
        <div id="dfplayerCard" class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition duration-200">
            <div class="flex items-start">
                <div id="dfplayerIcon" class="p-3 rounded-full bg-green-100 flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Audio Player</h3>
                    <p id="dfplayerStatusText" class="text-lg font-semibold text-green-600">Connected</p>
                    <p id="dfplayerDetails" class="text-xs text-gray-500 mt-1">50 Audio File Tersedia</p>
                </div>
            </div>
        </div>

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
                        @if($nextSchedule)
                            Menghitung ...
                        @else
                            Tidak Ada Jadwal Bel Yang Akan Datang
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

    <!-- Today's Schedule Section -->
    @if($todaySchedules->count() > 0)
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 rounded-r-lg">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-medium text-blue-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
                Today's Schedule ({{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }})
            </h3>
            <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-medium">
                {{ $todaySchedules->count() }} {{ $todaySchedules->count() > 1 ? 'Schedules' : 'Schedule' }}
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
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <!-- Search and Filters -->
                <form action="{{ route('bel.index') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                    <!-- Day Filter -->
                    <div class="w-full md:w-auto">
                        <label for="hari" class="block text-sm font-medium text-gray-700 mb-1">Filter Hari</label>
                        <select name="hari" id="hari" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
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
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10 text-sm">
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
                    <button onclick="showRingModal()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 shadow hover:shadow-md text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Bunyikan Bell
                    </button>

                    <!-- Sync Button -->
                    <button onclick="syncSchedules()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 shadow hover:shadow-md text-sm">
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
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Audio</th>
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
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" />
                                </svg>
                                <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    File {{ $schedule->file_number }}
                                </span>
                            </div>
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
                                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </label>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('bel.edit', $schedule->id) }}" class="text-blue-600 hover:text-blue-900" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </a>
                                <form action="{{ route('bel.delete', $schedule->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="text-red-600 hover:text-red-900 delete-btn" title="Delete">
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
                                <a href="{{ route('bel.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 shadow-md hover:shadow-lg">
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

<script>
// Enhanced JavaScript with better organization and error handling

// ======================
// UTILITY FUNCTIONS
// ======================

function showLoading(title = 'Process...') {
    Swal.fire({
        title: title,
        html: 'Harap Tunggu Sementara Kami Memproses Permintaan Anda...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function showToast(icon, title) {
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
    
    Toast.fire({ icon, title });
}

// ======================
// UI FUNCTIONS
// ======================

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

// ======================
// SCHEDULE MANAGEMENT
// ======================

// Delete confirmation
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Hapus Jadwal?',
                text: "Ini Tidak Dapat Di Batalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya ',
                cancelButtonText: 'TIdak',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText);
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error}`
                        );
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    showToast('success', result.value.message || 'Jadwal Bel Berhasil Di Hapus');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            });
        });
    });
});

// Bulk delete confirmation
function confirmDeleteAll() {
    Swal.fire({
        title: 'Hapus Semua Jadwwal?',
        text: "Anda Akan Mengahpus Semua Jadwal!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus Semua',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch("{{ route('bel.delete-all') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText);
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(
                    `Request failed: ${error}`
                );
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            showToast('success', result.value.message || 'Semua Jadwal Berhasil Di Hapus');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    });
}

// Activate all schedules
function activateAll() {
    showLoading('Aktifkan Semua Jadwal...');
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
            showToast('success', data.message || 'Semua Jadwal Berhasil Di Aktifkan');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('error', data.message || 'Jadwal Gagal Di Aktifkan');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

// Deactivate all schedules
function deactivateAll() {
    showLoading('Menonaktifkan Semua Jadwal...');
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
            showToast('success', data.message || 'Semua Jadwal Berhasil Di Nonaktifkan');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('error', data.message || 'Jadwal Gagal Di Nonaktifkan');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

// ======================
// BELL CONTROL FUNCTIONS
// ======================

// Ring bell modal
function showRingModal() {
    Swal.fire({
        title: 'Manual Bell Ring',
        html: `
            <div class="text-left">
                <div class="mb-4">
                    <label for="swal-file-number" class="block text-sm font-medium text-gray-700 mb-1">Sound File</label>
                    <select id="swal-file-number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                        <option value="">Select Sound File</option>
                        @for($i = 1; $i <= 50; $i++)
                            <option value="{{ sprintf('%04d', $i) }}">File {{ sprintf('%04d', $i) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="mb-4">
                    <label for="swal-volume" class="block text-sm font-medium text-gray-700 mb-1">Volume (1-30)</label>
                    <input type="number" id="swal-volume" min="1" max="30" value="20" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ring Bell',
        cancelButtonText: 'Cancel',
        focusConfirm: false,
        preConfirm: () => {
            const fileNumber = document.getElementById('swal-file-number').value;
            const volume = document.getElementById('swal-volume').value;
            
            if (!fileNumber) {
                Swal.showValidationMessage('Pilih File');
                return false;
            }
            if (volume < 1 || volume > 30) {
                Swal.showValidationMessage('Volume Antar 1 - 30');
                return false;
            }
            
            return { file_number: fileNumber, volume: volume };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            ringBell(result.value.file_number, result.value.volume);
        }
    });
}

// Ring bell function
function ringBell(fileNumber, volume = 20) {
    showLoading('Ringing bell...');
    fetch("{{ route('api.bel.ring') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            file_number: fileNumber,
            volume: volume
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            showToast('success', 'Bel Berhasil Di Bunyikan');
        } else {
            showToast('error', data.message || 'Bel Gagal Di Bunyikan');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

// Sync schedules function
function syncSchedules() {
    showLoading('Sinkronasi Jadwal Bel Dengan Device...');
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
            showToast('success', data.message || 'Sinkronisasi Jadwal Berhasil');
        } else {
            showToast('error', data.message || 'Sinkronisasi Jadwal Gagal');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

// Export schedules function
function exportSchedules() {
    showLoading('Preparing export...');
    
    // Get current filter parameters
    const params = new URLSearchParams({
        hari: document.getElementById('hari').value || '',
        search: document.getElementById('search').value || '',
        export: 'true'
    });
    
    window.location.href = `{{ route('bel.index') }}?${params.toString()}`;
    Swal.close();
}

// ======================
// DEVICE STATUS FUNCTIONS
// ======================

// Get live status
function getLiveStatus() {
    fetch("{{ route('api.bel.status') }}", {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateDeviceStatus(data.data);
        }
    })
    .catch(error => console.error('Status update error:', error));
}

// Update device status
function updateDeviceStatus(data) {
    // Update MQTT Status
    if (data.mqtt_status !== undefined) {
        const mqttCard = document.querySelector('#mqttCard');
        const mqttStatusText = document.querySelector('#mqttStatusText');
        const mqttIconBg = document.querySelector('#mqttIconBg');
        const mqttIconSvg = document.querySelector('#mqttIconSvg');
        
        const isConnected = data.mqtt_status === true || data.mqtt_status === 'Connected';
        
        mqttCard.classList.toggle('border-green-500', isConnected);
        mqttCard.classList.toggle('border-red-500', !isConnected);
        
        mqttIconBg.classList.toggle('bg-green-100', isConnected);
        mqttIconBg.classList.toggle('bg-red-100', !isConnected);
        
        mqttIconSvg.classList.toggle('text-green-600', isConnected);
        mqttIconSvg.classList.toggle('text-red-600', !isConnected);
        
        mqttStatusText.textContent = isConnected ? 'Connected' : 'Disconnected';
        mqttStatusText.classList.toggle('text-green-600', isConnected);
        mqttStatusText.classList.toggle('text-red-600', !isConnected);
        
        if (data.mqtt_last_update) {
            document.querySelector('#mqttStatusDetails').textContent = 
                `Last updated: ${new Date(data.mqtt_last_update).toLocaleTimeString()}`;
        }
    }

    // Update RTC Status
    if (data.rtc !== undefined) {
        const rtcCard = document.querySelector('#rtcCard');
        const rtcIcon = document.querySelector('#rtcIcon');
        const rtcStatusText = document.querySelector('#rtcStatusText');
        const rtcTimeText = document.querySelector('#rtcTimeText');
        
        const isConnected = data.rtc === true;
        
        rtcCard.classList.toggle('border-green-500', isConnected);
        rtcCard.classList.toggle('border-red-500', !isConnected);
        
        rtcIcon.classList.toggle('bg-green-100', isConnected);
        rtcIcon.classList.toggle('bg-red-100', !isConnected);
        
        rtcIcon.querySelector('svg').classList.toggle('text-green-600', isConnected);
        rtcIcon.querySelector('svg').classList.toggle('text-red-600', !isConnected);
        
        rtcStatusText.textContent = isConnected ? 'Connected' : 'Disconnected';
        rtcStatusText.classList.toggle('text-green-600', isConnected);
        rtcStatusText.classList.toggle('text-red-600', !isConnected);
        
        if (data.rtc_time) {
            rtcTimeText.textContent = new Date(data.rtc_time).toLocaleString();
        }
    }

    // Update DFPlayer Status
    if (data.dfplayer !== undefined) {
        const dfplayerCard = document.querySelector('#dfplayerCard');
        const dfplayerIcon = document.querySelector('#dfplayerIcon');
        const dfplayerStatusText = document.querySelector('#dfplayerStatusText');
        
        const isConnected = data.dfplayer === true;
        
        dfplayerCard.classList.toggle('border-green-500', isConnected);
        dfplayerCard.classList.toggle('border-red-500', !isConnected);
        
        dfplayerIcon.classList.toggle('bg-green-100', isConnected);
        dfplayerIcon.classList.toggle('bg-red-100', !isConnected);
        
        dfplayerIcon.querySelector('svg').classList.toggle('text-green-600', isConnected);
        dfplayerIcon.querySelector('svg').classList.toggle('text-red-600', !isConnected);
        
        dfplayerStatusText.textContent = isConnected ? 'Connected' : 'Disconnected';
        dfplayerStatusText.classList.toggle('text-green-600', isConnected);
        dfplayerStatusText.classList.toggle('text-red-600', !isConnected);
        
        if (data.dfplayer_files) {
            document.querySelector('#dfplayerDetails').textContent = 
                `${data.dfplayer_files} sound files available`;
        }
    }
}

// ======================
// NEXT SCHEDULE COUNTDOWN
// ======================

function updateNextSchedule() {
    fetch("{{ route('api.bel.next-schedule') }}")
    .then(response => {
        if (!response.ok) throw new Error('Gagal Memuat Jadwal');
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
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const currentDayIndex = now.getDay();
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
                        <span class="text-blue-600 font-medium">${h.toString().padStart(2, '0')}</span>h 
                        <span class="text-blue-600 font-medium">${m.toString().padStart(2, '0')}</span>m 
                        <span class="text-blue-600 font-medium">${s.toString().padStart(2, '0')}</span>s
                    `;
                } else {
                    countdownEl.innerHTML = '<span class="text-green-600 font-bold">IN PROGRESS</span>';
                    clearInterval(window.countdownInterval);
                    setTimeout(updateNextSchedule, 5000);
                }
            };
            
            // Clear previous interval and start new one
            if (window.countdownInterval) clearInterval(window.countdownInterval);
            updateCountdown();
            window.countdownInterval = setInterval(updateCountdown, 1000);
            
        } else {
            countdownEl.textContent = 'Tidak Ada Jadwal Aktif';
            timeEl.textContent = '';
            if (window.countdownInterval) clearInterval(window.countdownInterval);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('nextScheduleCountdown').textContent = 'Error loading Jadwal';
        document.getElementById('nextScheduleTime').textContent = '';
    });
}

// ======================
// INITIALIZATION
// ======================

document.addEventListener('DOMContentLoaded', function() {
    updateClock();
    updateNextSchedule();
    getLiveStatus();
    
    // Refresh every minute to stay accurate
    setInterval(updateNextSchedule, 60000);
    setInterval(getLiveStatus, 30000); // Update status every 30 seconds
    
    // Add animation to status cards on hover
    document.querySelectorAll('#mqttCard, #rtcCard, #dfplayerCard').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.querySelector('svg').classList.add('animate-pulse');
        });
        card.addEventListener('mouseleave', () => {
            card.querySelector('svg').classList.remove('animate-pulse');
        });
    });
});

</script>
@endsection