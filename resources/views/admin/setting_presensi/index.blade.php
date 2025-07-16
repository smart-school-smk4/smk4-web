@extends('layouts.dashboard')

@section('title', 'Smart School | Setting Waktu Presensi')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
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
@endsection