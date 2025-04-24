@extends('layouts.dashboard')

@section('title', 'Smart School | Edit Jurusan')

@section('content')

<div class="bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-semibold mb-4">Edit Jurusan</h1>

    <form action="{{ route('admin.jurusan.update', $jurusan->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="nama_jurusan" class="block text-sm font-medium text-gray-700">Nama Jurusan</label>
            <input type="text" id="nama_jurusan" name="nama_jurusan" value="{{ old('nama_jurusan', $jurusan->nama_jurusan) }}" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
            @error('nama_jurusan')
                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
            @enderror
        </div>
        
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Update</button>
        <a href="{{ route('admin.jurusan.index') }}" class="ml-4 text-gray-700 hover:text-gray-900">Kembali</a>
    </form>
</div>

@endsection
