@extends('layouts.dashboard')

@section('title', 'Sistem Pengumuman Digital')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Sistem Pengumuman Digital</h1>
                    <p class="text-gray-600 mt-1">Kontrol Terpusat untuk Ruangan dan Pengumuman</p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-4">
                    <div class="flex items-center bg-white px-4 py-2 rounded-full shadow-sm border border-gray-200">
                        <div class="w-3 h-3 rounded-full mr-2 {{ $mqttStatus === 'Connected' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                        <span class="text-sm font-medium">MQTT: {{ $mqttStatus }}</span>
                    </div>
                    <button onclick="window.location.reload()" class="p-2 text-gray-500 hover:text-blue-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Mode Selection Card -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button id="reguler-tab" class="tab-button active" data-tab="reguler">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                        Kontrol Relay
                    </button>
                    <button id="tts-tab" class="tab-button" data-tab="tts">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                        </svg>
                        Pengumuman Suara
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Reguler Tab Content -->
                <div id="reguler-content" class="tab-content active">
                    <form id="reguler-form" action="{{ route('announcement.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mode" value="reguler">

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Ruangan Selection -->
                            <div class="lg:col-span-2">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-lg font-semibold text-gray-800">Pilih Ruangan</h2>
                                    <button type="button" id="selectAllBtn" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Pilih Semua
                                    </button>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto p-2">
                                        @foreach($ruangans as $ruangan)
                                        <div class="flex items-center p-3 hover:bg-gray-100 rounded-lg transition">
                                            <input id="ruangan-{{ $ruangan->id }}" name="ruangans[]" type="checkbox" 
                                                value="{{ $ruangan->id }}" 
                                                class="room-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="ruangan-{{ $ruangan->id }}" class="ml-3 flex-1">
                                                <div class="flex justify-between items-center">
                                                    <span class="block text-gray-800 font-medium">{{ $ruangan->nama_ruangan }}</span>
                                                    <span class="relay-status text-xs px-2 py-1 rounded-full 
                                                        {{ $ruangan->relay_state === 'ON' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                        {{ $ruangan->relay_state ?? 'OFF' }}
                                                    </span>
                                                </div>
                                                <span class="block text-xs text-gray-500 mt-1">
                                                    {{ $ruangan->kelas->nama_kelas ?? '-' }} • 
                                                    {{ $ruangan->jurusan->nama_jurusan ?? '-' }}
                                                </span>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @error('ruangans')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Relay Control Panel -->
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">Kontrol Relay</h2>
                                <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="space-y-4">
                                        <div class="flex items-center p-3 rounded-lg bg-blue-50 border border-blue-100">
                                            <input id="relay-on" name="relay_action" type="radio" value="ON" checked
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                            <label for="relay-on" class="ml-3 flex items-center">
                                                <span class="w-3 h-3 rounded-full bg-green-500 mr-3"></span>
                                                <span class="text-gray-700 font-medium">Aktifkan Relay</span>
                                            </label>
                                        </div>
                                        <div class="flex items-center p-3 rounded-lg bg-red-50 border border-red-100">
                                            <input id="relay-off" name="relay_action" type="radio" value="OFF"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                            <label for="relay-off" class="ml-3 flex items-center">
                                                <span class="w-3 h-3 rounded-full bg-red-500 mr-3"></span>
                                                <span class="text-gray-700 font-medium">Nonaktifkan Relay</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mt-6 pt-5 border-t border-gray-200">
                                        <button type="submit" 
                                            class="w-full flex justify-center items-center px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                            </svg>
                                            Eksekusi Perintah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TTS Tab Content -->
                <div id="tts-content" class="tab-content hidden">
                    <form id="tts-form" action="{{ route('announcement.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mode" value="tts">

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">Buat Pengumuman Suara</h2>
                                
                                <!-- TTS Editor -->
                                <div class="mb-6">
                                    <label for="tts_text" class="block text-gray-700 font-medium mb-2">Teks Pengumuman</label>
                                    <div class="relative">
                                        <textarea id="tts_text" name="tts_text" rows="6"
                                            class="w-full px-4 py-3 text-gray-700 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                            placeholder="Ketikkan teks pengumuman di sini..."></textarea>
                                        <div class="absolute bottom-3 right-3 text-xs text-gray-400" id="charCount">0/1000 karakter</div>
                                    </div>
                                    @error('tts_text')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Voice Settings -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="tts_voice" class="block text-gray-700 font-medium mb-2">Jenis Suara</label>
                                        <div class="relative">
                                            <select id="tts_voice" name="tts_voice"
                                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg shadow-sm">
                                                <option value="id-id">Bahasa Indonesia</option>
                                                <option value="en-us">English (US)</option>
                                                <option value="en-gb">English (UK)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="tts_speed" class="block text-gray-700 font-medium mb-2">Kecepatan Bicara</label>
                                        <div class="px-2">
                                            <input type="range" id="tts_speed" name="tts_speed" min="-10" max="10" value="0"
                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                            <div class="flex justify-between text-xs text-gray-500 mt-1 px-1">
                                                <span>Lambat</span>
                                                <span>Normal</span>
                                                <span>Cepat</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Preview Section -->
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <div class="flex justify-between items-center mb-3">
                                        <h3 class="text-sm font-medium text-gray-700">Preview Suara</h3>
                                        <button type="button" id="previewBtn"
                                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                            </svg>
                                            Generate Preview
                                        </button>
                                    </div>
                                    <div id="previewContainer" class="hidden">
                                        <audio id="previewAudio" controls class="w-full mt-2"></audio>
                                        <div id="previewLoading" class="mt-3 text-center py-4 hidden">
                                            <svg class="animate-spin mx-auto h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <p class="mt-2 text-sm text-gray-500">Sedang memproses suara...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ruangan Selection -->
                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-lg font-semibold text-gray-800">Ruangan Tujuan</h2>
                                    <button type="button" id="selectAllTtsBtn" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Pilih Semua
                                    </button>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 max-h-96 overflow-y-auto">
                                    @foreach($ruangans as $ruangan)
                                    <div class="flex items-start mb-3">
                                        <div class="flex items-center h-5 mt-1">
                                            <input id="tts-ruangan-{{ $ruangan->id }}" name="ruangans[]" type="checkbox" 
                                                value="{{ $ruangan->id }}" 
                                                class="tts-room-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        </div>
                                        <label for="tts-ruangan-{{ $ruangan->id }}" class="ml-3">
                                            <span class="block text-gray-800 font-medium">{{ $ruangan->nama_ruangan }}</span>
                                            <span class="block text-xs text-gray-500">
                                                {{ $ruangan->kelas->nama_kelas ?? '-' }} • 
                                                {{ $ruangan->jurusan->nama_jurusan ?? '-' }}
                                            </span>
                                        </label>
                                    </div>
                                    @endforeach
                                    @error('ruangans')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror

                                    <div class="mt-6 pt-5 border-t border-gray-200">
                                        <button type="submit" 
                                            class="w-full flex justify-center items-center px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-medium rounded-lg hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all shadow-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                            </svg>
                                            Kirim Pengumuman
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Announcements -->
        <div class="mt-8 bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-800">Riwayat Pengumuman Terakhir</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konten</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($announcements as $announcement)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $announcement->sent_at->format('d M Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $announcement->sent_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full 
                                    {{ $announcement->mode === 'reguler' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $announcement->mode === 'reguler' ? 'Kontrol Relay' : 'Pengumuman Suara' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($announcement->mode === 'reguler')
                                        {{ $announcement->message }}
                                        <span class="text-xs font-normal ml-2 {{ $announcement->relay_state === 'ON' ? 'text-green-600' : 'text-red-600' }}">
                                            (Relay: {{ $announcement->relay_state }})
                                        </span>
                                    @else
                                        {{ Str::limit($announcement->message, 50) }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Ruangan: {{ $announcement->ruangans->pluck('nama_ruangan')->implode(', ') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-medium rounded-full 
                                        {{ $announcement->status === 'delivered' ? 'bg-green-100 text-green-800' : ($announcement->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($announcement->status) }}
                                    </span>
                                    @if($announcement->error_message)
                                    <button onclick="showErrorModal('{{ $announcement->error_message }}')" class="ml-2 text-gray-400 hover:text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                Belum ada riwayat pengumuman
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Error Details</h3>
                        <div class="mt-2">
                            <p id="errorModalContent" class="text-sm text-gray-500"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeErrorModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab Switching
    const tabs = document.querySelectorAll('.tab-button');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Update tab buttons
            tabs.forEach(t => t.classList.remove('active', 'border-blue-500', 'text-blue-600'));
            tab.classList.add('active', 'border-blue-500', 'text-blue-600');
            
            // Update tab contents
            const tabId = tab.getAttribute('data-tab');
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            document.getElementById(`${tabId}-content`).classList.remove('hidden');
            document.getElementById(`${tabId}-content`).classList.add('active');
        });
    });

    // Select All Rooms - Reguler
    document.getElementById('selectAllBtn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.room-checkbox');
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });
        
        this.innerHTML = allChecked ? 
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>Pilih Semua' : 
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>Batal Pilih';
    });

    // Select All Rooms - TTS
    document.getElementById('selectAllTtsBtn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.tts-room-checkbox');
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });
        
        this.innerHTML = allChecked ? 
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>Pilih Semua' : 
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>Batal Pilih';
    });

    // Character Count for TTS
    document.getElementById('tts_text').addEventListener('input', function() {
        const count = this.value.length;
        document.getElementById('charCount').textContent = `${count}/1000 karakter`;
    });

    // TTS Preview
    document.getElementById('previewBtn').addEventListener('click', function() {
        const text = document.getElementById('tts_text').value;
        const voice = document.getElementById('tts_voice').value;
        const speed = document.getElementById('tts_speed').value;
        const previewContainer = document.getElementById('previewContainer');
        const previewAudio = document.getElementById('previewAudio');
        const previewLoading = document.getElementById('previewLoading');

        if (!text) {
            alert('Masukkan teks terlebih dahulu');
            return;
        }

        // Show loading
        previewContainer.classList.remove('hidden');
        previewAudio.classList.add('hidden');
        previewLoading.classList.remove('hidden');

        fetch("{{ route('announcement.tts-preview') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                text: text,
                voice: voice,
                speed: speed
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                previewContainer.classList.add('hidden');
            } else {
                previewAudio.src = data.audio_url;
                previewLoading.classList.add('hidden');
                previewAudio.classList.remove('hidden');
                previewAudio.play();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal membuat preview suara');
            previewContainer.classList.add('hidden');
        });
    });

    // Initialize first tab
    document.querySelector('.tab-button').click();
});

// Error Modal Functions
function showErrorModal(message) {
    document.getElementById('errorModalContent').textContent = message;
    document.getElementById('errorModal').classList.remove('hidden');
}

function closeErrorModal() {
    document.getElementById('errorModal').classList.add('hidden');
}
</script>

@endsection