@extends('layouts.dashboard')

@section('title', 'Smart School | Pendaftaran Siswa Baru')

@section('content')
<div class="container mx-auto px-4 py-8 relative">

    <!-- Toast Notification Container -->
    <div class="absolute top-0 right-0 p-4 space-y-2 z-50" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
        <!-- Toast for Validation Errors -->
        @if ($errors->any())
            <div id="toast-validation-error" class="flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert" style="transition: all 0.3s ease-out;">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z"/>
                    </svg>
                    <span class="sr-only">Error icon</span>
                </div>
                <div class="ml-3 text-sm font-normal">
                    <span class="mb-1 text-sm font-semibold text-gray-900">Data tidak valid!</span>
                    <ul class="mt-1.5 ml-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" onclick="closeToast('toast-validation-error')" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" aria-label="Close" style="cursor: pointer;">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Toast for General Errors -->
        @if (session('error'))
            <div id="toast-danger" class="flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert" style="transition: all 0.3s ease-out;">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z"/>
                    </svg>
                    <span class="sr-only">Error icon</span>
                </div>
                <div class="ml-3 text-sm font-normal">{{ session('error') }}</div>
                <button type="button" onclick="closeToast('toast-danger')" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" aria-label="Close" style="cursor: pointer;">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                Pendaftaran Siswa Baru
            </h1>
            <p class="text-gray-600 mt-2">Lengkapi formulir pendaftaran siswa dengan data yang valid</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('admin.siswa.index') }}" class="flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali ke Daftar Siswa
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <form id="createSiswaForm" action="{{ route('admin.siswa.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <!-- Section 1: Data Pribadi & Foto -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Data Pribadi & Foto Wajah
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        {{-- Nama, NISN, Tanggal Lahir, Jenis Kelamin --}}
                        <div>
                            <label for="nama_siswa" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" id="nama_siswa" name="nama_siswa" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('nama_siswa') }}" placeholder="Masukkan nama lengkap">
                            @error('nama_siswa') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="nomer_absen" class="block text-sm font-medium text-gray-700 mb-1">Nomer Absen <span class="text-red-500">*</span></label>
                            <input type="number" id="nomer_absen" name="nomer_absen" required min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('nomer_absen') }}" placeholder="Masukkan nomer absen">
                            <p class="mt-1 text-xs text-gray-500">Nomer absen harus unik dalam satu kelas</p>
                            @error('nomer_absen') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="nisn" class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                            <input type="number" id="nisn" name="nisn" maxlength="10" oninput="if(this.value.length > 10) this.value = this.value.slice(0,10);" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('nisn') }}" placeholder="Nomor Induk Siswa Nasional">
                            <p class="mt-1 text-xs text-gray-500">Masukkan 10 digit NISN</p>
                            @error('nisn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('tanggal_lahir') }}">
                            @error('tanggal_lahir') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <div class="mt-2 flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="jenis_kelamin" value="L" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" required {{ old('jenis_kelamin', 'L') == 'L' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">Laki-laki</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="jenis_kelamin" value="P" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">Perempuan</span>
                                </label>
                            </div>
                            @error('jenis_kelamin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Wajah Siswa (Pilih 1-15 foto) <span class="text-red-500">*</span></label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="foto_siswa" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload file</span>
                                        <input id="foto_siswa" name="foto_siswa[]" type="file" class="sr-only" multiple required accept="image/jpeg,image/png,image/jpg">
                                    </label>
                                    <p class="pl-1">atau tarik dan lepas</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, JPEG hingga 2MB per file</p>
                            </div>
                        </div>
                        @error('foto_siswa') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error('foto_siswa.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div id="imagePreviewContainer" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
                    </div>
                </div>
            </div>
            
            <!-- Section 2: Informasi Akademik -->
            <div class="mb-8">
                 <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    Informasi Akademik
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="id_jurusan" class="block text-sm font-medium text-gray-700 mb-1">Jurusan <span class="text-red-500">*</span></label>
                        <select id="id_jurusan" name="id_jurusan" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach($jurusan as $item)
                                <option value="{{ $item->id }}" {{ old('id_jurusan') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_jurusan }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_jurusan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="id_kelas" class="block text-sm font-medium text-gray-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                        <select id="id_kelas" name="id_kelas" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Pilih Kelas --</option>
                             @foreach($kelas as $item)
                                <option value="{{ $item->id }}" {{ old('id_kelas') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_kelas') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Informasi Kontak -->
            <div class="mb-8">
                 <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    Informasi Kontak
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('email') }}" placeholder="email@contoh.com">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                        <input type="tel" id="no_hp" name="no_hp" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('no_hp') }}" placeholder="0812-3456-7890">
                        @error('no_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                        <textarea id="alamat" name="alamat" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Jl. Contoh No. 123, Kota/Kabupaten">{{ old('alamat') }}</textarea>
                        @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6">
                <button type="reset" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset Form
                </button>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Script untuk menampilkan preview dari banyak gambar
document.addEventListener('DOMContentLoaded', function() {
    const fotoInput = document.getElementById('foto_siswa');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            previewContainer.innerHTML = ''; // Bersihkan preview lama setiap kali ada perubahan

            if (this.files) {
                const filesToShow = Array.from(this.files).slice(0, 15);
                filesToShow.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'relative w-full h-32 rounded-lg overflow-hidden shadow-sm border';
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.className = 'w-full h-full object-cover';
                        img.alt = file.name;
                        const caption = document.createElement('div');
                        caption.className = 'absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 truncate';
                        caption.textContent = file.name;
                        wrapper.appendChild(img);
                        wrapper.appendChild(caption);
                        previewContainer.appendChild(wrapper);
                    }
                    reader.readAsDataURL(file);
                });
            }
        });
    }
});

// Simple toast close function
function closeToast(id) {
    const toast = document.getElementById(id);
    if (toast) {
        toast.style.transition = 'all 0.3s ease-out';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100px)';
        setTimeout(function() {
            toast.remove();
        }, 300);
    }
}

// Auto-hide all toasts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const toast1 = document.getElementById('toast-validation-error');
        const toast2 = document.getElementById('toast-danger');
        
        if (toast1) closeToast('toast-validation-error');
        if (toast2) closeToast('toast-danger');
    }, 5000);
});
</script>
@endsection