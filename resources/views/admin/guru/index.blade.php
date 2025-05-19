@extends('layouts.dashboard')

@section('title', 'Smart School | Guru')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Guru</h1>
            <div class="flex items-center mt-2 text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                </svg>
                Kelola data guru dan informasi kontaknya
            </div>
        </div>

        <a href="{{ route('admin.guru.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white px-6 py-3 rounded-lg flex items-center transition duration-300 shadow-lg hover:shadow-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Tambah Guru
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h2 class="text-lg font-semibold text-gray-800">Daftar Guru</h2>
            <p class="text-sm text-gray-500">Data guru yang terdaftar di sistem</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse">
                <thead class="bg-gray-100 text-gray-700 text-sm font-semibold">
                    <tr>
                        <th class="border px-6 py-3 text-left">No</th>
                        <th class="border px-6 py-3 text-left">Nama Guru</th>
                        <th class="border px-6 py-3 text-left">Jabatan</th>
                        <th class="border px-6 py-3 text-left">No HP</th>
                        <th class="border px-6 py-3 text-left">Email</th>
                        <th class="border px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    @foreach ($guru as $index => $item)
                    <tr class="hover:bg-gray-50 transition duration-200">
                        <td class="border px-6 py-3">{{ $index + 1 }}</td>
                        <td class="border px-6 py-3">{{ $item->nama_guru }}</td>
                        <td class="border px-6 py-3">{{ $item->jabatan }}</td>
                        <td class="border px-6 py-3">{{ $item->no_hp_guru }}</td>
                        <td class="border px-6 py-3">{{ $item->email_guru }}</td>
                        <td class="border px-6 py-3 text-center space-x-2">
                            <a href="{{ route('admin.guru.edit', $item->id) }}" class="inline-block bg-blue-600 hover:bg-blue-600 text-white px-3 py-1 rounded transition duration-200">
                                Edit
                            </a>
                            <form action="{{ route('admin.guru.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition duration-200">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach

                    @if($guru->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 py-6">
                            Tidak ada data guru.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
