@extends('layouts.dashboard')

@section('title', 'Riwayat Pengumuman - Smart School')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Riwayat Pengumuman</h1>
        <a href="{{ route('admin.announcement.index') }}" 
           class="flex items-center mt-4 md:mt-0 px-4 py-2 bg-white border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
            <i class="fas fa-plus-circle mr-2"></i> Buat Pengumuman Baru
        </a>
    </div>

    <!-- Enhanced Filter Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-filter mr-2 text-blue-500"></i> Filter Riwayat
            </h2>
        </div>
        <div class="p-4">
            <form id="filterForm" method="GET" action="{{ route('admin.announcement.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="mode" class="block text-sm font-medium text-gray-700 mb-1">Mode Pengumuman</label>
                    <div class="relative">
                        <select id="mode" name="mode" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border">
                            <option value="">Semua Mode</option>
                            <option value="tts" {{ request('mode') == 'tts' ? 'selected' : '' }}>Text-to-Speech</option>
                            <option value="manual" {{ request('mode') == 'manual' ? 'selected' : '' }}>Manual</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-caret-down text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <div class="relative">
                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" 
                               class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <div class="relative">
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" 
                               class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="flex items-end space-x-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 h-[42px] transition-colors">
                        <i class="fas fa-search mr-2"></i> Terapkan Filter
                    </button>
                    @if(request()->has('mode') || request()->has('start_date') || request()->has('end_date'))
                    <a href="{{ route('admin.announcement.history') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 h-[42px] transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i> Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- History Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            #
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Waktu Pengiriman
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mode
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Isi Pengumuman
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ruangan Tujuan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($announcements as $index => $announcement)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $index + 1 + (($announcements->currentPage() - 1) * $announcements->perPage()) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $announcement->formatted_sent_at }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $announcement->sent_at->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($announcement->mode === 'tts')
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-robot mr-1"></i> TTS
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-4 font-medium rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-microphone-alt mr-1"></i> Manual
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $announcement->short_message }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($announcement->message, 80) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($announcement->ruangans as $ruangan)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1 {{ $announcement->mode === 'tts' ? 'bg-green-500' : 'bg-blue-500' }}"></span>
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
                                <i class="fas fa-inbox text-3xl text-gray-300 mb-3"></i>
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
    $.get(`/admin/announcement/${id}/details`, function(data) {
        Swal.fire({
            title: 'Detail Pengumuman',
            html: `
                <div class="text-left space-y-3">
                    <div class="flex items-center">
                        <span class="font-medium w-24">Mode:</span>
                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-4 font-medium rounded-full ${data.mode === 'tts' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                            ${data.mode === 'tts' ? 'Text-to-Speech' : 'Manual'}
                        </span>
                    </div>
                    <div>
                        <span class="font-medium w-24">Waktu:</span>
                        <span>${data.formatted_sent_at}</span>
                    </div>
                    <div>
                        <span class="font-medium w-24">Ruangan:</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            ${data.ruangans.map(r => `
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1 ${data.mode === 'tts' ? 'bg-green-500' : 'bg-blue-500'}"></span>
                                    ${r.nama_ruangan}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-gray-50 rounded border border-gray-200">
                        <p class="font-medium text-sm">Isi Pengumuman:</p>
                        <p class="mt-1 text-sm">${data.message}</p>
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