@extends('layouts.dashboard')

@section('title', 'Smart School | Edit Kelas')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-md overflow-hidden max-w-2xl mx-auto">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h1 class="text-xl font-semibold text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit Data Kelas
            </h1>
        </div>

        <!-- Form Section -->
        <div class="p-6">
            <form id="editForm" action="{{ route('admin.kelas.update', $kelas->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nama Kelas -->
                <div class="mb-6">
                    <label for="nama_kelas" class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas</label>
                    <input type="text" name="nama_kelas" id="nama_kelas" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-300"
                           value="{{ old('nama_kelas', $kelas->nama_kelas) }}"
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
                            <option value="{{ $item->id }}" {{ old('id_jurusan', $kelas->id_jurusan) == $item->id ? 'selected' : '' }}>
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
                    <button type="button" onclick="confirmUpdate()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 shadow-md hover:shadow-lg flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmUpdate() {
    const form = document.getElementById('editForm');
    const namaKelas = document.getElementById('nama_kelas').value;
    const jurusan = document.getElementById('id_jurusan').options[document.getElementById('id_jurusan').selectedIndex].text;

    Swal.fire({
        title: 'Update Data Kelas?',
        html: `Anda akan mengupdate kelas menjadi:<br>
               <strong>${namaKelas}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Update!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Memperbarui...',
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