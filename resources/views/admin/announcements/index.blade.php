@extends('layouts.dashboard')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="flex items-center space-x-3">
            <div class="p-3 rounded-xl bg-blue-100/80 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Sistem Pengumuman</h1>
                <p class="text-sm text-gray-500">Kelola pengumuman untuk seluruh ruangan</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <div id="mqtt-status" class="flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                <span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span>
                <span>Memeriksa koneksi...</span>
            </div>
            <a href="{{ route('announcements.history') }}" class="px-3 py-1 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Riwayat
            </a>
        </div>
    </div>

    <!-- Notification System -->
    <div id="notification-container">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm animate-fade-in">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm animate-fade-in">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in">
        <!-- Announcement Form Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Buat Pengumuman Baru
                    </h2>
                </div>
                <div class="p-6">
                    <form id="announcement-form">
                        @csrf

                        <!-- Announcement Type -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Mode Pengumuman</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="tts" class="peer absolute opacity-0" checked>
                                    <div class="p-4 border-2 border-gray-200 rounded-xl transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-1 peer-checked:ring-blue-200 hover:border-blue-300">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-5 w-5 rounded-full border-2 border-gray-300 peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:border-4 transition-all duration-200 mr-3"></div>
                                            <div>
                                                <h3 class="font-medium text-gray-800">Text-to-Speech</h3>
                                                <p class="text-sm text-gray-500">Sistem akan membacakan teks otomatis</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="manual" class="peer absolute opacity-0">
                                    <div class="p-4 border-2 border-gray-200 rounded-xl transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-1 peer-checked:ring-blue-200 hover:border-blue-300">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-5 w-5 rounded-full border-2 border-gray-300 peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:border-4 transition-all duration-200 mr-3"></div>
                                            <div>
                                                <h3 class="font-medium text-gray-800">Pengumuman Manual</h3>
                                                <p class="text-sm text-gray-500">Aktifkan relay audio di ruangan terpilih</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Target Rooms -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-3">
                                <label class="block text-sm font-medium text-gray-700">Ruangan Tujuan</label>
                                <button type="button" id="select-all" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">Pilih Semua</button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                @foreach($ruangans as $ruangan)
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="ruangans[]" value="{{ $ruangan }}" class="peer absolute opacity-0">
                                    <div class="px-3 py-2 border border-gray-200 rounded-lg text-center transition-all duration-150 peer-checked:bg-blue-50 peer-checked:border-blue-300 peer-checked:text-blue-700 peer-checked:font-medium hover:bg-gray-50">
                                        {{ $ruangan }}
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- TTS Content -->
                        <div id="tts-fields" class="mb-6 transition-all duration-300">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Isi Pengumuman</label>
                            <div class="relative">
                                <textarea name="content" rows="4" class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-150 ease-in-out" placeholder="Ketikkan teks pengumuman..."></textarea>
                                <div class="absolute bottom-3 right-3 flex items-center text-xs text-gray-500 bg-white px-2 rounded-full transition-opacity duration-200">
                                    <span id="char-count">0</span>/500
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button type="submit" id="submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-sm font-medium text-white hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-300 flex items-center justify-center transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Kirim Pengumuman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="space-y-6">
            <!-- System Status Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Status Sistem
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Active Announcement Indicator -->
                    <div id="active-indicator" class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <div class="h-3 w-3 rounded-full bg-gray-300"></div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-700">Tidak ada pengumuman aktif</p>
                            <p class="text-xs text-gray-500">Siap menerima pengumuman baru</p>
                        </div>
                    </div>

                    <!-- Active Rooms Indicator -->
                    <div id="active-rooms-indicator" class="hidden">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-gray-700">Ruangan Aktif</p>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                <span id="active-count">0</span> aktif
                            </span>
                        </div>
                        <div id="active-rooms-badges" class="flex flex-wrap gap-2">
                            <!-- Badge ruangan akan muncul di sini -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Control Card -->
            <div id="stop-manual-section" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Kontrol Ruangan
                    </h2>
                </div>
                <div class="p-6">
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-amber-700">
                                    <span class="font-medium">Perhatian!</span> Relay audio sedang aktif di ruangan terpilih.
                                </p>
                            </div>
                        </div>
                    </div>
                    <button id="stop-routing-btn" class="w-full px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl text-sm font-medium text-white hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 shadow-sm transition-all duration-300 flex items-center justify-center transform hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Matikan Semua Ruangan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.animate-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Base URL for API endpoints
    const baseUrl = '/admin/pengumuman';

    // Initialize UI state
    function initUI() {
        // Check MQTT status immediately
        updateMqttStatus();
        
        // Check for active announcements
        checkActiveAnnouncement();
        
        // Trigger change event to set initial view
        $('input[name="type"]').trigger('change');
        
        // Load active announcements
        loadActiveAnnouncements();
    }

    // MQTT Status Indicator
    function updateMqttStatus() {
        $.ajax({
            url: `${baseUrl}/mqtt-status`,
            method: 'GET',
            beforeSend: function() {
                $('#mqtt-status span:first').removeClass('bg-green-500 bg-red-500').addClass('bg-gray-400');
                $('#mqtt-status span:last').text('Memeriksa...');
            },
            success: function(response) {
                const statusEl = $('#mqtt-status');
                const statusDot = statusEl.find('span:first');
                const statusText = statusEl.find('span:last');
                
                statusDot.removeClass('bg-gray-400');
                
                if (response.connected) {
                    statusDot.addClass('bg-green-500');
                    statusText.text('Terhubung');
                } else {
                    statusDot.addClass('bg-red-500');
                    statusText.text('Terputus');
                }
            },
            error: function() {
                $('#mqtt-status span:first').removeClass('bg-green-500 bg-red-500').addClass('bg-gray-400');
                $('#mqtt-status span:last').text('Gagal memeriksa');
            }
        });
    }

    // Character counter
    $('textarea[name="content"]').on('input', function() {
        const count = $(this).val().length;
        $('#char-count').text(count);
        $('#char-count').toggleClass('text-red-500', count > 500);
    }).trigger('input');

    // Toggle fields based on announcement type
    $('input[name="type"]').change(function() {
        const isManual = $(this).val() === 'manual';
        $('#tts-fields').toggleClass('hidden', isManual);
        $('#submit-btn').html(`
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                ${isManual ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />' : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />'}
            </svg>
            ${isManual ? 'Hidupkan Ruangan' : 'Kirim Pengumuman'}
        `);
    });

    // Select all rooms
    $('#select-all').click(function() {
        const checkboxes = $('input[name="ruangans[]"]');
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        checkboxes.prop('checked', !allChecked).trigger('change');
        $(this).text(allChecked ? 'Pilih Semua' : 'Batalkan Semua');
    });

    // Form submission
    $('#announcement-form').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serializeArray();
        const isManual = $('input[name="type"]:checked').val() === 'manual';
        const selectedRuangans = $('input[name="ruangans[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedRuangans.length === 0) {
            showAlert('error', 'Peringatan', 'Silakan pilih minimal satu ruangan');
            return;
        }

        const confirmMessage = isManual 
            ? `Anda akan menghidupkan relay audio di ${selectedRuangans.length} ruangan`
            : `Anda akan mengirim pengumuman TTS ke ${selectedRuangans.length} ruangan`;

        showConfirmation(
            'Konfirmasi',
            confirmMessage,
            '',
            function() {
                const submitBtn = $('#submit-btn');
                const buttonText = isManual ? 'Menghidupkan...' : 'Mengirim...';
                submitBtn.prop('disabled', true).html(`
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    ${buttonText}
                `);

                $.ajax({
                    url: `${baseUrl}/send`,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        showAlert('success', 'Berhasil!', response.message);
                        
                        if (isManual) {
                            $('#stop-manual-section').removeClass('hidden');
                            updateActiveRooms(selectedRuangans);
                        }
                        
                        loadActiveAnnouncements();
                    },
                    error: handleAjaxError,
                    complete: function() {
                        submitBtn.prop('disabled', false).html(`
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                ${isManual ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />' : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />'}
                            </svg>
                            ${isManual ? 'Hidupkan Ruangan' : 'Kirim Pengumuman'}
                        `);
                    }
                });
            }
        );
    });

    // Stop routing button
    $('#stop-routing-btn').click(function(e) {
        e.preventDefault();
        showConfirmation(
            'Konfirmasi',
            'Anda yakin ingin mematikan semua relay ruangan?',
            '',
            function() {
                const stopBtn = $('#stop-routing-btn');
                stopBtn.prop('disabled', true).html(`
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                `);
                $.post(`${baseUrl}/stop-manual`, function(response) {
                    showAlert('success', 'Berhasil!', response.message);
                    $('#stop-manual-section').addClass('hidden');
                    loadActiveAnnouncements();
                }).fail(handleAjaxError).always(function() {
                    stopBtn.prop('disabled', false).html(`
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Matikan Semua Ruangan
                    `);
                });
            }
        );
    });

    // Check if there's an active manual announcement
    function checkActiveAnnouncement() {
        $.get(`${baseUrl}/check-active`, function(response) {
            if (response.active && response.type === 'manual') {
                $('input[name="type"][value="manual"]').prop('checked', true).trigger('change');
                $('#stop-manual-section').removeClass('hidden');
            }
        });
    }

    // Load active announcements
    function loadActiveAnnouncements() {
        $.get(`${baseUrl}/active-announcements`, function(response) {
            updateActiveStatus(response);
        });
    }

    // Update active status UI
    function updateActiveStatus(activeAnnouncements) {
        const activeIndicator = $('#active-indicator');
        const activeRoomsIndicator = $('#active-rooms-indicator');
        const activeRoomsBadges = $('#active-rooms-badges');
        
        if (activeAnnouncements.length === 0) {
            activeIndicator.html(`
                <div class="flex-shrink-0 mt-1">
                    <div class="h-3 w-3 rounded-full bg-gray-300"></div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-700">Tidak ada pengumuman aktif</p>
                    <p class="text-xs text-gray-500">Siap menerima pengumuman baru</p>
                </div>
            `);
            activeRoomsIndicator.addClass('hidden');
            return;
        }

        // Count unique active rooms
        const allRooms = [];
        activeAnnouncements.forEach(announcement => {
            allRooms.push(...announcement.target_ruangans);
        });
        const uniqueRooms = [...new Set(allRooms)];

        // Update main indicator
        activeIndicator.html(`
            <div class="flex-shrink-0 mt-1">
                <div class="h-3 w-3 rounded-full bg-green-500 animate-pulse"></div>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-700">${activeAnnouncements.length} pengumuman aktif</p>
                <p class="text-xs text-gray-500">${uniqueRooms.length} ruangan terpengaruh</p>
            </div>
        `);

        // Update active rooms badges
        activeRoomsIndicator.removeClass('hidden');
        $('#active-count').text(uniqueRooms.length);
        
        let badgesHtml = '';
        uniqueRooms.forEach(room => {
            badgesHtml += `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ${room}
                </span>
            `;
        });
        activeRoomsBadges.html(badgesHtml);
    }

    // Update active rooms display
    function updateActiveRooms(ruangans) {
        const activeRoomsIndicator = $('#active-rooms-indicator');
        const activeRoomsBadges = $('#active-rooms-badges');
        
        activeRoomsIndicator.removeClass('hidden');
        $('#active-count').text(ruangans.length);
        
        let badgesHtml = '';
        ruangans.forEach(ruangan => {
            badgesHtml += `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ${ruangan}
                </span>
            `;
        });
        activeRoomsBadges.html(badgesHtml);
    }

    // Helper function to show alert
    function showAlert(icon, title, text) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            timer: 2000,
            showConfirmButton: false
        });
    }

    // Helper function to show confirmation dialog
    function showConfirmation(title, text, html, confirmCallback) {
        Swal.fire({
            title: title,
            text: text,
            html: html,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md',
                cancelButton: 'px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                confirmCallback();
            }
        });
    }

    // Helper function to handle AJAX errors
    function handleAjaxError(xhr) {
        const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses permintaan';
        showAlert('error', 'Gagal!', errorMsg);
    }

    // Initialize the UI
    initUI();
    
    // Polling for updates every 30 seconds
    setInterval(function() {
        updateMqttStatus();
        loadActiveAnnouncements();
    }, 30000);
});
</script>
@endsection