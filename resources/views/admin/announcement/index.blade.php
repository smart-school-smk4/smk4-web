@extends('layouts.dashboard')

@section('title', 'Pengumuman Sekolah')

@section('content')
<style>
.selection-tab.active {
    font-weight: 600;
}
.rotate-180 {
    transform: rotate(180deg);
}
</style>
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Sistem Pengumuman Sekolah</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola pengumuman TTS untuk seluruh ruangan sekolah</p>
            
            <div class="flex items-center mt-3 gap-4">
                <div class="flex items-center bg-white px-3 py-1.5 rounded-lg shadow-sm border border-gray-200">
                    <span class="relative flex h-3 w-3 mr-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $mqttStatus ? 'bg-green-400' : 'bg-red-400' }} opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $mqttStatus ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    </span>
                    <span class="text-sm font-medium {{ $mqttStatus ? 'text-green-700' : 'text-red-700' }}">
                        {{ $mqttStatus ? 'Connected' : 'Disconnected' }}
                    </span>
                </div>
                <div class="flex items-center text-sm text-gray-600 bg-white px-3 py-1.5 rounded-lg shadow-sm border border-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="font-semibold">{{ $ruangans->count() }}</span><span class="ml-1">Ruangan</span>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.announcement.history') }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                Riwayat
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">


        <!-- Tab Content -->
        <div class="p-6">
            <!-- TTS Mode Tab -->
            <div id="ttsTab" class="tab-content">
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
                            <div class="flex items-center gap-2">
                                <button type="button" id="selectAllTTS" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors flex items-center">
                                    <i class="fas fa-check-circle mr-1"></i> Pilih Semua
                                </button>
                            </div>
                        </div>

                        <!-- Selection Mode Tabs -->
                        <div class="mb-4 border-b border-gray-200">
                            <nav class="flex -mb-px" aria-label="Tabs">
                                <button type="button" id="tabByRuangan" class="selection-tab active px-4 py-2 text-sm font-medium border-b-2 border-green-500 text-green-600 hover:text-green-700 transition-colors">
                                    <i class="fas fa-door-open mr-1"></i> Per Ruangan
                                </button>
                                <button type="button" id="tabByJurusan" class="selection-tab px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors">
                                    <i class="fas fa-building mr-1"></i> Per Jurusan
                                </button>
                            </nav>
                        </div>

                        @php
                            // Group ruangan berdasarkan jurusan
                            $ruangansByJurusan = collect($ruangans)->groupBy('jurusan.nama_jurusan')->sortKeys();
                            
                            // Urutkan ruangan berdasarkan angka dalam nama_ruangan
                            $sortedRuangans = collect($ruangans)->sortBy(function($ruangan) {
                                preg_match('/\d+/', $ruangan->nama_ruangan, $matches);
                                return (int) ($matches[0] ?? PHP_INT_MAX);
                            });
                        @endphp

                        <!-- Content Per Ruangan -->
                        <div id="contentByRuangan" class="selection-content">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($sortedRuangans as $ruangan)
                                    <div class="relative flex items-start p-3 rounded-lg border border-gray-200 hover:border-green-300 transition-colors">
                                        <div class="flex items-center h-5 mt-1">
                                            <input id="tts-ruang-{{ $ruangan->nama_ruangan }}" 
                                                name="ruangans[]" 
                                                type="checkbox" 
                                                value="{{ $ruangan->nama_ruangan }}" 
                                                class="ruangan-checkbox focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 rounded"
                                                data-jurusan="{{ $ruangan->jurusan->nama_jurusan ?? 'Lainnya' }}">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="tts-ruang-{{ $ruangan->nama_ruangan }}" class="font-medium text-gray-700 flex items-center cursor-pointer">
                                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500 mr-2"></span>
                                                {{ $ruangan->nama_ruangan }}
                                            </label>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $ruangan->jurusan->nama_jurusan ?? 'Lainnya' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Content Per Jurusan -->
                        <div id="contentByJurusan" class="selection-content hidden">
                            <div class="space-y-4">
                                @foreach($ruangansByJurusan as $namaJurusan => $ruanganList)
                                    @php
                                        $sortedJurusanRuangans = $ruanganList->sortBy(function($ruangan) {
                                            preg_match('/\d+/', $ruangan->nama_ruangan, $matches);
                                            return (int) ($matches[0] ?? PHP_INT_MAX);
                                        });
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 cursor-pointer hover:from-blue-100 hover:to-indigo-100 transition-colors" onclick="toggleJurusan('{{ Str::slug($namaJurusan) }}')">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex items-center h-5">
                                                        <input id="jurusan-{{ Str::slug($namaJurusan) }}" 
                                                            type="checkbox" 
                                                            class="jurusan-checkbox focus:ring-blue-500 h-5 w-5 text-blue-600 border-gray-300 rounded"
                                                            data-jurusan="{{ $namaJurusan }}"
                                                            onclick="event.stopPropagation(); selectJurusan('{{ $namaJurusan }}')">
                                                    </div>
                                                    <label for="jurusan-{{ Str::slug($namaJurusan) }}" class="font-semibold text-gray-800 flex items-center cursor-pointer" onclick="event.stopPropagation()">
                                                        <i class="fas fa-graduation-cap mr-2 text-blue-600"></i>
                                                        {{ $namaJurusan }}
                                                    </label>
                                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium">
                                                        {{ $sortedJurusanRuangans->count() }} ruangan
                                                    </span>
                                                </div>
                                                <i class="fas fa-chevron-down transition-transform duration-200" id="icon-{{ Str::slug($namaJurusan) }}"></i>
                                            </div>
                                        </div>
                                        <div id="collapse-{{ Str::slug($namaJurusan) }}" class="hidden p-4 bg-white">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                @foreach($sortedJurusanRuangans as $ruangan)
                                                    <div class="flex items-start p-2 rounded border border-gray-100 hover:border-blue-200 hover:bg-blue-50 transition-colors">
                                                        <div class="flex items-center h-5 mt-0.5">
                                                            <input id="tts-jurusan-ruang-{{ $ruangan->nama_ruangan }}" 
                                                                type="checkbox" 
                                                                value="{{ $ruangan->nama_ruangan }}" 
                                                                class="ruangan-in-jurusan-checkbox focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                                                data-jurusan-group="{{ $namaJurusan }}"
                                                                onchange="updateJurusanCheckbox('{{ $namaJurusan }}')">
                                                        </div>
                                                        <div class="ml-2 text-sm">
                                                            <label for="tts-jurusan-ruang-{{ $ruangan->nama_ruangan }}" class="font-medium text-gray-700 cursor-pointer">
                                                                {{ $ruangan->nama_ruangan }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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
// Toggle Jurusan collapse
function toggleJurusan(slug) {
    const content = document.getElementById('collapse-' + slug);
    const icon = document.getElementById('icon-' + slug);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

// Select all rooms in a jurusan
function selectJurusan(namaJurusan) {
    const jurusanCheckbox = document.querySelector(`input[data-jurusan="${namaJurusan}"].jurusan-checkbox`);
    const isChecked = jurusanCheckbox.checked;
    
    // Get all room checkboxes in this jurusan
    const roomCheckboxes = document.querySelectorAll(`input[data-jurusan-group="${namaJurusan}"].ruangan-in-jurusan-checkbox`);
    
    roomCheckboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
    });
    
    // Sync to main form checkboxes
    syncJurusanToRuangan();
}

