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
                                                <h3 class="font-medium text-gray-800">Mikrofon Manual</h3>
                                                <p class="text-sm text-gray-500">Gunakan mikrofon langsung</p>
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
                                @foreach($rooms as $room)
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="rooms[]" value="{{ $room }}" class="peer absolute opacity-0">
                                    <div class="px-3 py-2 border border-gray-200 rounded-lg text-center transition-all duration-150 peer-checked:bg-blue-50 peer-checked:border-blue-300 peer-checked:text-blue-700 peer-checked:font-medium hover:bg-gray-50">
                                        {{ $room }}
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

                        <!-- Manual Mic Duration -->
                        <div id="manual-fields" class="hidden mb-6 transition-all duration-300">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (detik)</label>
                            <div class="flex items-center space-x-2">
                                <input type="range" name="duration" min="5" max="300" value="60" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                <span id="duration-value" class="text-sm font-medium text-gray-700 w-12 text-center">60</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Durasi: 5-300 detik (5 menit)</p>
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
            <!-- Audio Routing Control Card -->
            <div id="stop-manual-section" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                        </svg>
                        Kontrol Routing Audio
                    </h2>
                </div>
                <div class="p-6 flex-grow">
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg mb-4 animate-pulse">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-amber-700">
                                    <span class="font-medium">Perhatian!</span> Mikrofon aktif sedang di-routing ke speaker ruangan.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg mb-4">
                        <div class="flex-shrink-0 p-2 bg-blue-100 rounded-lg text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Status Mikrofon</p>
                            <p class="text-sm text-gray-500">Sedang aktif dan terhubung</p>
                        </div>
                    </div>
                    <div id="timer-display" class="hidden text-center mb-4">
                        <div class="inline-flex items-center justify-center">
                            <div class="relative">
                                <svg class="w-16 h-16" viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="16" fill="none" class="stroke-gray-200" stroke-width="2"></circle>
                                    <circle cx="18" cy="18" r="16" fill="none" class="stroke-blue-500" stroke-width="2" stroke-dasharray="100" stroke-dashoffset="0" id="countdown-circle"></circle>
                                </svg>
                                <span id="countdown" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-lg font-bold text-gray-800">60</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Sisa waktu pengumuman</p>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <button id="stop-routing-btn" class="w-full px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl text-sm font-medium text-white hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 shadow-sm transition-all duration-300 flex items-center justify-center transform hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                        </svg>
                        Putuskan Routing Audio
                    </button>
                </div>
            </div>

            <!-- Active Announcements Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Pengumuman Aktif
                    </h2>
                </div>
                <div class="p-6">
                    <div id="active-announcements-container">
                        <p class="text-sm text-gray-500">Memuat pengumuman aktif...</p>
                    </div>
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

