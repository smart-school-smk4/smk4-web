@extends('layouts.dashboard')

@section('title', 'Tambah Jadwal Bel')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Tambah Jadwal Bel Baru</h2>
            <button onclick="showHelp()" class="text-blue-500 hover:text-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>
        
        <form id="createForm" action="{{ route('bel.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Hari -->
                <div>
                    <label for="hari" class="block text-sm font-medium text-gray-700 mb-1">Hari <span class="text-red-500">*</span></label>
                    <select name="hari" id="hari" required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border">
                        <option value="">Pilih Hari</option>
                        @foreach(\App\Models\JadwalBel::DAYS as $day)
                            <option value="{{ $day }}" {{ old('hari') == $day ? 'selected' : '' }}>
                                {{ $day }}
                            </option>
                        @endforeach
                    </select>
                    @error('hari')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Waktu -->
                <div>
                    <label for="waktu" class="block text-sm font-medium text-gray-700 mb-1">Waktu <span class="text-red-500">*</span></label>
                    <input type="time" name="waktu" id="waktu" required
                           value="{{ old('waktu') }}"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border">
                    @error('waktu')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- File Number -->
                <div>
                    <label for="file_number" class="block text-sm font-medium text-gray-700 mb-1">File MP3 <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="file_number" id="file_number" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border appearance-none">
                            <option value="">Pilih File</option>
                            @for($i = 1; $i <= 50; $i++)
                                <option value="{{ sprintf('%04d', $i) }}" 
                                    {{ old('file_number', $default_file ?? '0001') == sprintf('%04d', $i) ? 'selected' : '' }}>
                                    File {{ sprintf('%04d', $i) }}
                                </option>
                            @endfor
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                            </svg>
                        </div>
                    </div>
                    @error('file_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">Aktifkan Jadwal Ini</label>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end space-x-3">
                <button type="button" onclick="confirmCancel()" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg inline-flex items-center transition duration-300 shadow hover:shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Batal
                </button>
                
                <button type="submit" id="submitBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center transition duration-300 shadow-md hover:shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Fungsi untuk menampilkan bantuan
function showHelp() {
    Swal.fire({
        title: 'Panduan Tambah Jadwal',
        html: `
            <div class="text-left">
                <p class="mb-2"><b>Hari:</b> Pilih hari sesuai jadwal bel</p>
                <p class="mb-2"><b>Waktu:</b> Masukkan waktu dalam format HH:MM</p>
                <p class="mb-2"><b>File MP3:</b> Pilih file suara yang akan diputar (0001-0050)</p>
                <p><b>Status:</b> Centang untuk mengaktifkan jadwal ini</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Mengerti',
        confirmButtonColor: '#3B82F6',
    });
}

// Konfirmasi pembatalan
function confirmCancel() {
    Swal.fire({
        title: 'Batalkan Penambahan?',
        text: 'Data yang sudah diisi akan hilang',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3B82F6',
        cancelButtonColor: '#EF4444',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Kembali ke Form',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('bel.index') }}";
        }
    });
}

// Handle form submission
document.getElementById('createForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Menyimpan...
    `;
    
    // Simpan data
    this.submit();
});
</script>
@endsection