@extends('layouts.dashboard')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="flex items-center space-x-3">
            <div class="p-3 rounded-xl bg-blue-100/80 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Riwayat Pengumuman</h1>
                <p class="text-sm text-gray-500">Daftar semua pengumuman yang pernah dikirim</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('announcements.index') }}" class="px-3 py-1 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                Kembali ke Pengumuman
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search Box -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Pengumuman</label>
                <div class="relative">
                    <input type="text" id="search" name="search" placeholder="Kata kunci..." class="block w-full pl-4 pr-10 py-2 border border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-150 ease-in-out">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Type Filter -->
            <div>
                <label for="type-filter" class="block text-sm font-medium text-gray-700 mb-1">Jenis Pengumuman</label>
                <select id="type-filter" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-150 ease-in-out">
                    <option value="">Semua Jenis</option>
                    <option value="tts">Text-to-Speech</option>
                    <option value="manual">Manual</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status-filter" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-150 ease-in-out">
                    <option value="">Semua Status</option>
                    <option value="completed">Selesai</option>
                    <option value="stopped">Dihentikan</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Announcement History Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Isi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="history-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be loaded via AJAX -->
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-500" id="pagination-info">
                Menampilkan 0 sampai 0 dari 0 entri
            </div>
            <div class="flex space-x-2" id="pagination-controls">
                <button id="prev-page" disabled class="px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-500 cursor-not-allowed">
                    Sebelumnya
                </button>
                <button id="next-page" disabled class="px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-500 cursor-not-allowed">
                    Selanjutnya
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="details-modal" class="hidden fixed inset-0 overflow-y-auto z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-white" id="modal-title">Detail Pengumuman</h3>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">ID Pengumuman</h4>
                        <p class="mt-1 text-sm text-gray-900" id="detail-id">-</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Waktu Pengiriman</h4>
                        <p class="mt-1 text-sm text-gray-900" id="detail-time">-</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Jenis</h4>
                        <p class="mt-1 text-sm text-gray-900" id="detail-type">-</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Status</h4>
                        <p class="mt-1 text-sm text-gray-900" id="detail-status">-</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Isi Pengumuman</h4>
                        <p class="mt-1 text-sm text-gray-900" id="detail-content">-</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Ruangan Tujuan</h4>
                        <div class="mt-1 flex flex-wrap gap-2" id="detail-rooms">
                            <!-- Room badges will appear here -->
                        </div>
                    </div>
                    <div id="audio-section" class="hidden">
                        <h4 class="text-sm font-medium text-gray-500">Audio</h4>
                        <audio controls class="mt-2 w-full" id="audio-player">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end">
                <button type="button" id="close-modal" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const baseUrl = '/admin/pengumuman';
    let currentPage = 1;
    let lastPage = 1;
    let loading = false;

    // Initialize the page
    function init() {
        loadHistory();
        setupEventListeners();
    }

    // Set up event listeners
    function setupEventListeners() {
        // Filter changes
        $('#search, #type-filter, #status-filter').on('change keyup', function() {
            currentPage = 1;
            loadHistory();
        });

        // Pagination controls
        $('#prev-page').click(function() {
            if (currentPage > 1) {
                currentPage--;
                loadHistory();
            }
        });

        $('#next-page').click(function() {
            if (currentPage < lastPage) {
                currentPage++;
                loadHistory();
            }
        });

        // Modal close button
        $('#close-modal').click(function() {
            $('#details-modal').addClass('hidden');
        });
    }

    // Load history data
    function loadHistory() {
        if (loading) return;
        loading = true;

        // Show loading state
        $('#history-table-body').html(`
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                    Memuat data...
                </td>
            </tr>
        `);

        const filters = {
            search: $('#search').val(),
            filter: $('#type-filter').val(),
            status: $('#status-filter').val(),
            page: currentPage
        };

        $.get(`${baseUrl}/history`, filters, function(response) {
            renderTable(response);
            updatePagination(response);
        }).fail(function() {
            $('#history-table-body').html(`
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-red-500">
                        Gagal memuat data. Silakan coba lagi.
                    </td>
                </tr>
            `);
        }).always(function() {
            loading = false;
        });
    }

    // Render table data
    function renderTable(data) {
        if (data.data.length === 0) {
            $('#history-table-body').html(`
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                        Tidak ada data yang ditemukan
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        data.data.forEach(item => {
            // Format time
            const sentAt = new Date(item.sent_at);
            const timeString = sentAt.toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Format type
            const typeBadge = item.type === 'tts' ? 
                '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">TTS</span>' :
                '<span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Manual</span>';

            // Format status
            let statusBadge;
            if (item.status === 'completed') {
                statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Selesai</span>';
            } else {
                statusBadge = '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Dihentikan</span>';
            }

            // Truncate content if too long
            const content = item.content ? 
                (item.content.length > 50 ? item.content.substring(0, 50) + '...' : item.content) :
                '-';

            // Room badges (show first 2 only)
            let roomsHtml = '';
            if (item.target_ruangans && item.target_ruangans.length > 0) {
                const roomsToShow = item.target_ruangans.slice(0, 2);
                roomsToShow.forEach(room => {
                    roomsHtml += `<span class="inline-block px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 mr-1">${room}</span>`;
                });
                if (item.target_ruangans.length > 2) {
                    roomsHtml += `<span class="inline-block px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">+${item.target_ruangans.length - 2}</span>`;
                }
            }

            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${timeString}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${typeBadge}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${content}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${roomsHtml || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${statusBadge}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="showDetails(${item.id})" class="text-blue-600 hover:text-blue-900 mr-3">Detail</button>
                    </td>
                </tr>
            `;
        });

        $('#history-table-body').html(html);
    }

    // Update pagination controls
    function updatePagination(data) {
        lastPage = data.last_page;
        
        // Update pagination info
        $('#pagination-info').text(
            `Menampilkan ${data.from || 0} sampai ${data.to || 0} dari ${data.total || 0} entri`
        );

        // Update previous button
        $('#prev-page').prop('disabled', currentPage === 1);
        if (currentPage === 1) {
            $('#prev-page').addClass('bg-gray-100 text-gray-500 cursor-not-allowed')
                .removeClass('bg-white text-gray-700 hover:bg-gray-50');
        } else {
            $('#prev-page').addClass('bg-white text-gray-700 hover:bg-gray-50')
                .removeClass('bg-gray-100 text-gray-500 cursor-not-allowed');
        }

        // Update next button
        $('#next-page').prop('disabled', currentPage === lastPage);
        if (currentPage === lastPage) {
            $('#next-page').addClass('bg-gray-100 text-gray-500 cursor-not-allowed')
                .removeClass('bg-white text-gray-700 hover:bg-gray-50');
        } else {
            $('#next-page').addClass('bg-white text-gray-700 hover:bg-gray-50')
                .removeClass('bg-gray-100 text-gray-500 cursor-not-allowed');
        }
    }

    // Show announcement details
    window.showDetails = function(id) {
        $.get(`${baseUrl}/${id}`, function(response) {
            // Format time
            const sentAt = new Date(response.sent_at);
            const timeString = sentAt.toLocaleString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            // Set basic info
            $('#detail-id').text(response.id);
            $('#detail-time').text(timeString);
            $('#detail-type').text(response.type === 'tts' ? 'Text-to-Speech' : 'Manual');
            $('#detail-status').text(response.status === 'completed' ? 'Selesai' : 'Dihentikan');
            $('#detail-content').text(response.content || '-');

            // Set rooms
            let roomsHtml = '';
            if (response.target_ruangans && response.target_ruangans.length > 0) {
                response.target_ruangans.forEach(room => {
                    roomsHtml += `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${room}</span>`;
                });
            }
            $('#detail-rooms').html(roomsHtml || '-');

            // Handle audio for TTS
            if (response.type === 'tts' && response.audio_url) {
                $('#audio-section').removeClass('hidden');
                $('#audio-player').attr('src', response.audio_url);
            } else {
                $('#audio-section').addClass('hidden');
            }

            // Show modal
            $('#details-modal').removeClass('hidden');
        }).fail(function() {
            alert('Gagal memuat detail pengumuman');
        });
    }

    // Initialize the page
    init();
});
</script>
@endsection