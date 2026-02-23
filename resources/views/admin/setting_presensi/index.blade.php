@extends('layouts.dashboard')

@section('title', 'Smart School | Setting Waktu Presensi')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-3"></div>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">⏱️ Pengaturan Waktu Presensi</h1>
        <a href="{{ route('admin.setting_presensi.create') }}" class="px-4 py-2 font-bold text-white bg-blue-500 rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75 transition duration-300">
            + Tambah Pengaturan
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-green-700 bg-green-100 border-l-4 border-green-500 rounded-md shadow-sm" role="alert">
            <p class="font-bold">Sukses!</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Status Mode Device (Display Only - Otomatis) -->
    <div class="mb-6 overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg shadow-lg border border-blue-200">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Status Sistem Presensi
            </h2>
            <p class="text-blue-100 text-sm mt-1">Status otomatis berdasarkan jadwal waktu yang telah diatur untuk semua device</p>
        </div>
        <div class="p-8">
            <div class="max-w-2xl mx-auto">
                <!-- Status keseluruhan sistem -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-gray-200" id="system-status">
                    <div class="flex items-center justify-center p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-blue-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-semibold text-gray-700">Memuat status sistem...</span>
                        </div>
                    </div>
                </div>
                
                @if($devices->count() > 0)
                    <div class="mt-4 text-center text-sm text-gray-600">
                        <p class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $devices->count() }} Device Terhubung</span>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabel Setting Waktu -->
    <div class="overflow-hidden bg-white rounded-lg shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Waktu Masuk (Mulai - Selesai)</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Waktu Pulang (Mulai - Selesai)</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($settings as $setting)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-sm font-semibold text-green-800 bg-green-100 rounded-full">{{ \Carbon\Carbon::parse($setting->waktu_masuk_mulai)->format('H:i') }}</span>
                             - 
                            <span class="px-3 py-1 text-sm font-semibold text-red-800 bg-red-100 rounded-full">{{ \Carbon\Carbon::parse($setting->waktu_masuk_selesai)->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-sm font-semibold text-green-800 bg-green-100 rounded-full">{{ \Carbon\Carbon::parse($setting->waktu_pulang_mulai)->format('H:i') }}</span>
                             - 
                            <span class="px-3 py-1 text-sm font-semibold text-red-800 bg-red-100 rounded-full">{{ \Carbon\Carbon::parse($setting->waktu_pulang_selesai)->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.setting_presensi.edit', $setting->id) }}" class="px-3 py-1 text-xs font-semibold text-white bg-yellow-500 rounded-md shadow-sm hover:bg-yellow-600 transition">Edit</a>
                                <form action="{{ route('admin.setting_presensi.destroy', $setting->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengaturan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded-md shadow-sm hover:bg-red-600 transition">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                            Belum ada pengaturan waktu presensi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// CSRF Token untuk request Laravel
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// Update status sistem presensi (keseluruhan, bukan per device)
function updateSystemStatus() {
    // Ambil status dari device pertama (karena setting berlaku untuk semua device)
    @if($devices->count() > 0)
        const firstDeviceId = {{ $devices->first()->id }};
        
        fetch(`/api/devices/${firstDeviceId}/mode`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('system-status');
            if (data.success && statusDiv) {
                const status = data.status || 'outside_hours';
                const statusMessage = data.status_message || 'Di luar jam presensi';
                const source = data.source || 'auto';
                const isManual = source === 'manual';
                
                let icon, color, bgColor, detailText;
                
                // Tentukan tampilan berdasarkan status
                switch(status) {
                    case 'checkin_open':
                        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>';
                        color = 'text-green-600';
                        bgColor = 'from-green-50 to-green-100';
                        detailText = 'Siswa dapat melakukan presensi masuk';
                        break;
                    case 'checkout_open':
                        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414 0l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 13H13a1 1 0 100-2H9.414l1.293-1.293a1 1 0 000-1.414z" clip-rule="evenodd"></path>';
                        color = 'text-red-600';
                        bgColor = 'from-red-50 to-red-100';
                        detailText = 'Siswa dapat melakukan presensi keluar';
                        break;
                    case 'before_checkin':
                        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>';
                        color = 'text-orange-600';
                        bgColor = 'from-orange-50 to-orange-100';
                        detailText = 'Menunggu waktu presensi masuk dibuka';
                        break;
                    case 'between_shifts':
                        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>';
                        color = 'text-blue-600';
                        bgColor = 'from-blue-50 to-blue-100';
                        detailText = 'Kegiatan pembelajaran sedang berlangsung';
                        break;
                    case 'after_checkout':
                        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.93-9.412l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 8.588zM9 4.5a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd"></path>';
                        color = 'text-gray-600';
                        bgColor = 'from-gray-50 to-gray-100';
                        detailText = 'Waktu presensi hari ini telah berakhir';
                        break;
                    case 'manual_override':
                        icon = '<path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>';
                        color = 'text-purple-600';
                        bgColor = 'from-purple-50 to-purple-100';
                        detailText = 'Mode diatur secara manual oleh admin';
                        break;
                    default:
                        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
                        color = 'text-gray-600';
                        bgColor = 'from-gray-50 to-gray-100';
                        detailText = 'Tidak ada aktivitas presensi saat ini';
                }
                
                // Tampilkan jadwal jika ada
                let scheduleInfo = '';
                if (data.schedule) {
                    const sched = data.schedule;
                    scheduleInfo = `
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="bg-green-50 rounded-lg p-3">
                                    <p class="text-xs text-green-700 font-semibold mb-1">Waktu Masuk</p>
                                    <p class="text-green-800 font-bold">${sched.waktu_masuk_mulai?.substring(0,5) || '-'} - ${sched.waktu_masuk_selesai?.substring(0,5) || '-'}</p>
                                </div>
                                <div class="bg-red-50 rounded-lg p-3">
                                    <p class="text-xs text-red-700 font-semibold mb-1">Waktu Keluar</p>
                                    <p class="text-red-800 font-bold">${sched.waktu_pulang_mulai?.substring(0,5) || '-'} - ${sched.waktu_pulang_selesai?.substring(0,5) || '-'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                statusDiv.innerHTML = `
                    <div class="flex flex-col items-center justify-center p-6 bg-gradient-to-r ${bgColor} rounded-lg">
                        <div class="flex items-center gap-3 mb-2">
                            <svg class="w-8 h-8 ${color}" fill="currentColor" viewBox="0 0 20 20">
                                ${icon}
                            </svg>
                            <span class="text-2xl font-bold ${color}">${statusMessage.toUpperCase()}</span>
                        </div>
                        <p class="text-sm text-gray-600 text-center">${detailText}</p>
                        <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                            ${isManual ? 
                                '<span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full font-semibold">🔧 Mode Manual</span>' : 
                                '<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full font-semibold">⏰ Mode Otomatis</span>'
                            }
                        </div>
                    </div>
                    ${scheduleInfo}
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching system status:', error);
            const statusDiv = document.getElementById('system-status');
            if (statusDiv) {
                statusDiv.innerHTML = `
                    <div class="flex items-center justify-center p-6 bg-gradient-to-r from-red-50 to-red-100 rounded-lg">
                        <div class="text-center">
                            <svg class="w-8 h-8 text-red-600 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-red-600 font-semibold">Gagal memuat status sistem</p>
                            <p class="text-sm text-red-500 mt-1">Pastikan koneksi server stabil</p>
                        </div>
                    </div>
                `;
            }
        });
    @else
        // Jika tidak ada device, tampilkan pesan
        const statusDiv = document.getElementById('system-status');
        if (statusDiv) {
            statusDiv.innerHTML = `
                <div class="flex items-center justify-center p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-600 font-semibold">Tidak ada device terdaftar</p>
                        <p class="text-sm text-gray-500 mt-1">Tambahkan device face recognition terlebih dahulu</p>
                    </div>
                </div>
            `;
        }
    @endif
}

// Update status saat halaman dimuat dan setiap 30 detik
document.addEventListener('DOMContentLoaded', function() {
    updateSystemStatus();
    setInterval(updateSystemStatus, 30000); // Update setiap 30 detik
});

function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    
    // Warna berdasarkan tipe
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    const icons = {
        success: '✅',
        error: '❌',
        info: 'ℹ️',
        warning: '⚠️'
    };
    
    const toast = document.createElement('div');
    toast.className = `${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 transform transition-all duration-300 ease-in-out`;
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    
    toast.innerHTML = `
        <span class="text-2xl">${icons[type]}</span>
        <span class="font-semibold">${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}
</script>

@endsection