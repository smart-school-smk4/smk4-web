@extends('layouts.dashboard')

@section('title', 'Smart School | Manajemen Ruangan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section with Gradient -->
    <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl shadow-xl p-8 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="text-white">
                <h1 class="text-3xl font-bold mb-2">Manajemen Ruangan</h1>
                <p class="text-primary-100">Kelola data ruangan kelas dan jurusan dengan mudah</p>
            </div>
            
            <a href="{{ route('admin.ruangan.create') }}" 
               class="bg-white text-primary-600 hover:bg-primary-50 px-6 py-3 rounded-xl font-semibold flex items-center transition duration-200 shadow-lg hover:shadow-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Ruangan
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Ruangan -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 transform hover:scale-105 transition duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-white/20 backdrop-blur-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-purple-100">Total Ruangan</h3>
                    <p class="text-3xl font-bold text-white">{{ $ruangan->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Search Section -->
        <div class="p-6 border-b border-gray-100">
            <form action="{{ route('admin.ruangan.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" id="search" name="search" placeholder="Cari nama ruangan..." 
                               value="{{ request('search') }}"
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                @if(request('search'))
                    <a href="{{ route('admin.ruangan.index') }}" 
                       class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition duration-200 flex items-center justify-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Ruangan Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Ruangan</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Kelas</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Jurusan</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($ruangan as $index => $item)
                        <tr class="hover:bg-blue-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-700">{{ $index + 1 }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">Ruang{{ $item->nama_ruangan }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg bg-blue-100 text-blue-700">
                                    {{ $item->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg bg-green-100 text-green-700">
                                    {{ $item->jurusan->nama_jurusan ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.ruangan.edit', $item->id) }}\" 
                                       class="p-2 text-primary-600 hover:text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition duration-200"
                                       title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    
                                    <form action="{{ route('admin.ruangan.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('{{ $item->nama_ruangan }}', this.form)" 
                                                class="p-2 text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition duration-200"
                                                title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Belum ada data ruangan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(name, form) {
    Swal.fire({
        title: 'Konfirmasi Penghapusan',
        html: `Anda yakin ingin menghapus ruangan <strong>${name}</strong>?`,
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
                text: 'Ruangan berhasil dihapus.',
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