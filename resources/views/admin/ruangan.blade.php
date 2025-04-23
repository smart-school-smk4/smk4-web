@extends('layouts.dashboard')

@section('title', 'Smart School | Ruangan')

@section('content')

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Data Ruangan</h1>
        <a href="{{ route('siswa.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            + Tambah Ruangan
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr class="text-left text-gray-700">
                    <th class="border px-4 py-2">No</th>
                    <th class="border px-4 py-2">Nama Ruangan</th>
                    <th class="border px-4 py-2">Nama Kelas</th>
                    <th class="border px-4 py-2">Nama Jurusan</th>
                    <th class="border px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ruangan as $index => $item)
                <tr class="border hover:bg-gray-50">
                    <td class="border px-4 py-2">{{ $index + 1 }}</td>
                    <td class="border px-4 py-2">{{ $item->nama_ruangan }}</td>
                    <td class="border px-4 py-2">{{ $item->nama_kelas }}</td>
                    <td class="border px-4 py-2">{{ $item->nama_jurusan }}</td>
                    <td class="border px-4 py-2 text-center">
                        <a href="{{ route('kelas.edit', $item->id) }}" class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded">Edit</a>
                        <form action="{{ route('kelas.destroy', $item->id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Yakin ingin menghapus?')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection