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

    <!-- Kontrol Mode Device -->
    <div class="mb-6 overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg shadow-lg border border-blue-200">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Kontrol Mode Absensi Device
            </h2>
            <p class="text-blue-100 text-sm mt-1">Atur mode masuk/keluar untuk setiap device face recognition</p>
        </div>
        <div class="p-6">
            @if($devices->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($devices as $device)
                    <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200 hover:shadow-lg transition">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse" title="Online"></div>
                                <h3 class="font-semibold text-gray-800">{{ $device->nama_device }}</h3>
                            </div>
                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $device->ip_address }}</span>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="setDeviceMode({{ $device->id }}, 'masuk')" class="flex-1 flex items-center justify-center px-3 py-2 text-sm font-semibold text-white bg-green-500 rounded-md hover:bg-green-600 transition shadow-sm">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                MASUK
                            </button>
                            <button onclick="setDeviceMode({{ $device->id }}, 'keluar')" class="flex-1 flex items-center justify-center px-3 py-2 text-sm font-semibold text-white bg-red-500 rounded-md hover:bg-red-600 transition shadow-sm">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" transform="rotate(180 10 10)"></path>
                                </svg>
                                KELUAR
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="font-semibold">Tidak ada device terdaftar</p>
                    <p class="text-sm">Tambahkan device face recognition terlebih dahulu</p>
                </div>
            @endif
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

function setDeviceMode(deviceId, mode) {
    // Show loading toast
    showToast('⏳ Mengubah mode device...', 'info');
    
    // Panggil Laravel API yang akan forward ke Flask
    fetch('{{ route("admin.setting_presensi.setDeviceMode") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            device_id: deviceId,
            mode: mode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`✅ ${data.message}`, 'success');
            console.log('Mode changed:', data);
        } else {
            showToast(`❌ ${data.message}`, 'error');
            console.error('Error:', data);
        }
    })
    .catch(error => {
        showToast('❌ Gagal terhubung ke device. Pastikan Flask API aktif.', 'error');
        console.error('Fetch error:', error);
    });
}

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