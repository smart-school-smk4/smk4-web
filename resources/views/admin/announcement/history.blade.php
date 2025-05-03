@extends('layouts.dashboard')

@section('title', 'Riwayat Pengumuman')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Riwayat Pengumuman</h1>
            <p class="text-gray-600 mt-2">Daftar seluruh pengumuman yang pernah dikirim</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('announcement.index') }}" 
               class="flex items-center px-5 py-2.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition duration-300 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 border border-gray-100">
        <div class="p-6">
            <form action="{{ route('admin.announcement.history') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-gray-700 font-medium mb-2">Cari</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 pr-10"
                                   placeholder="Cari pengumuman atau ruangan...">
                            <button type="submit" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Mode Filter -->
                    <div>
                        <label for="mode" class="block text-gray-700 font-medium mb-2">Jenis Pengumuman</label>
                        <div class="relative">
                            <select name="mode" id="mode"
                                    class="appearance-none w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 pr-10 bg-white">
                                <option value="">Semua Jenis</option>
                                <option value="reguler" {{ request('mode') == 'reguler' ? 'selected' : '' }}>Aktivasi Ruangan</option>
                                <option value="tts" {{ request('mode') == 'tts' ? 'selected' : '' }}>Pengumuman Suara (TTS)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reset Button -->
                    <div class="flex items-end">
                        <a href="{{ route('admin.announcement.history') }}" 
                           class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-300 flex items-center">
                            <i class="fas fa-sync-alt mr-2"></i> Reset Filter
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Announcements Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <!-- Table Header -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <h2 class="text-lg font-semibold text-gray-800">Daftar Pengumuman</h2>
                <div class="mt-2 md:mt-0 text-sm">
                    Menampilkan {{ $announcements->count() }} dari {{ $announcements->total() }} pengumuman
                </div>
            </div>
        </div>
        
        <!-- Table Body -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jenis
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Konten
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ruangan Tujuan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Waktu
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($announcements as $announcement)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <!-- Jenis -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($announcement->mode === 'reguler')
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-door-open text-blue-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Aktivasi</span>
                                @else
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-volume-up text-purple-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">TTS</span>
                                @endif
                            </div>
                        </td>
                        
                        <!-- Konten -->
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs truncate">
                                @if($announcement->mode === 'tts')
                                    {{ $announcement->message }}
                                @else
                                    {{ $announcement->is_active ? 'Aktivasi ruangan' : 'Deaktivasi ruangan' }}
                                    <div class="text-xs text-gray-500 mt-1">
                                        Status: {{ $announcement->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                    </div>
                                @endif
                            </div>
                            @if($announcement->mode === 'tts')
                            <div class="mt-1">
                                <audio controls class="h-8">
                                    <source src="{{ asset('storage/' . $announcement->audio_path) }}" type="audio/wav">
                                </audio>
                            </div>
                            @endif
                        </td>
                        
                        <!-- Ruangan -->
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">
                                {{ $announcement->ruangans->count() }} ruangan
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                @foreach($announcement->ruangans->take(3) as $ruangan)
                                    {{ $ruangan->nama_ruangan }}@if(!$loop->last), @endif
                                @endforeach
                                @if($announcement->ruangans->count() > 3)
                                    +{{ $announcement->ruangans->count() - 3 }} lainnya
                                @endif
                            </div>
                        </td>
                        
                        <!-- Waktu -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $announcement->sent_at->format('d M Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $announcement->sent_at->format('H:i') }}
                            </div>
                        </td>
                        
                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($announcement->status === 'delivered')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Terkirim
                            </span>
                            @elseif($announcement->status === 'failed')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i> Gagal
                            </span>
                            @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Proses
                            </span>
                            @endif
                        </td>
                        
                        <!-- Aksi -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="dropdown relative inline-block">
                                <button class="dropdown-toggle p-1 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition duration-200">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu absolute right-0 mt-1 w-40 bg-white rounded-md shadow-lg py-1 z-10 hidden border border-gray-200">
                                    <a href="{{ route('admin.announcement.show', $announcement->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-eye mr-2"></i> Detail
                                    </a>
                                    <form action="{{ route('admin.announcement.destroy', $announcement->id) }}" method="POST" class="block w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete(this)" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <i class="fas fa-trash-alt mr-2"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-bullhorn text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-700">Belum Ada Pengumuman</h3>
                            <p class="text-gray-500 mt-1">Tidak ada riwayat pengumuman yang ditemukan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $announcements->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirm delete function
    function confirmDelete(form) {
        Swal.fire({
            title: 'Hapus Pengumuman?',
            text: "Anda tidak akan bisa mengembalikan data ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#6366f1',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            backdrop: 'rgba(99, 102, 241, 0.1)'
        }).then((result) => {
            if (result.isConfirmed) {
                form.closest('form').submit();
            }
        });
    }

    // Dropdown menu handler
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        } else {
            const dropdown = e.target.closest('.dropdown');
            const menu = dropdown.querySelector('.dropdown-menu');
            menu.classList.toggle('hidden');
        }
    });
});
</script>
@endsection