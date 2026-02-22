@extends('layouts.dashboard')

@section('title', 'Smart School | Data Siswa')

@section('content')
<div class="container mx-auto px-4 py-6 relative">

    <!-- Toast Notification Container -->
    <div class="absolute top-4 right-4 space-y-3 z-50">
        <!-- Toast for Success -->
        @if (session('success'))
            <div id="toast-success" class="flex items-center w-full max-w-xs p-4 mb-4 text-gray-700 bg-white rounded-xl shadow-xl border border-green-200" role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 text-green-600 bg-green-100 rounded-xl">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 text-sm font-semibold">{{ session('success') }}</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#toast-success" aria-label="Close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif
        
        <!-- Toast for General Errors -->
        @if (session('error'))
            <div id="toast-danger" class="flex items-center w-full max-w-xs p-4 mb-4 text-gray-700 bg-white rounded-xl shadow-xl border border-red-200" role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 text-red-600 bg-red-100 rounded-xl">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 text-sm font-semibold">{{ session('error') }}</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#toast-danger" aria-label="Close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-3xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        Data Siswa
                    </h1>
                    <p class="text-blue-100 mt-1 text-lg">Total {{ $siswa->total() }} siswa terdaftar</p>
                </div>
                <div>
                    <a href="{{ route('admin.siswa.create') }}" class="flex items-center px-6 py-3 bg-white text-primary-600 rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transform transition duration-200 font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Tambah Siswa
                    </a>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="p-8">
            <!-- Search and Filter -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Form untuk Search, Kelas, dan Jurusan --}}
                <form action="{{ route('admin.siswa.index') }}" method="GET" class="flex flex-col md:flex-row md:items-center gap-4 w-full">
                    <div class="relative w-full md:w-80">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" placeholder="Cari siswa..." value="{{ request('search') }}"
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition duration-200 outline-none">
                    </div>
                    
                    <div class="flex items-center gap-3 flex-wrap">
                        <select name="kelas" class="px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition duration-200 outline-none">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $item)
                                <option value="{{ $item->id }}" {{ request('kelas') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        
                        <select name="jurusan" class="px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition duration-200 outline-none">
                            <option value="">Semua Jurusan</option>
                            @foreach($jurusan as $item)
                                <option value="{{ $item->id }}" {{ request('jurusan') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_jurusan }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="px-6 py-3 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition duration-200">Filter</button>

                        @if(request('search') || request('kelas') || request('jurusan'))
                            <a href="{{ route('admin.siswa.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition duration-200">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Students Table -->
            <div class="overflow-hidden rounded-xl border border-gray-200 shadow-md">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <a href="{{ route('admin.siswa.index', array_merge(request()->query(), ['sort' => 'nomer_absen', 'order' => (request('sort') == 'nomer_absen' && request('order') == 'asc') ? 'desc' : 'asc'])) }}" class="flex items-center hover:text-primary-600">
                                        No. Absen
                                        @if(request('sort') == 'nomer_absen')
                                            <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                @if(request('order') == 'asc')
                                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                                @else
                                                    <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/>
                                                @endif
                                            </svg>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Kelas/Jurusan</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Foto</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Siswa</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">NISN</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Kontak</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($siswa as $item)
                            <tr class="hover:bg-blue-50 transition duration-150">
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-bold text-primary-700 bg-primary-100 rounded-lg">
                                        {{ $item->nomer_absen ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->kelas->nama_kelas ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $item->jurusan->nama_jurusan ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-xl overflow-hidden border-2 border-gray-200 shadow-sm">
                                        {{-- Mengambil foto pertama dari relasi 'fotos' --}}
                                        @if($item->fotos && $item->fotos->isNotEmpty())
                                            <img class="h-full w-full object-cover" src="{{ asset('storage/' . $item->fotos->first()->path) }}" alt="Foto {{ $item->nama_siswa }}">
                                        @else
                                            <div class="bg-gradient-to-br from-gray-100 to-gray-200 h-full w-full flex items-center justify-center text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $item->nama_siswa }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }} • {{ \Carbon\Carbon::parse($item->tanggal_lahir)->age }} tahun</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-700">{{ $item->nisn }}</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex items-center mb-1">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $item->email }}
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $item->no_hp }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('admin.siswa.detail', $item->id) }}" class="p-2 text-gray-600 hover:text-primary-600 bg-gray-100 hover:bg-primary-50 rounded-lg transition duration-200" title="Lihat Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.siswa.edit', $item->id) }}" class="p-2 text-primary-600 hover:text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition duration-200" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                        <form id="delete-form-{{ $item->id }}" action="{{ route('admin.siswa.destroy', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('{{ $item->id }}', '{{ $item->nama_siswa }}')" class="p-2 text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition duration-200" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p class="text-gray-500 font-medium">Tidak ada data siswa ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $siswa->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Fungsi untuk close toast
function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.style.transition = 'all 0.3s ease-out';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100px)';
        setTimeout(function() {
            toast.remove();
        }, 300);
    }
}

// Event listener untuk tombol close manual
document.addEventListener('DOMContentLoaded', function() {
    // Attach event listeners ke semua tombol close
    const closeButtons = document.querySelectorAll('[data-dismiss-target]');
    closeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-dismiss-target').replace('#', '');
            closeToast(targetId);
        });
    });
    
    // Auto-hide toast setelah 5 detik
    setTimeout(function() {
        const toastElements = document.querySelectorAll('[id^="toast-"]');
        toastElements.forEach(function(toast) {
            closeToast(toast.id);
        });
    }, 5000);
});

// Konfirmasi hapus dengan SweetAlert2
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus Siswa?',
        html: `Anda akan menghapus siswa: <strong>${name}</strong><br>Data yang dihapus tidak dapat dikembalikan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    });
}
</script>
@endsection
