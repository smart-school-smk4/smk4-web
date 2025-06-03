@extends('layouts.dashboard')

@section('title', 'Pengumuman Sekolah')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <div class="flex items-center mb-2">
                <h1 class="text-3xl font-bold text-gray-800">Sistem Pengumuman Sekolah</h1>
                <span class="ml-3 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 flex items-center">
                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                    LIVE CONTROL
                </span>
            </div>
            <p class="text-gray-600">Kelola pengumuman manual dan TTS untuk seluruh ruangan sekolah</p>
            
            <div class="flex items-center mt-4 space-x-4">
                <div class="flex items-center">
                    <span class="relative flex h-3 w-3 mr-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $mqttStatus ? 'bg-green-400' : 'bg-red-400' }} opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $mqttStatus ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    </span>
                    <span class="text-sm font-medium {{ $mqttStatus ? 'text-green-700' : 'text-red-700' }}">
                        MQTT: {{ $mqttStatus ? 'Connected' : 'Disconnected' }}
                    </span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-door-open mr-2 text-blue-500"></i> 
                    <span class="font-medium">{{ $ruangans->count() }}</span> Ruangan Terdaftar
                </div>
            </div>
        </div>
        <a href="{{ route('admin.announcement.history') }}" 
           class="flex items-center px-5 py-2.5 bg-white border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition-all duration-200 shadow-sm hover:shadow-md">
            <i class="fas fa-history mr-2"></i> Riwayat Pengumuman
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-gray-50">
            <nav class="flex">
                <button id="manualTabBtn" class="px-6 py-4 font-medium text-sm border-b-2 border-blue-600 text-blue-600 focus:outline-none transition-colors flex items-center">
                    <i class="fas fa-microphone-alt mr-2"></i> Mode Manual
                </button>
                <button id="ttsTabBtn" class="px-6 py-4 font-medium text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none transition-colors flex items-center">
                    <i class="fas fa-robot mr-2"></i> Text-to-Speech
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Manual Mode Tab -->
            <div id="manualTab" class="tab-content">
                <form id="manualForm" action="{{ url('/api/announcements/relay/control') }}" method="POST">
                    @csrf
                    <input type="hidden" name="mode" value="manual">
                    <input type="hidden" id="actionType" name="action" value="activate">
                    
                    <div class="mb-6">
                        <div class="flex items-center mb-3">
                            <div class="p-2 rounded-lg bg-blue-100 text-blue-800 mr-3">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800">Pengumuman Manual</h3>
                                <p class="text-gray-600 text-sm">Aktifkan atau nonaktifkan Speaker untuk ruangan tertentu</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700">Pilih Ruangan <span class="text-red-500">*</span></label>
                            <button type="button" id="selectAllManual" class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors flex items-center">
                                <i class="fas fa-check-circle mr-1"></i> Pilih Semua
                            </button>
                        </div>

                        @php
                            // Urutkan ruangan berdasarkan angka dalam nama_ruangan
                            $sortedRuangans = collect($ruangans)->sortBy(function($ruangan) {
                                preg_match('/\d+/', $ruangan->nama_ruangan, $matches);
                                return (int) ($matches[0] ?? PHP_INT_MAX); // PHP_INT_MAX agar yang tanpa angka muncul paling akhir
                            });
                        @endphp
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($sortedRuangans as $ruangan)
                                <div class="relative flex items-start p-3 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors">
                                    <div class="flex items-center h-5 mt-1">
                                        <input id="manual-ruang-{{ $ruangan->nama_ruangan }}" 
                                            name="ruangans[]" 
                                            type="checkbox" 
                                            value="{{ $ruangan->nama_ruangan }}" 
                                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm flex-1">
                                        <div class="flex items-center justify-between">
                                            <label for="manual-ruang-{{ $ruangan->nama_ruangan }}" class="font-medium text-gray-700 flex items-center">
                                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500 mr-2"></span>
                                                Ruangan {{ str_pad($ruangan->nama_ruangan, 2, '0', STR_PAD_LEFT) }}
                                            </label>
                                            <span id="status-ruang-{{ $ruangan->nama_ruangan }}" 
                                                class="text-xs px-2 py-0.5 rounded-full 
                                                {{ $ruangan->relay_state === 'on' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $ruangan->relay_state === 'on' ? 'AKTIF' : 'NONAKTIF' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="ruanganError" class="mt-2 text-sm text-red-600 hidden flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> Pilih minimal satu ruangan
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" id="resetManual" class="inline-flex items-center px-4 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <i class="fas fa-redo mr-2"></i> Reset
                        </button>
                        <button type="submit" id="toggleRelayBtn" class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <i class="fas fa-broadcast-tower mr-2"></i> Aktifkan Relay
                        </button>
                    </div>
                </form>
            </div>

            <!-- TTS Mode Tab -->
            <div id="ttsTab" class="tab-content hidden">
                <form id="ttsForm" action="{{ url('/api/announcements/store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="mode" value="tts">
                    
                    <div class="mb-6">
                        <div class="flex items-center mb-3">
                            <div class="p-2 rounded-lg bg-green-100 text-green-800 mr-3">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800">Pengumuman Suara (TTS)</h3>
                                <p class="text-gray-600 text-sm">Masukkan teks yang akan diubah menjadi suara dan pilih ruangan tujuan</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Isi Pengumuman <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <textarea id="message" name="message" rows="5" 
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm placeholder-gray-400"
                                    placeholder="Contoh: Selamat pagi siswa-siswi, harap berkumpul di lapangan upacara untuk mengikuti kegiatan hari ini">{{ old('message') }}</textarea>
                            <div class="absolute bottom-3 right-3 bg-white px-2 py-1 rounded text-xs text-gray-500 border border-gray-200">
                                <span id="charCount">0</span>/500
                            </div>
                        </div>
                        <div id="messageError" class="mt-1 text-sm text-red-600 hidden flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> Isi pengumuman diperlukan
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-gray-700">Pilih Ruangan <span class="text-red-500">*</span></label>
                            <button type="button" id="selectAllTTS" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors flex items-center">
                                <i class="fas fa-check-circle mr-1"></i> Pilih Semua
                            </button>
                        </div>

                        @php
                            // Urutkan ruangan berdasarkan angka dalam nama_ruangan
                            $sortedRuangans = collect($ruangans)->sortBy(function($ruangan) {
                                preg_match('/\d+/', $ruangan->nama_ruangan, $matches);
                                return (int) ($matches[0] ?? PHP_INT_MAX); // PHP_INT_MAX agar yang tanpa angka muncul paling akhir
                            });
                        @endphp
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($sortedRuangans as $ruangan)
                                <div class="relative flex items-start p-3 rounded-lg border border-gray-200 hover:border-green-300 transition-colors">
                                    <div class="flex items-center h-5 mt-1">
                                        <input id="tts-ruang-{{ $ruangan->nama_ruangan }}" 
                                            name="ruangans[]" 
                                            type="checkbox" 
                                            value="{{ $ruangan->nama_ruangan }}" 
                                            class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="tts-ruang-{{ $ruangan->nama_ruangan }}" class="font-medium text-gray-700 flex items-center">
                                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500 mr-2"></span>
                                            Ruangan {{ str_pad($ruangan->nama_ruangan, 2, '0', STR_PAD_LEFT) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="ttsRuanganError" class="mt-2 text-sm text-red-600 hidden flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> Pilih minimal satu ruangan
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-100">
                        <button type="button" id="resetTTS" class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 px-6 rounded-lg text-sm font-medium transition-all duration-200 shadow-sm flex items-center">
                            <i class="fas fa-redo mr-2"></i> Reset Form
                        </button>
                        <button type="submit" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white py-2.5 px-6 rounded-lg text-sm font-medium transition-all duration-200 shadow-md hover:shadow-lg flex items-center">
                            <i class="fas fa-play mr-2"></i> Kirim Pengumuman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Fungsi untuk membuat selector aman dari karakter khusus
    function getSafeRoomSelector(roomName) {
        return roomName.replace(/[^a-zA-Z0-9-]/g, '-');
    }

    // Tab switching functionality
    function switchTab(activeTab, inactiveTab, activeBtn, inactiveBtn) {
        activeTab.classList.remove('hidden');
        inactiveTab.classList.add('hidden');
        activeBtn.classList.add('border-blue-600', 'text-blue-600');
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
        inactiveBtn.classList.remove('border-blue-600', 'text-blue-600');
        inactiveBtn.classList.add('border-transparent', 'text-gray-500');
    }

    $('#manualTabBtn').click(function() {
        switchTab(document.getElementById('manualTab'), document.getElementById('ttsTab'), 
                 document.getElementById('manualTabBtn'), document.getElementById('ttsTabBtn'));
    });

    $('#ttsTabBtn').click(function() {
        switchTab(document.getElementById('ttsTab'), document.getElementById('manualTab'), 
                 document.getElementById('ttsTabBtn'), document.getElementById('manualTabBtn'));
    });

    // Character counter for TTS message
    $('#message').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;
        $('#charCount').text(currentLength);
        
        if (currentLength > maxLength) {
            $(this).addClass('border-red-300');
            $('#charCount').addClass('text-red-600');
        } else {
            $(this).removeClass('border-red-300');
            $('#charCount').removeClass('text-red-600');
        }
    });

    // Select all checkboxes
    $('#selectAllManual').click(function() {
        const allChecked = $('#manualTab input[type="checkbox"]').length === $('#manualTab input[type="checkbox"]:checked').length;
        $('#manualTab input[type="checkbox"]').prop('checked', !allChecked);
        
        // Update button text
        $(this).html(`<i class="fas ${!allChecked ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i> ${!allChecked ? 'Pilih Semua' : 'Batal Pilih'}`);
    });

    $('#selectAllTTS').click(function() {
        const allChecked = $('#ttsTab input[type="checkbox"]').length === $('#ttsTab input[type="checkbox"]:checked').length;
        $('#ttsTab input[type="checkbox"]').prop('checked', !allChecked);
        
        // Update button text
        $(this).html(`<i class="fas ${!allChecked ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i> ${!allChecked ? 'Pilih Semua' : 'Batal Pilih'}`);
    });

    // Reset forms
    $('#resetManual').click(function() {
        $('#manualForm')[0].reset();
        $('#ruanganError').addClass('hidden');
        $('#selectAllManual').html('<i class="fas fa-check-circle mr-1"></i> Pilih Semua');
    });

    $('#resetTTS').click(function() {
        $('#ttsForm')[0].reset();
        $('#charCount').text('0');
        $('#messageError').addClass('hidden');
        $('#ttsRuanganError').addClass('hidden');
        $('#selectAllTTS').html('<i class="fas fa-check-circle mr-1"></i> Pilih Semua');
    });

    // Form validation and submission
    $('#manualForm').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const roomNames = formData.getAll('ruangans[]');
        
        // Reset error states
        $('#ruanganError').addClass('hidden');
        
        if (roomNames.length === 0) {
            $('#ruanganError').removeClass('hidden');
            Swal.fire({
                title: 'Peringatan',
                text: 'Pilih minimal satu ruangan untuk mengontrol relay',
                icon: 'warning',
                confirmButtonText: 'Mengerti',
                customClass: {
                    confirmButton: 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors'
                }
            });
            return;
        }
        
        // Toggle relay state
        isRelayActive = !isRelayActive;
        formData.set('action', isRelayActive ? 'activate' : 'deactivate');
        updateButtonState();
        
        // Show loading state
        const submitBtn = $('#toggleRelayBtn');
        const originalBtnText = submitBtn.html();
        submitBtn.html('<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...');
        submitBtn.prop('disabled', true);
        
        // Submit data
        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Update room status indicators
                roomNames.forEach(roomName => {
                    const safeRoomName = getSafeRoomSelector(roomName);
                    const statusElement = $(`#status-ruang-${safeRoomName}`);
                    statusElement.text(isRelayActive ? 'AKTIF' : 'NONAKTIF');
                    statusElement.removeClass('bg-gray-100 text-gray-800 bg-green-100 text-green-800')
                               .addClass(isRelayActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800');
                });
                
                // Show success notification
                const action = isRelayActive ? 'diaktifkan' : 'dinonaktifkan';
                Swal.fire({
                    title: 'Berhasil!',
                    text: `Relay berhasil ${action}`,
                    icon: 'success',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true,
                    background: '#f0fdf4',
                    iconColor: '#10b981'
                });
            },
            error: function(xhr) {
                // Revert state on error
                isRelayActive = !isRelayActive;
                updateButtonState();
                
                let errorMessage = 'Terjadi kesalahan saat mengontrol relay';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    title: 'Gagal!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        confirmButton: 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors'
                    }
                });
            },
            complete: function() {
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    $('#ttsForm').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        
        // Reset error states
        $('#messageError').addClass('hidden');
        $('#ttsRuanganError').addClass('hidden');
        
        // Client-side validation
        if (!formData.get('message')) {
            $('#messageError').removeClass('hidden');
            Swal.fire({
                title: 'Peringatan',
                text: 'Isi pengumuman diperlukan untuk mode TTS',
                icon: 'warning',
                confirmButtonText: 'Mengerti',
                customClass: {
                    confirmButton: 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors'
                }
            });
            return;
        }

        if ($('#message').val().length > 500) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Isi pengumuman melebihi 500 karakter',
                icon: 'warning',
                confirmButtonText: 'Mengerti',
                customClass: {
                    confirmButton: 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors'
                }
            });
            return;
        }

        const roomNames = formData.getAll('ruangans[]');
        if (roomNames.length === 0) {
            $('#ttsRuanganError').removeClass('hidden');
            Swal.fire({
                title: 'Peringatan',
                text: 'Pilih minimal satu ruangan',
                icon: 'warning',
                confirmButtonText: 'Mengerti',
                customClass: {
                    confirmButton: 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors'
                }
            });
            return;
        }

        // Show loading state
        const submitBtn = $(form).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.html('<i class="fas fa-circle-notch fa-spin mr-2"></i> Mengirim...');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Pengumuman TTS berhasil dikirim',
                        icon: 'success',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true,
                        background: '#f0fdf4',
                        iconColor: '#10b981',
                        willClose: () => {
                            window.location.href = "{{ route('admin.announcement.history') }}";
                        }
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Pengumuman TTS gagal dikirim';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    title: 'Gagal!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        confirmButton: 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors'
                    }
                });
            },
            complete: function() {
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Check relay status periodically
    function checkRelayStatus() {
        $.get("{{ url('/api/announcements/relay/status') }}", function(data) {
            data.forEach(room => {
                const safeRoomName = getSafeRoomSelector(room.nama_ruangan);
                const statusElement = $(`#status-ruang-${safeRoomName}`);
                if (statusElement.length) {
                    statusElement.text(room.relay_state === 'on' ? 'AKTIF' : 'NONAKTIF');
                    statusElement.removeClass('bg-gray-100 text-gray-800 bg-green-100 text-green-800')
                               .addClass(room.relay_state === 'on' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800');
                }
            });
        });
    }

    // Check MQTT connection status
    function checkMqttStatus() {
        $.get("{{ url('/api/announcements/mqtt/status') }}", function(data) {
            const statusElement = $('.mqtt-status-indicator');
            const textElement = $('.mqtt-status-text');
            
            if (data.connected) {
                statusElement.removeClass('bg-red-500').addClass('bg-green-500');
                textElement.removeClass('text-red-600').addClass('text-green-600');
                textElement.text('MQTT: Connected');
            } else {
                statusElement.removeClass('bg-green-500').addClass('bg-red-500');
                textElement.removeClass('text-green-600').addClass('text-red-600');
                textElement.text('MQTT: Disconnected');
            }
        });
    }

    // Update button state based on relay status
    function updateButtonState() {
        const btn = $('#toggleRelayBtn');
        if (isRelayActive) {
            btn.html('<i class="fas fa-broadcast-tower mr-2"></i> Matikan Relay');
            btn.removeClass('from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800')
               .addClass('from-red-600 to-red-700 hover:from-red-700 hover:to-red-800');
        } else {
            btn.html('<i class="fas fa-broadcast-tower mr-2"></i> Aktifkan Relay');
            btn.removeClass('from-red-600 to-red-700 hover:from-red-700 hover:to-red-800')
               .addClass('from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800');
        }
    }

    // Initialize
    let isRelayActive = false;
    updateButtonState();
    setInterval(checkRelayStatus, 10000);
    checkRelayStatus();
    setInterval(checkMqttStatus, 30000);
});
</script>
@endsection