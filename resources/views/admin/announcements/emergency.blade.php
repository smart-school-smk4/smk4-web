@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto p-6">
    <!-- Judul Halaman -->
    <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-yellow-600 mb-8 text-center">
        Mode Darurat
    </h1>

    <!-- Card Container -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden max-w-3xl mx-auto">
        <div class="p-6">
            <!-- Form -->
            <form id="emergency-form" action="{{ route('announcements.emergency') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Pilih Jenis Darurat -->
                <div>
                    <label for="emergency-type" class="block text-sm font-medium text-gray-700">
                        Jenis Darurat <span class="text-red-500">*</span>
                    </label>
                    <select id="emergency-type" name="emergency_type"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                        <option value="fire">Kebakaran</option>
                        <option value="earthquake">Gempa Bumi</option>
                        <option value="evacuation">Evakuasi</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <!-- Isi Pesan Darurat (Hanya untuk "Lainnya") -->
                <div id="custom-message-field" style="display: none;">
                    <label for="custom-message" class="block text-sm font-medium text-gray-700">
                        Pesan Darurat <span class="text-red-500">*</span>
                    </label>
                    <textarea id="custom-message" name="custom_message" rows="4"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Masukkan pesan darurat..."></textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex justify-between items-center">
                    <a href="{{ route('announcements.index') }}"
                        class="text-gray-500 hover:text-gray-700 font-medium transition duration-300 ease-in-out flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm-3.707-8.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        Kembali ke Daftar Pengumuman
                    </a>
                    <button type="submit" id="submit-btn"
                        class="relative inline-flex items-center justify-center px-8 py-3 font-medium text-white rounded-lg transition duration-300 ease-in-out hover:scale-105 shadow-md bg-gradient-to-r from-red-600 to-yellow-600">
                        <svg id="spinner" class="hidden animate-spin mr-2 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Aktifkan Mode Darurat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    const emergencyTypeField = document.getElementById('emergency-type');
    const customMessageField = document.getElementById('custom-message-field');

    // Tampilkan/hilangkan field pesan khusus berdasarkan jenis darurat
    emergencyTypeField.addEventListener('change', () => {
        if (emergencyTypeField.value === 'other') {
            customMessageField.style.display = 'block';
        } else {
            customMessageField.style.display = 'none';
        }
    });

    const form = document.getElementById('emergency-form');
    const submitBtn = document.getElementById('submit-btn');
    const spinner = document.getElementById('spinner');

    // Handle form submission
    form.addEventListener('submit', function () {
        // Tampilkan loading SweetAlert
        Swal.fire({
            title: 'Mengaktifkan Mode Darurat...',
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