// Update jurusan checkbox state based on its rooms
function updateJurusanCheckbox(namaJurusan) {
    const roomCheckboxes = document.querySelectorAll(`input[data-jurusan-group="${namaJurusan}"].ruangan-in-jurusan-checkbox`);
    const jurusanCheckbox = document.querySelector(`input[data-jurusan="${namaJurusan}"].jurusan-checkbox`);
    
    const totalRooms = roomCheckboxes.length;
    const checkedRooms = Array.from(roomCheckboxes).filter(cb => cb.checked).length;
    
    if (checkedRooms === 0) {
        jurusanCheckbox.checked = false;
        jurusanCheckbox.indeterminate = false;
    } else if (checkedRooms === totalRooms) {
        jurusanCheckbox.checked = true;
        jurusanCheckbox.indeterminate = false;
    } else {
        jurusanCheckbox.checked = false;
        jurusanCheckbox.indeterminate = true;
    }
    
    // Sync to main form checkboxes
    syncJurusanToRuangan();
}

// Sync jurusan mode selections to main form
function syncJurusanToRuangan() {
    // Clear all main checkboxes first
    document.querySelectorAll('input[name="ruangans[]"]').forEach(cb => {
        cb.checked = false;
    });
    
    // Check the ones selected in jurusan mode
    document.querySelectorAll('.ruangan-in-jurusan-checkbox:checked').forEach(checkbox => {
        const value = checkbox.value;
        const mainCheckbox = document.querySelector(`input[name="ruangans[]"][value="${value}"]`);
        if (mainCheckbox) {
            mainCheckbox.checked = true;
        }
    });
}

