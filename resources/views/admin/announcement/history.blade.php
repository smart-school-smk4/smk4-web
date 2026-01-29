@extends('layouts.dashboard')

@section('title', 'Riwayat Pengumuman Sekolah')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Riwayat Pengumuman</h1>
            <p class="text-sm text-gray-600 mt-1">Daftar lengkap pengumuman yang telah dikirim</p>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.announcement.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Buat Baru
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-8 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                </svg>
                Filter Riwayat
            </h2>
        </div>
        
        <form id="filterForm" method="GET" action="{{ route('admin.announcement.history') }}" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <!-- Dari Tanggal -->
                <div class="md:col-span-5">
                    <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" 
                           class="block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 py-3 px-4 transition duration-200 bg-gray-50 hover:bg-white">
                </div>
                
                <!-- Sampai Tanggal -->
                <div class="md:col-span-5">
                    <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" 
                           class="block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 py-3 px-4 transition duration-200 bg-gray-50 hover:bg-white">
                </div>
                
                <!-- Action Buttons -->
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-semibold hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg" title="Terapkan Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    @if(request()->has('start_date') || request()->has('end_date'))
                    <a href="{{ route('admin.announcement.history') }}" class="inline-flex items-center justify-center px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow" title="Reset Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- History Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-green-50 to-emerald-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Waktu Pengiriman
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Isi Pengumuman
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Ruangan Tujuan
                        </th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider w-32">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($announcements as $announcement)
                    <tr class="hover:bg-green-50 transition duration-200" data-id="{{ $announcement->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-11 w-11 flex items-center justify-center rounded-xl bg-gradient-to-br from-green-100 to-emerald-100 text-green-600 mr-3 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $announcement->formatted_sent_at }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $announcement->sent_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <!-- <td class="px-6 py-4 whitespace-nowrap">
                            @if($announcement->mode === 'tts')
                            <span class="px-2.5 py-0.5 inline-flex items-center text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-robot mr-1.5"></i> TTS
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 inline-flex items-center text-xs leading-4 font-medium rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-microphone-alt mr-1.5"></i> Manual
                            </span>
                            @endif
                        </td> -->
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $announcement->short_message }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($announcement->message, 80) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($announcement->ruangans as $ruangan)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <span class="w-2 h-2 rounded-full mr-1.5 {{ $announcement->mode === 'tts' ? 'bg-green-500' : 'bg-blue-500' }}"></span>
                                    {{ $ruangan->nama_ruangan }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <!-- Detail Button -->
                                <button onclick="showAnnouncementDetails('{{ $announcement->id }}')" 
                                        class="inline-flex items-center px-3 py-1.5 border border-blue-500 text-sm font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </button>
                                
                                <!-- Delete Button -->
                                <button onclick="confirmDelete('{{ $announcement->id }}')" 
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 text-sm">Tidak ada riwayat pengumuman</p>
                                @if(request()->has('mode') || request()->has('start_date') || request()->has('end_date'))
                                <a href="{{ route('admin.announcement.history') }}" class="text-blue-600 hover:text-blue-800 mt-2 text-sm flex items-center">
                                    <i class="fas fa-sync-alt mr-1"></i> Reset filter
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($announcements->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <p class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium">{{ $announcements->firstItem() }}</span> 
                        sampai <span class="font-medium">{{ $announcements->lastItem() }}</span> 
                        dari <span class="font-medium">{{ $announcements->total() }}</span> hasil
                    </p>
                </div>
                <div>
                    {{ $announcements->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Show announcement details in modal
function showAnnouncementDetails(id) {
    $.get(`/admin/announcement/${id}/details`, function(response) {
    const data = response.data; // ambil data sebenarnya dari response
        Swal.fire({
            title: 'Detail Pengumuman',
            html: `
                <div class="text-left space-y-3">
                    <div class="flex items-center">
                        <span class="font-medium w-24">Mode:</span>
                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-4 font-medium rounded-full ${data.mode === 'tts' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                            <i class="fas ${data.mode === 'tts' ? 'fa-robot' : 'fa-microphone-alt'} mr-1"></i>
                            ${data.mode === 'tts' ? 'Text-to-Speech' : 'Manual'}
                        </span>
                    </div>
                    <div>
                        <span class="font-medium w-24">Ruangan:</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            ${data.ruangans.map(r => `
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <span class="w-2 h-2 rounded-full mr-1.5 ${data.mode === 'tts' ? 'bg-green-500' : 'bg-blue-500'}"></span>
                                    ${r.nama_ruangan}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-gray-50 rounded border border-gray-200">
                        <p class="font-medium text-sm">Isi Pengumuman:</p>
                        <p class="mt-1 text-sm whitespace-pre-line">${data.message}</p>
                    </div>
                </div>
            `,
            confirmButtonText: 'Tutup',
            width: '600px',
            customClass: {
                confirmButton: 'px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium'
            }
        });
    });
}

// Confirm deletion
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Pengumuman?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/announcement/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Pengumuman telah dihapus.',
                        icon: 'success',
                        timer: 1500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        willClose: () => {
                            location.reload();
                        }
                    });
                },
                error: function() {
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menghapus pengumuman.',
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                }
            });
        }
    });
}

// Date range validation
document.getElementById('filterForm').addEventListener('submit', function(e) {
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(document.getElementById('end_date').value);
    
    if (document.getElementById('start_date').value && document.getElementById('end_date').value && startDate > endDate) {
        e.preventDefault();
        Swal.fire({
            title: 'Tanggal Tidak Valid',
            text: 'Tanggal akhir tidak boleh sebelum tanggal awal',
            icon: 'error',
            confirmButtonText: 'Tutup'
        });
    }
});
</script>
@endsection