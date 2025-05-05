@extends('layouts.dashboard')

@section('title', 'Smart School | Manajemen Jurusan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section with Gradient -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Jurusan</h1>
            <div class="flex items-center mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-gray-600">Kelola data jurusan sekolah dengan mudah</p>
            </div>
        </div>
        
        <a href="{{ route('admin.jurusan.create') }}" 
           class="bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white px-6 py-3 rounded-lg flex items-center transition duration-300 shadow-lg hover:shadow-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Tambah Jurusan Baru
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Jurusan -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-md transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Jurusan</h3>
                    <p class="text-2xl font-bold text-gray-700">{{ $jurusan->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Card Header with Filter -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Daftar Jurusan</h2>
                    <p class="text-sm text-gray-500">Manajemen semua jurusan yang terdaftar</p>
                </div>
                
                <form action="{{ route('admin.jurusan.index') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                    <!-- Search Box -->
                    <div class="w-full md:w-auto flex-grow">
                        <div class="relative">
                            <input type="text" id="search" name="search" placeholder="Cari nama jurusan..." 
                                   value="{{ request('search') }}"
                                   class="block w-full md:w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10 pr-4 py-2">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    @if(request('search'))
                        <div class="flex items-end">
                            <a href="{{ route('admin.jurusan.index') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Jurusan Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Jurusan</th>
                        <!-- <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jumlah Ruangan</th> -->
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($jurusan as $index => $item)
                    <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $index + 1 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $item->nama_jurusan }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('admin.jurusan.edit', $item->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 inline-flex items-center transition duration-150 ease-in-out transform hover:scale-110"
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </a>
                                
                                <form action="{{ route('admin.jurusan.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('{{ $item->nama_jurusan }}', this.form)" 
                                            class="text-red-600 hover:text-red-900 inline-flex items-center transition duration-150 ease-in-out transform hover:scale-110"
                                            title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-lg font-medium">Belum ada data jurusan</h3>
                                <p class="text-sm mt-1">Silakan tambah jurusan baru menggunakan tombol di atas</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($jurusan->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $jurusan->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(name, form) {
    Swal.fire({
        title: 'Konfirmasi Penghapusan',
        html: `Anda yakin ingin menghapus jurusan <strong>${name}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
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
                title: 'Dihapus!',
                text: 'Jurusan berhasil dihapus.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                form.submit();
            });
        }
    });
}
</script>
@endsection