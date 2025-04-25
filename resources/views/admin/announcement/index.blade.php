@extends('layouts.dashboard')

@section('title', 'Manajemen Pengumuman')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Sistem Pengumuman Digital</h1>
            <p class="text-gray-600 mt-2">Kelola dan kirim pengumuman ke ruangan terpilih</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('announcement.history') }}" 
               class="flex items-center px-5 py-2.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition duration-300 shadow-sm">
                <i class="fas fa-history mr-2"></i> Riwayat
            </a>
            <button id="help-btn" class="flex items-center px-5 py-2.5 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition duration-300 shadow-sm">
                <i class="fas fa-question-circle mr-2"></i> Bantuan
            </button>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8 border border-gray-100">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
            <h2 class="text-xl font-semibold text-white">Formulir Pengumuman Baru</h2>
        </div>
        
        <!-- Card Body -->
        <div class="p-6">
            <form id="announcementForm" action="{{ route('announcement.store') }}" method="POST">
                @csrf
                
                <!-- Mode Selection -->
                <div class="mb-8">
                    <label class="block text-gray-700 font-medium mb-3">Jenis Pengumuman</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="mode-option">
                            <input type="radio" name="mode" value="reguler" checked 
                                   class="absolute opacity-0 h-0 w-0 mode-selector" data-mode="reguler">
                            <div class="border-2 border-gray-200 rounded-xl p-5 flex items-center hover:border-blue-400 transition duration-200 cursor-pointer h-full">
                                <div class="mr-4">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-align-left text-blue-600 text-lg"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800">Pengumuman Teks</h3>
                                    <p class="text-sm text-gray-500 mt-1">Kirim pengumuman berupa teks biasa</p>
                                </div>
                            </div>
                        </label>
                        <label class="mode-option">
                            <input type="radio" name="mode" value="tts" 
                                   class="absolute opacity-0 h-0 w-0 mode-selector" data-mode="tts">
                            <div class="border-2 border-gray-200 rounded-xl p-5 flex items-center hover:border-purple-400 transition duration-200 cursor-pointer h-full">
                                <div class="mr-4">
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <i class="fas fa-volume-up text-purple-600 text-lg"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-800">Pengumuman Suara (TTS)</h3>
                                    <p class="text-sm text-gray-500 mt-1">Konversi teks ke suara otomatis</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Regular Announcement Form -->
                <div id="reguler-form" class="mode-form">
                    <div class="mb-6">
                        <label for="message" class="block text-gray-700 font-medium mb-2">Konten Pengumuman</label>
                        <div class="relative">
                            <textarea name="message" id="message" rows="4" maxlength="500"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                                      placeholder="Tulis isi pengumuman Anda di sini..."></textarea>
                            <div class="absolute bottom-2 right-2 bg-white px-2 text-xs text-gray-500 rounded">
                                <span id="char-count">0</span>/500
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TTS Announcement Form -->
                <div id="tts-form" class="mode-form hidden">
                    <div class="mb-6">
                        <label for="tts_text" class="block text-gray-700 font-medium mb-2">Teks untuk Suara</label>
                        <div class="relative">
                            <textarea name="tts_text" id="tts_text" rows="4" maxlength="1000"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                                      placeholder="Masukkan teks yang akan diubah menjadi suara..."></textarea>
                            <div class="absolute bottom-2 right-2 bg-white px-2 text-xs text-gray-500 rounded">
                                <span id="tts-char-count">0</span>/1000
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="tts_voice" class="block text-gray-700 font-medium mb-2">Pilihan Suara</label>
                            <div class="relative">
                                <select name="tts_voice" id="tts_voice"
                                        class="appearance-none w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 pr-10 bg-white">
                                    <option value="id-id">Bahasa Indonesia</option>
                                    <option value="en-us">English (Amerika)</option>
                                    <option value="en-gb">English (Inggris)</option>
                                    <option value="ja-jp">日本語 (Jepang)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="tts_speed" class="block text-gray-700 font-medium mb-2">Kecepatan Bicara</label>
                            <div class="flex items-center space-x-4">
                                <i class="fas fa-turtle text-gray-400"></i>
                                <input type="range" name="tts_speed" id="tts_speed" min="-10" max="10" value="0"
                                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-purple-600">
                                <i class="fas fa-hare text-gray-400"></i>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1 px-1">
                                <span>Lambat</span>
                                <span>Normal</span>
                                <span>Cepat</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <button type="button" id="preview-tts" 
                                class="px-5 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-300 shadow-md flex items-center">
                            <i class="fas fa-play-circle mr-2"></i> Dengarkan Preview
                        </button>
                        <div class="mt-3 flex items-center">
                            <audio id="tts-preview" controls class="w-full max-w-md hidden"></audio>
                            <div id="preview-loading" class="hidden items-center text-purple-600 ml-3">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                <span>Memproses audio...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ruangan Selection -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-gray-700 font-medium">Tujuan Pengumuman</label>
                        <div class="flex space-x-3">
                            <button type="button" id="select-all" 
                                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                <i class="fas fa-check-circle mr-1"></i> Pilih Semua
                            </button>
                            <button type="button" id="deselect-all" 
                                    class="text-sm text-gray-600 hover:text-gray-800 flex items-center">
                                <i class="fas fa-times-circle mr-1"></i> Hapus Semua
                            </button>
                        </div>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($ruangan as $room)
                            <label class="room-label flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition duration-200 cursor-pointer">
                                <input type="checkbox" name="ruangan[]" value="{{ $room->id }}" 
                                       class="form-checkbox h-5 w-5 text-blue-600 rounded room-checkbox transition duration-200">
                                <div>
                                    <span class="font-medium text-gray-800">{{ $room->nama_ruangan }}</span>
                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                        <i class="fas fa-door-open mr-1"></i>
                                        <span>{{ $room->kelas->nama_kelas ?? '-' }} • {{ $room->jurusan->nama_jurusan ?? '-' }}</span>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" id="submit-btn"
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-lg hover:from-blue-700 hover:to-indigo-800 transition duration-300 shadow-lg flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim Pengumuman
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Announcements Section -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <!-- Section Header -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <h2 class="text-lg font-semibold text-gray-800">Pengumuman Terakhir</h2>
                <div class="mt-2 md:mt-0 text-sm">
                    Menampilkan {{ $announcements->count() }} dari {{ $announcements->total() }} pengumuman
                </div>
            </div>
        </div>
        
        <!-- Section Body -->
        <div class="p-6">
            @if($announcements->isEmpty())
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-bullhorn text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-700">Belum Ada Pengumuman</h3>
                <p class="text-gray-500 mt-1">Mulailah dengan membuat pengumuman baru</p>
            </div>
            @else
            <div class="space-y-4">
                @foreach($announcements as $announcement)
                <div class="announcement-card border border-gray-200 rounded-xl p-5 hover:shadow-md transition duration-200">
                    <div class="flex justify-between items-start">
                        <div class="flex items-start space-x-4">
                            <div class="mt-1">
                                @if($announcement->mode === 'reguler')
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-align-left text-blue-600"></i>
                                </div>
                                @else
                                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                    <i class="fas fa-volume-up text-purple-600"></i>
                                </div>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 line-clamp-2">
                                    {{ $announcement->message }}
                                </h3>
                                <div class="flex flex-wrap items-center mt-2 text-sm text-gray-500 space-x-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-circle mr-1.5"></i>
                                        <span>{{ $announcement->user->name }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-1.5"></i>
                                        <span>{{ $announcement->sent_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-door-open mr-1.5"></i>
                                        <span>{{ count($announcement->ruangan) }} ruangan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown relative">
                            <button class="dropdown-toggle p-1 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition duration-200">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu absolute right-0 mt-1 w-40 bg-white rounded-md shadow-lg py-1 z-10 hidden border border-gray-200">
                                <a href="{{ route('announcement.show', $announcement->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2"></i> Detail
                                </a>
                                <form action="{{ route('announcement.destroy', $announcement->id) }}" method="POST" class="block w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete(this)" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-trash-alt mr-2"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $announcements->links('vendor.pagination.tailwind') }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Help Modal -->
<div id="help-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="border-b border-gray-200 p-5 flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-800">Panduan Penggunaan</h3>
            <button id="close-help" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-5">
            <div class="space-y-6">
                <div>
                    <h4 class="font-medium text-lg text-gray-800 mb-2 flex items-center">
                        <span class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 text-blue-600">
                            <i class="fas fa-info-circle"></i>
                        </span>
                        Tentang Sistem Pengumuman
                    </h4>
                    <p class="text-gray-600 pl-11">
                        Sistem ini memungkinkan Anda mengirim pengumuman ke ruangan terpilih dalam dua format: teks biasa atau suara (TTS). 
                        Pengumuman akan langsung diterima oleh perangkat di ruangan tujuan.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-medium text-lg text-gray-800 mb-2 flex items-center">
                        <span class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3 text-purple-600">
                            <i class="fas fa-volume-up"></i>
                        </span>
                        Mode Text-to-Speech (TTS)
                    </h4>
                    <ul class="text-gray-600 pl-11 space-y-2 list-disc list-inside">
                        <li>Gunakan untuk pengumuman audio yang akan dibacakan oleh sistem</li>
                        <li>Anda bisa memilih jenis suara dan kecepatan bicara</li>
                        <li>Gunakan tombol preview untuk mendengarkan sebelum mengirim</li>
                        <li>Maksimal 1000 karakter per pengumuman</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-medium text-lg text-gray-800 mb-2 flex items-center">
                        <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3 text-green-600">
                            <i class="fas fa-lightbulb"></i>
                        </span>
                        Tips Penggunaan
                    </h4>
                    <ul class="text-gray-600 pl-11 space-y-2 list-disc list-inside">
                        <li>Untuk pengumuman penting, gunakan mode TTS untuk memastikan didengar</li>
                        <li>Gunakan bahasa yang jelas dan singkat</li>
                        <li>Periksa kembali ruangan tujuan sebelum mengirim</li>
                        <li>Anda bisa melihat riwayat pengumuman di menu Riwayat</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-200 p-5 flex justify-end">
            <button id="close-help-btn" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                Mengerti
            </button>
        </div>
    </div>
</div>

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mode selector with enhanced UI
    const modeOptions = document.querySelectorAll('.mode-option');
    modeOptions.forEach(option => {
        option.addEventListener('click', function() {
            modeOptions.forEach(opt => {
                opt.querySelector('div').classList.remove('border-blue-400', 'border-purple-400', 'bg-blue-50', 'bg-purple-50');
            });
            
            const selectedMode = this.querySelector('input').dataset.mode;
            const optionDiv = this.querySelector('div');
            
            if (selectedMode === 'reguler') {
                optionDiv.classList.add('border-blue-400', 'bg-blue-50');
            } else {
                optionDiv.classList.add('border-purple-400', 'bg-purple-50');
            }
            
            // Toggle forms
            document.querySelectorAll('.mode-form').forEach(form => {
                form.classList.toggle('hidden', form.id !== `${selectedMode}-form`);
            });
            
            // Update room checkboxes name based on mode
            document.querySelectorAll('.room-checkbox').forEach(checkbox => {
                checkbox.name = selectedMode === 'tts' ? 'tts_ruangan[]' : 'ruangan[]';
            });
        });
    });

    // Initialize first mode as selected
    document.querySelector('.mode-option input[checked]').dispatchEvent(new Event('click'));

    // Character counter with progress circle
    const setupCharCounter = (textarea, counter, max) => {
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            counter.textContent = count;
            
            // Update progress color
            const percentage = (count / max) * 100;
            if (percentage > 90) {
                counter.classList.add('text-red-500');
                counter.classList.remove('text-yellow-500', 'text-gray-500');
            } else if (percentage > 70) {
                counter.classList.add('text-yellow-500');
                counter.classList.remove('text-red-500', 'text-gray-500');
            } else {
                counter.classList.add('text-gray-500');
                counter.classList.remove('text-red-500', 'text-yellow-500');
            }
        });
    };

    setupCharCounter(document.getElementById('message'), document.getElementById('char-count'), 500);
    setupCharCounter(document.getElementById('tts_text'), document.getElementById('tts-char-count'), 1000);

    // Room selection with enhanced UI
    document.getElementById('select-all').addEventListener('click', function() {
        document.querySelectorAll('.room-checkbox').forEach(checkbox => {
            checkbox.checked = true;
            checkbox.closest('.room-label').classList.add('bg-blue-50', 'border-blue-200');
        });
    });
    
    document.getElementById('deselect-all').addEventListener('click', function() {
        document.querySelectorAll('.room-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            checkbox.closest('.room-label').classList.remove('bg-blue-50', 'border-blue-200');
        });
    });

    // Room label click handler
    document.querySelectorAll('.room-label').forEach(label => {
        label.addEventListener('click', function(e) {
            if (!e.target.classList.contains('room-checkbox')) {
                const checkbox = this.querySelector('.room-checkbox');
                checkbox.checked = !checkbox.checked;
                
                if (checkbox.checked) {
                    this.classList.add('bg-blue-50', 'border-blue-200');
                } else {
                    this.classList.remove('bg-blue-50', 'border-blue-200');
                }
            }
        });
    });

    // TTS Preview with enhanced UI
    document.getElementById('preview-tts').addEventListener('click', function() {
        const text = document.getElementById('tts_text').value.trim();
        const voice = document.getElementById('tts_voice').value;
        const speed = document.getElementById('tts_speed').value;
        
        if (!text) {
            Swal.fire({
                icon: 'warning',
                title: 'Teks Kosong',
                text: 'Silakan masukkan teks untuk dipreview',
                confirmButtonColor: '#6366f1',
                backdrop: 'rgba(99, 102, 241, 0.1)'
            });
            return;
        }
        
        // Show loading
        const previewBtn = this;
        const loadingIndicator = document.getElementById('preview-loading');
        const audioPlayer = document.getElementById('tts-preview');
        
        previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        previewBtn.disabled = true;
        loadingIndicator.classList.remove('hidden');
        audioPlayer.classList.add('hidden');
        
        // Call TTS API
        fetch("{{ route('announcement.ttsPreview') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                text: text,
                voice: voice,
                speed: speed
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.audio_url) {
                audioPlayer.src = data.audio_url;
                audioPlayer.classList.remove('hidden');
                loadingIndicator.classList.add('hidden');
                audioPlayer.play();
                
                // Show success toast
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
                
                Toast.fire({
                    icon: 'success',
                    title: 'Preview audio siap!'
                });
            } else {
                throw new Error(data.message || 'Gagal menghasilkan audio');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Membuat Preview',
                text: error.message || 'Terjadi kesalahan saat menghasilkan audio',
                confirmButtonColor: '#6366f1',
                backdrop: 'rgba(99, 102, 241, 0.1)'
            });
        })
        .finally(() => {
            previewBtn.innerHTML = '<i class="fas fa-play-circle mr-2"></i> Dengarkan Preview';
            previewBtn.disabled = false;
            loadingIndicator.classList.add('hidden');
        });
    });

    // Form submission with enhanced validation
    document.getElementById('announcementForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const selectedRooms = document.querySelectorAll('.room-checkbox:checked');
        const mode = document.querySelector('input[name="mode"]:checked').value;
        const messageField = mode === 'reguler' ? 'message' : 'tts_text';
        const message = formData.get(messageField);
        
        // Validation
        if (selectedRooms.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Ruangan Belum Dipilih',
                text: 'Silakan pilih setidaknya satu ruangan tujuan',
                confirmButtonColor: '#6366f1',
                backdrop: 'rgba(99, 102, 241, 0.1)'
            });
            
            // Scroll to room selection
            document.querySelector('.room-checkbox').closest('.mb-8').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            return;
        }
        
        if (!message || message.trim().length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Konten Kosong',
                text: `Silakan isi ${mode === 'reguler' ? 'pesan pengumuman' : 'teks untuk TTS'}`,
                confirmButtonColor: '#6366f1',
                backdrop: 'rgba(99, 102, 241, 0.1)'
            });
            return;
        }
        
        // Confirmation dialog
        Swal.fire({
            title: 'Konfirmasi Pengiriman',
            html: `
                <div class="text-left">
                    <p class="mb-2">Anda akan mengirim pengumuman <span class="font-semibold">${mode === 'reguler' ? 'teks' : 'suara (TTS)'}</span> ke:</p>
                    <ul class="list-disc list-inside mb-4 max-h-40 overflow-y-auto">
                        ${Array.from(selectedRooms).map(room => {
                            const roomLabel = room.closest('.room-label').querySelector('span').textContent;
                            return `<li>${roomLabel}</li>`;
                        }).join('')}
                    </ul>
                    <p class="text-sm text-gray-500">Pastikan informasi sudah benar sebelum mengirim</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6366f1',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim Sekarang',
            cancelButtonText: 'Periksa Kembali',
            backdrop: 'rgba(99, 102, 241, 0.1)',
            width: '32rem'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                const submitBtn = document.getElementById('submit-btn');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...';
                submitBtn.disabled = true;
                
                // Submit form
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else {
                        return response.json();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengirim',
                        text: error.message || 'Terjadi kesalahan saat mengirim pengumuman',
                        confirmButtonColor: '#6366f1',
                        backdrop: 'rgba(99, 102, 241, 0.1)'
                    });
                })
                .finally(() => {
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Kirim Pengumuman';
                    submitBtn.disabled = false;
                });
            }
        });
    });

    // Help modal
    const helpModal = document.getElementById('help-modal');
    document.getElementById('help-btn').addEventListener('click', () => {
        helpModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    });
    
    document.getElementById('close-help').addEventListener('click', () => {
        helpModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    });
    
    document.getElementById('close-help-btn').addEventListener('click', () => {
        helpModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    });

    // Dropdown menu for announcement cards
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== menu) m.classList.add('hidden');
            });
            menu.classList.toggle('hidden');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    });

    // Confirm delete function
    function confirmDelete(form) {
        Swal.fire({
            title: 'Hapus Pengumuman?',
            text: "Anda tidak akan bisa mengembalikan data ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6366f1',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            backdrop: 'rgba(99, 102, 241, 0.1)'
        }).then((result) => {
            if (result.isConfirmed) {
                form.closest('form').submit();
            }
        });
    };
});
</script>
@endsection