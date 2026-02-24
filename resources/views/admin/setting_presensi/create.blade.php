@extends('layouts.dashboard')

@section('title', 'Smart School | Edit Waktu Presensi')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <div class="max-w-2xl mx-auto">
        <div class="p-8 bg-white rounded-lg shadow-lg">
            <h1 class="mb-6 text-2xl font-bold text-gray-800">📝 Tambah Pengaturan Presensi</h1>
            
            <form method="POST" action="{{ route('admin.setting_presensi.store') }}">
                @csrf
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <h3 class="mb-4 font-semibold text-gray-700">Jam Masuk</h3>
                        <div class="mb-4">
                            <label for="waktu_masuk_mulai" class="block mb-2 text-sm font-medium text-gray-700">Waktu Mulai</label>
                            <input type="time" name="waktu_masuk_mulai" id="waktu_masuk_mulai" class="block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label for="waktu_masuk_selesai" class="block mb-2 text-sm font-medium text-gray-700">Waktu Selesai</label>
                            <input type="time" name="waktu_masuk_selesai" id="waktu_masuk_selesai" class="block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                    
                    <div class="p-4 border border-gray-200 rounded-lg">
                         <h3 class="mb-4 font-semibold text-gray-700">Jam Pulang</h3>
                        <div class="mb-4">
                            <label for="waktu_pulang_mulai" class="block mb-2 text-sm font-medium text-gray-700">Waktu Mulai</label>
                            <input type="time" name="waktu_pulang_mulai" id="waktu_pulang_mulai" class="block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label for="waktu_pulang_selesai" class="block mb-2 text-sm font-medium text-gray-700">Waktu Selesai</label>
                            <input type="time" name="waktu_pulang_selesai" id="waktu_pulang_selesai" class="block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                </div>

                <!-- Threshold Probabilitas Section -->
                <div class="p-4 mt-6 border border-blue-200 rounded-lg bg-blue-50">
                    <h3 class="mb-3 font-semibold text-gray-700 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Threshold Akurasi Face Recognition
                    </h3>
                    <div>
                        <label for="threshold_probabilitas" class="block mb-2 text-sm font-medium text-gray-700">
                            Minimum Akurasi Deteksi Wajah (0.1 - 1.0)
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="number" name="threshold_probabilitas" id="threshold_probabilitas" 
                                   value="0.50" 
                                   min="0.1" max="1.0" step="0.05" 
                                   class="block w-32 px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                            <span class="text-sm text-gray-600">
                                <span class="font-semibold" id="thresholdPercentage">50%</span> - 
                                <span id="thresholdDescription" class="text-blue-600">Seimbang</span>
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            💡 <strong>Rekomendasi:</strong> 0.50 (Seimbang antara akurasi dan false positive). 
                            Nilai lebih tinggi = lebih ketat (mengurangi kesalahan deteksi), nilai lebih rendah = lebih longgar.
                        </p>
                    </div>
                </div>

                <script>
                    // Update threshold percentage display
                    const thresholdInput = document.getElementById('threshold_probabilitas');
                    const thresholdPercentage = document.getElementById('thresholdPercentage');
                    const thresholdDescription = document.getElementById('thresholdDescription');
                    
                    thresholdInput.addEventListener('input', function() {
                        const value = parseFloat(this.value);
                        thresholdPercentage.textContent = Math.round(value * 100) + '%';
                        
                        if (value < 0.3) {
                            thresholdDescription.textContent = 'Sangat Longgar (Banyak false positive)';
                            thresholdDescription.className = 'text-red-600';
                        } else if (value < 0.5) {
                            thresholdDescription.textContent = 'Longgar';
                            thresholdDescription.className = 'text-orange-600';
                        } else if (value <= 0.6) {
                            thresholdDescription.textContent = 'Seimbang (Direkomendasikan)';
                            thresholdDescription.className = 'text-blue-600';
                        } else if (value <= 0.8) {
                            thresholdDescription.textContent = 'Ketat';
                            thresholdDescription.className = 'text-green-600';
                        } else {
                            thresholdDescription.textContent = 'Sangat Ketat (Banyak missed detection)';
                            thresholdDescription.className = 'text-purple-600';
                        }
                    });
                </script>

                <div class="flex justify-end mt-8 space-x-4">
                    <a href="{{ route('admin.setting_presensi.index') }}" class="px-4 py-2 font-bold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-300">Batal</a>
                    <button type="submit" class="px-4 py-2 font-bold text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition duration-300">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection