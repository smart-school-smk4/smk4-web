@extends('layouts.dashboard')

@section('title', 'Smart School | Edit Waktu Presensi')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <div class="max-w-2xl mx-auto">
        <div class="p-8 bg-white rounded-lg shadow-lg">
            <h1 class="mb-6 text-2xl font-bold text-gray-800">📝 Edit Pengaturan Presensi</h1>
            
            @if ($errors->any())
                <div class="p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500 rounded-md" role="alert">
                    <p class="font-bold">Terjadi kesalahan!</p>
                    <ul class="mt-2 ml-4 list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('admin.setting_presensi.update', $setting->id) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <h3 class="mb-4 font-semibold text-gray-700">Jam Masuk</h3>
                        <div class="mb-4">
                            <label for="waktu_masuk_mulai" class="block mb-2 text-sm font-medium text-gray-700">Waktu Mulai</label>
                            <input type="time" name="waktu_masuk_mulai" id="waktu_masuk_mulai" value="{{ old('waktu_masuk_mulai', $setting->waktu_masuk_mulai ? (strlen($setting->waktu_masuk_mulai) == 5 ? $setting->waktu_masuk_mulai : \Carbon\Carbon::parse($setting->waktu_masuk_mulai)->format('H:i')) : '') }}" class="block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label for="waktu_masuk_selesai" class="block mb-2 text-sm font-medium text-gray-700">Waktu Selesai</label>
                            <input type="time" name="waktu_masuk_selesai" id="waktu_masuk_selesai" value="{{ old('waktu_masuk_selesai', $setting->waktu_masuk_selesai ? (strlen($setting->waktu_masuk_selesai) == 5 ? $setting->waktu_masuk_selesai : \Carbon\Carbon::parse($setting->waktu_masuk_selesai)->format('H:i')) : '') }}" class="block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                    
                    <div class="p-4 border border-gray-200 rounded-lg">
                         <h3 class="mb-4 font-semibold text-gray-700">Jam Pulang</h3>
                        <div class="mb-4">
                            <label for="waktu_pulang_mulai" class="block mb-2 text-sm font-medium text-gray-700">Waktu Mulai</label>
                            <input type="time" name="waktu_pulang_mulai" id="waktu_pulang_mulai" value="{{ old('waktu_pulang_mulai', $setting->waktu_pulang_mulai ? (strlen($setting->waktu_pulang_mulai) == 5 ? $setting->waktu_pulang_mulai : \Carbon\Carbon::parse($setting->waktu_pulang_mulai)->format('H:i')) : '') }}" class="block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label for="waktu_pulang_selesai" class="block mb-2 text-sm font-medium text-gray-700">Waktu Selesai</label>
                            <input type="time" name="waktu_pulang_selesai" id="waktu_pulang_selesai" value="{{ old('waktu_pulang_selesai', $setting->waktu_pulang_selesai ? (strlen($setting->waktu_pulang_selesai) == 5 ? $setting->waktu_pulang_selesai : \Carbon\Carbon::parse($setting->waktu_pulang_selesai)->format('H:i')) : '') }}" class="block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-8 space-x-4">
                    <a href="{{ route('admin.setting_presensi.index') }}" class="px-4 py-2 font-bold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-300">Batal</a>
                    <button type="submit" class="px-4 py-2 font-bold text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition duration-300">Update Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection