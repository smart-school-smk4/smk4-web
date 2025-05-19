@extends('layouts.dashboard')

@section('title', 'Smart School | Edit Guru')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Data Guru</h1>
            <div class="flex items-center mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-gray-600">Ubah informasi guru yang sudah ada</p>
            </div>
        </div>

        <a href="{{ route('admin.guru.index') }}" 
           class="bg-gradient-to-r from-gray-200 to-gray-300 hover:from-gray-300 hover:to-gray-400 text-gray-800 px-6 py-3 rounded-lg flex items-center transition duration-300 shadow-md hover:shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-3xl mx-auto">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <h2 class="text-xl font-semibold text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/>
                    <path fill-rule="evenodd" d="M2 15a2 2 0 012-2h4a1 1 0 010 2H4v2h12v-4h2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-2z" clip-rule="evenodd"/>
                </svg>
                Form Edit Guru
            </h2>
        </div>

        <!-- Form Content -->
        <div class="p-6">
            <form id="editForm" action="{{ route('admin.guru.update', $guru->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nama Guru -->
                <div class="mb-6">
                    <label for="nama_guru" class="block text-sm font-medium text-gray-700 mb-2">Nama Guru <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_guru" id="nama_guru"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition duration-300 @error('nama_guru') border-red-500 @enderror"
                           value="{{ old('nama_guru', $guru->nama_guru) }}" required>
                    @error('nama_guru')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jabatan -->
                <div class="mb-6">
                    <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-2">Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" name="jabatan" id="jabatan"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition duration-300 @error('jabatan') border-red-500 @enderror"
                           value="{{ old('jabatan', $guru->jabatan) }}" required>
                    @error('jabatan')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor HP -->
                <div class="mb-6">
                    <label for="no_hp_guru" class="block text-sm font-medium text-gray-700 mb-2">No. HP Guru <span class="text-red-500">*</span></label>
                    <input type="text" name="no_hp_guru" id="no_hp_guru"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition duration-300 @error('no_hp_guru') border-red-500 @enderror"
                           value="{{ old('no_hp_guru', $guru->no_hp_guru) }}" required>
                    @error('no_hp_guru')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email_guru" class="block text-sm font-medium text-gray-700 mb-2">Email Guru <span class="text-red-500">*</span></label>
                    <input type="email" name="email_guru" id="email_guru"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition duration-300 @error('email_guru') border-red-500 @enderror"
                           value="{{ old('email_guru', $guru->email_guru) }}" required>
                    @error('email_guru')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.guru.index') }}" 
                       class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-300 flex items-center shadow-sm hover:shadow-md">
                        Batal
                    </a>
                    <button type="submit"
                            class="bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white px-6 py-2.5 rounded-lg transition duration-300 shadow-lg hover:shadow-xl flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
