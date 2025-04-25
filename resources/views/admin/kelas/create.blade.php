@extends('layouts.dashboard')

@section('title', 'Smart School | Tambah Kelas')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Kelas Baru</h1>
            <div class="flex items-center mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-gray-600">Tambahkan data kelas baru ke sistem</p>
            </div>
        </div>
        
        <a href="{{ route('admin.kelas.index') }}" 
           class="bg-gradient-to-r from-gray-200 to-gray-300 hover:from-gray-300 hover:to-gray-400 text-gray-800 px-6 py-3 rounded-lg flex items-center transition duration-300 shadow-md hover:shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-3xl mx-auto">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <h2 class="text-xl font-semibold text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Form Tambah Kelas
            </h2>
        </div>

        <!-- Form Content -->
        <div class="p-6">
            <form id="createForm" action="{{ route('admin.kelas.store') }}" method="POST">
                @csrf

                <!-- Nama Kelas -->
                <div class="mb-6">
                    <label for="nama_kelas" class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kelas" id="nama_kelas" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-300 @error('nama_kelas') border-red-500 @enderror"
                           value="{{ old('nama_kelas') }}"
                           placeholder="Contoh: X IPA 1" required>
                    @error('nama_kelas')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kode Kelas -->
                <div class="mb-6">
                    <label for="kode_kelas" class="block text-sm font-medium text-gray-700 mb-2">Kode Kelas</label>
                    <input type="text" name="kode_kelas" id="kode_kelas" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-300 @error('kode_kelas') border-red-500 @enderror"
                           value="{{ old('kode_kelas') }}"
                           placeholder="Contoh: XIPA1">
                    @error('kode_kelas')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tingkat -->
                <div class="mb-6">
                    <label for="tingkat" class="block text-sm font-medium text-gray-700 mb-2">Tingkat <span class="text-red-500">*</span></label>
                    <select name="tingkat" id="tingkat" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-300 @error('tingkat') border-red-500 @enderror" required>
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="X" {{ old('tingkat') == 'X' ? 'selected' : '' }}>X</option>
                        <option value="XI" {{ old('tingkat') == 'XI' ? 'selected' : '' }}>XI</option>
                        <option value="XII" {{ old('tingkat') == 'XII' ? 'selected' : '' }}>XII</option>
                    </select>
                    @error('tingkat')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jurusan -->
                <div class="mb-6">
                    <label for="id_jurusan" class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                    <select name="id_jurusan" id="id_jurusan" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-300 @error('id_jurusan') border-red-500 @enderror">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach ($jurusan as $item)
                            <option value="{{ $item->id }}" {{ old('id_jurusan') == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_jurusan')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <button type="reset" 
                            class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-300 flex items-center shadow-sm hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Reset
                    </button>
                    <button type="button" onclick="confirmCreate()" 
                            class="bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white px-6 py-2.5 rounded-lg transition duration-300 shadow-lg hover:shadow-xl flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Simpan Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmCreate() {
    const form = document.getElementById('createForm');
    const namaKelas = document.getElementById('nama_kelas').value;
    const tingkat = document.getElementById('tingkat').value;
    const jurusan = document.getElementById('id_jurusan').options[document.getElementById('id_jurusan').selectedIndex].text;

    if (!namaKelas) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Nama kelas harus diisi!',
        });
        return;
    }

    if (!tingkat) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Tingkat kelas harus dipilih!',
        });
        return;
    }

    Swal.fire({
        title: 'Tambah Kelas Baru?',
        html: `Anda akan menambahkan kelas:<br>
               <strong>${namaKelas}</strong><br>
               Tingkat: <strong>${tingkat}</strong><br>
               Jurusan: <strong>${jurusan || '-'}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal',
        backdrop: `
            rgba(0,0,0,0.7)
            url("https://sweetalert2.github.io/images/nyan-cat.gif")
            left top
            no-repeat
        `
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menyimpan...',
                html: 'Sedang menyimpan data kelas',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    form.submit();
                }
            });
        }
    });
}
</script>
@endsection