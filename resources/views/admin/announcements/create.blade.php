@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto p-6">
    <!-- Judul Halaman -->
    <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 mb-8 text-center">
        Tambah Pengumuman
    </h1>

    <!-- Card Container -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden max-w-3xl mx-auto">
        <div class="p-6">
            <!-- Form -->
            <form id="announcement-form" action="{{ route('announcements.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Judul Pengumuman -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        Judul Pengumuman <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Masukkan judul pengumuman..."
                        required>
                </div>

                <!-- Tipe Pengumuman -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">
                        Tipe Pengumuman <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                        <option value="tts">TTS (Text-to-Speech)</option>
                        <option value="manual">Manual Mic</option>
                    </select>
                </div>

                <!-- Isi Pengumuman (Hanya untuk TTS) -->
                <div id="content-field" style="display: none;">
                    <label for="content" class="block text-sm font-medium text-gray-700">
                        Isi Pengumuman <span class="text-red-500">*</span>
                    </label>
                    <textarea id="content" name="content" rows="4"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Masukkan teks pengumuman..."
                        minlength="5" maxlength="200"></textarea>
                    <span id="text-error" class="text-sm text-red-500 mt-1 hidden">Teks minimal 5 karakter dan maksimal 200 karakter.</span>
                </div>

                <!-- Pilih Bahasa (Hanya untuk TTS) -->
                <div id="language-field" style="display: none;">
                    <label for="language" class="block text-sm font-medium text-gray-700">
                        Bahasa <span class="text-red-500">*</span>
                    </label>
                    <select id="language" name="language"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="id">Indonesia</option>
                        <option value="en">English</option>
                    </select>
                </div>

                <!-- Pilih Ruangan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4 4a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 00-1 1v3a1 1 0 001 1h8a1 1 0 001-1v-3a1 1 0 00-1-1H6z"
                                clip-rule="evenodd" />
                        </svg>
                        Pilih Ruangan <span class="text-red-500">*</span>
                    </label>

                    <!-- Checkbox Semua -->
                    <div class="flex items-center mt-2">
                        <input type="checkbox" id="select-all-rooms"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="select-all-rooms" class="ml-2 text-sm text-gray-700">Pilih Semua</label>
                    </div>

                    <!-- Daftar Checkbox Ruangan -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-4">
                        @foreach($rooms as $room)
                        <div class="flex items-center">
                            <input type="checkbox" id="room-{{ $room->id }}" name="rooms[]"
                                value="{{ $room->name }}"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded room-checkbox">
                            <label for="room-{{ $room->id }}"
                                class="ml-2 text-sm text-gray-700">{{ $room->name }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex justify-between items-center">
                    <a href="{{ route('announcements.index') }}"
                        class="text-gray-500 hover:text-gray-700 font-medium transition duration-300 ease-in-out flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm-3.707-8.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        Kembali ke Daftar Pengumuman
                    </a>
                    <button type="submit" id="submit-btn"
                        class="relative inline-flex items-center justify-center px-8 py-3 font-medium text-white rounded-lg transition duration-300 ease-in-out hover:scale-105 shadow-md bg-gradient-to-r from-blue-600 to-purple-600">
                        <svg id="spinner" class="hidden animate-spin mr-2 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Simpan Pengumuman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    const form = document.getElementById('announcement-form');
    const submitBtn = document.getElementById('submit-btn');
    const spinner = document.getElementById('spinner');
    const contentField = document.getElementById('content-field');
    const languageField = document.getElementById('language-field');
    const typeField = document.getElementById('type');

    // Tampilkan/hilangkan field berdasarkan tipe pengumuman
    typeField.addEventListener('change', () => {
        if (typeField.value === 'tts') {
            contentField.style.display = 'block';
            languageField.style.display = 'block';
        } else {
            contentField.style.display = 'none';
            languageField.style.display = 'none';
        }
    });

    // Checkbox "Pilih Semua"
    const selectAll = document.getElementById('select-all-rooms');
    const roomCheckboxes = document.querySelectorAll('.room-checkbox');

    selectAll.addEventListener('change', () => {
        roomCheckboxes.forEach(cb => cb.checked = selectAll.checked);
    });

    roomCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked) selectAll.checked = false;
            else selectAll.checked = Array.from(roomCheckboxes).every(c => c.checked);
        });
    });

    // Validasi panjang teks
    const contentTextarea = document.getElementById('content');
    const errorSpan = document.getElementById('text-error');

    if (contentTextarea) {
        contentTextarea.addEventListener('input', function () {
            const value = contentTextarea.value.trim();
            if (value.length < 5 || value.length > 200) {
                errorSpan.classList.remove('hidden');
            } else {
                errorSpan.classList.add('hidden');
            }
        });
    }

    // Handle form submission
    form.addEventListener('submit', function () {
        // Tampilkan loading SweetAlert
        Swal.fire({
            title: 'Memproses Pengumuman...',
            text: 'Mohon tunggu sebentar.',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => Swal.showLoading()
        });

        // Tampilkan spinner dan disable tombol
        spinner.classList.remove('hidden');
        submitBtn.setAttribute('disabled', true);
    });
</script>
@endsection