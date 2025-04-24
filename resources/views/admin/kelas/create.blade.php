@extends('layouts.dashboard')

@section('title', 'Smart School | Tambah Kelas')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-md overflow-hidden max-w-2xl mx-auto">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h1 class="text-xl font-semibold text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah Kelas Baru
            </h1>
        </div>

        <!-- Form Section -->
        <div class="p-6">
            <form id="createForm" action="{{ route('admin.kelas.store') }}" method="POST">
                @csrf

                <!-- Nama Kelas -->
                <div class="mb-6">
                    <label for="nama_kelas" class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas</label>
                    <input type="text" name="nama_kelas" id="nama_kelas" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-300"
                           value="{{ old('nama_kelas') }}"
                           placeholder="Contoh: X IPA 1" required>
                    @error('nama_kelas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jurusan -->
                <div class="mb-6">
                    <label for="id_jurusan" class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                    <select name="id_jurusan" id="id_jurusan" 
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-300">
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach ($jurusan as $item)
                            <option value="{{ $item->id }}" {{ old('id_jurusan') == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_jurusan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.kelas.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-300 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Batal
                    </a>
                    <button type="button" onclick="confirmCreate()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 shadow-md hover:shadow-lg flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Simpan
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
    const jurusan = document.getElementById('id_jurusan').options[document.getElementById('id_jurusan').selectedIndex].text;

    if (!namaKelas) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Nama kelas harus diisi!',
        });
        return;
    }

    Swal.fire({
        title: 'Tambah Kelas Baru?',
        html: `Anda akan menambahkan kelas:<br><strong>${namaKelas}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Menyimpan...',
                html: 'Mohon tunggu sebentar',
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