#countdown-circle {
    transition: stroke-dashoffset 1s linear;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Base URL for API endpoints
    const baseUrl = '/admin/pengumuman';

    // Initialize UI state
    function initUI() {
        // Set initial state for manual fields
        $('input[name="duration"]').val(60);
        $('#duration-value').text(60);
        
        // Check MQTT status immediately
        updateMqttStatus();
        
        // Check for active announcements
        checkActiveAnnouncement();
        
        // Trigger change event to set initial view
        $('input[name="type"]').trigger('change');
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

    // Update duration value display
    $('input[name="duration"]').on('input', function() {
        $('#duration-value').text($(this).val());
    });

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
        $('#manual-fields').toggleClass('hidden', !isManual);
        $('#stop-manual-section').toggleClass('hidden', !isManual);
    });

    // Select all rooms
    $('#select-all').click(function() {
        const checkboxes = $('input[name="rooms[]"]');
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        checkboxes.prop('checked', !allChecked).trigger('change');
        $(this).text(allChecked ? 'Pilih Semua' : 'Batalkan Semua');
    });

    // Form submission
    $('#announcement-form').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serializeArray();
        const isManual = $('input[name="type"]:checked').val() === 'manual';
        const duration = isManual ? parseInt($('input[name="duration"]').val()) : 0;
        const selectedRooms = $('input[name="rooms[]"]:checked').map(function() {
            return $(this).closest('label').text().trim();
        }).get();

        if (selectedRooms.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Peringatan',
                text: 'Silakan pilih minimal satu ruangan',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Pengiriman',
            html: `<div class="text-left">
                <p>Anda akan mengirim pengumuman <strong>${isManual ? 'manual' : 'TTS'}</strong> ke:</p>
                <ul class="list-disc pl-5 mt-2 mb-2 max-h-40 overflow-y-auto">
                    ${selectedRooms.map(room => `<li>${room}</li>`).join('')}
                </ul>
                ${isManual ? `<p>Durasi: <strong>${duration} detik</strong></p>` : ''}
            </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim Sekarang',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md',
                cancelButton: 'px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const submitBtn = $('#submit-btn');
                submitBtn.prop('disabled', true).html(`
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Mengirim...
                `);

                $.ajax({
                    url: `${baseUrl}/send`,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Pengumuman berhasil dikirim',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        if (isManual) {
                            startCountdown(duration);
                        }
                        
                        // Refresh active announcements
                        loadActiveAnnouncements();
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengirim pengumuman';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMsg,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(`
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Kirim Pengumuman
                        `);
                    }
                });
            }
        });
    });

    // Stop routing button
    $('#stop-routing-btn').click(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Anda yakin ingin memutus routing audio?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Putuskan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const stopBtn = $('#stop-routing-btn');
                stopBtn.prop('disabled', true).html(`
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                `);

                $.post(`${baseUrl}/stop-manual`, function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Routing audio berhasil diputus',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#timer-display').hide();
                    
                    // Refresh active announcements
                    loadActiveAnnouncements();
                }).fail(function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Gagal memutus routing audio';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMsg,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }).always(function() {
                    stopBtn.prop('disabled', false).html(`
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                        </svg>
                        Putuskan Routing Audio
                    `);
                });
            }
        });
    });

    // Countdown timer with circular progress
    function startCountdown(seconds) {
        $('#timer-display').show();
        let counter = seconds;
        const circle = $('#countdown-circle');
        const circumference = 2 * Math.PI * 16;
        const countdownElement = $('#countdown');
        
        // Initialize circle
        circle.css('stroke-dasharray', circumference);
        circle.css('stroke-dashoffset', 0);
        
        const interval = setInterval(() => {
            countdownElement.text(counter);
            
            // Update progress circle
            const offset = circumference - (counter / seconds) * circumference;
            circle.css('stroke-dashoffset', offset);
            
            counter--;
            
            if (counter < 0) {
                clearInterval(interval);
                $('#timer-display').hide();
            }
        }, 1000);
    }

    // Check if there's an active manual announcement
    function checkActiveAnnouncement() {
        $.get(`${baseUrl}/check-active`, function(response) {
            if (response.active && response.type === 'manual') {
                $('input[name="type"][value="manual"]').prop('checked', true).trigger('change');
                $('input[name="duration"]').val(response.duration);
                $('#duration-value').text(response.duration);
                startCountdown(response.remaining);
            }
        });
    }

    // Load active announcements
    function loadActiveAnnouncements() {
        $.get(`${baseUrl}/active-announcements`, function(response) {
            const container = $('#active-announcements-container');
            
            if (response.length === 0) {
                container.html('<p class="text-sm text-gray-500">Tidak ada pengumuman aktif</p>');
                return;
            }
            
            let html = '<div class="space-y-3">';
            
            response.forEach(announcement => {
                html += `
                    <div class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium text-gray-800">${announcement.room}</h3>
                                <p class="text-sm text-gray-500">${announcement.type === 'tts' ? 'TTS' : 'Manual'}</p>
                            </div>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            <p>Mulai: ${new Date(announcement.sent_at).toLocaleString()}</p>
                            ${announcement.type === 'manual' ? `<p>Durasi: ${announcement.duration} detik</p>` : ''}
                        </div>
                        <div class="mt-2">
                            <button class="text-red-600 hover:text-red-900 text-sm font-medium stop-announcement" data-id="${announcement.id}">
                                Hentikan
                            </button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.html(html);
        });
    }

    // Stop announcement button handler
    $(document).on('click', '.stop-announcement', function() {
        const announcementId = $(this).data('id');
        
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Anda yakin ingin menghentikan pengumuman ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hentikan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`${baseUrl}/stop-announcement`, {
                    id: announcementId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Pengumuman berhasil dihentikan',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Refresh active announcements
                    loadActiveAnnouncements();
                }).fail(function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Gagal menghentikan pengumuman';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMsg,
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        });
    });

    // Initialize the UI
    initUI();
    
    // Load initial data
    loadActiveAnnouncements();
});
</script>
@endsection