// Sync ruangan mode selections to jurusan mode
function syncRuanganToJurusan() {
    // Clear all jurusan checkboxes first
    document.querySelectorAll('.ruangan-in-jurusan-checkbox').forEach(cb => {
        cb.checked = false;
    });
    
    // Check the ones selected in ruangan mode
    document.querySelectorAll('input[name="ruangans[]"]:checked').forEach(checkbox => {
        const value = checkbox.value;
        const jurusanCheckbox = document.querySelector(`.ruangan-in-jurusan-checkbox[value="${value}"]`);
        if (jurusanCheckbox) {
            jurusanCheckbox.checked = true;
            const jurusan = jurusanCheckbox.getAttribute('data-jurusan-group');
            updateJurusanCheckbox(jurusan);
        }
    });
}

$(document).ready(function() {
    let currentMode = 'ruangan'; // 'ruangan' or 'jurusan'
    
    // Tab switching
    $('#tabByRuangan').click(function() {
        currentMode = 'ruangan';
        $('.selection-tab').removeClass('active border-green-500 text-green-600').addClass('border-transparent text-gray-500');
        $(this).addClass('active border-green-500 text-green-600').removeClass('border-transparent text-gray-500');
        $('#contentByRuangan').removeClass('hidden');
        $('#contentByJurusan').addClass('hidden');
        syncJurusanToRuangan();
    });
    
    $('#tabByJurusan').click(function() {
        currentMode = 'jurusan';
        $('.selection-tab').removeClass('active border-green-500 text-green-600').addClass('border-transparent text-gray-500');
        $(this).addClass('active border-blue-500 text-blue-600').removeClass('border-transparent text-gray-500');
        $('#contentByJurusan').removeClass('hidden');
        $('#contentByRuangan').addClass('hidden');
        syncRuanganToJurusan();
    });
    
    // Sync when ruangan checkboxes change
    $(document).on('change', '.ruangan-checkbox', function() {
        if (currentMode === 'ruangan') {
            syncRuanganToJurusan();
        }
    });

    // Fungsi untuk membuat selector aman dari karakter khusus
    function getSafeRoomSelector(roomName) {
        return roomName.replace(/[^a-zA-Z0-9-]/g, '-');
    }

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
    $('#selectAllTTS').click(function() {
        if (currentMode === 'ruangan') {
            const allChecked = $('.ruangan-checkbox').length === $('.ruangan-checkbox:checked').length;
            $('.ruangan-checkbox').prop('checked', !allChecked);
            syncRuanganToJurusan();
            // Update button text
            $(this).html(`<i class="fas ${!allChecked ? 'fa-times-circle' : 'fa-check-circle'} mr-1"></i> ${!allChecked ? 'Batal Pilih' : 'Pilih Semua'}`);
        } else {
            const allChecked = $('.jurusan-checkbox').length === $('.jurusan-checkbox:checked').length;
            $('.jurusan-checkbox').prop('checked', !allChecked);
            $('.jurusan-checkbox').each(function() {
                const jurusan = $(this).attr('data-jurusan');
                selectJurusan(jurusan);
            });
            // Update button text
            $(this).html(`<i class="fas ${!allChecked ? 'fa-times-circle' : 'fa-check-circle'} mr-1"></i> ${!allChecked ? 'Batal Pilih' : 'Pilih Semua'}`);
        }
    });

    // Reset forms

    $('#resetTTS').click(function() {
        $('#ttsForm')[0].reset();
        $('#charCount').text('0');
        $('#messageError').addClass('hidden');
        $('#ttsRuanganError').addClass('hidden');
        $('#selectAllTTS').html('<i class="fas fa-check-circle mr-1"></i> Pilih Semua');
        
        // Reset jurusan checkboxes
        $('.jurusan-checkbox').prop('checked', false);
        $('.ruangan-in-jurusan-checkbox').prop('checked', false);
    });

    // Form validation and submission
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

        // Non-blocking fetch request (async)
        fetch($(form).attr('action'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
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
                        // Reset form tanpa reload halaman
                        form.reset();
                        $('#charCount').text('0');
                        $('#messageError').addClass('hidden');
                        $('#ttsRuanganError').addClass('hidden');
                        $('#selectAllTTS').html('<i class="fas fa-check-circle mr-1"></i> Pilih Semua');
                        submitBtn.html(originalBtnText);
                        submitBtn.prop('disabled', false);
                        
                        // Redirect ke history setelah 2 detik
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.announcement.history') }}";
                        }, 500);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'Pengumuman TTS gagal dikirim';
            Swal.fire({
                title: 'Gagal!',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'Tutup',
                customClass: {
                    confirmButton: 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors'
                }
            });
            submitBtn.html(originalBtnText);
            submitBtn.prop('disabled', false);
        });
    });

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

    // Initialize
    checkMqttStatus();
    setInterval(checkMqttStatus, 30000);
});
</script>
@endsection