@extends('layouts.dashboard')

@section('title', 'Smart School | Edit Ruangan')

@section('content')

<div class="bg-white shadow-md rounded-lg p-6 max-w-xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Edit Ruangan</h1>

    <form action="{{ route('admin.ruangan.update', $ruangan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="nama_ruangan" class="block font-medium">Nama Ruangan</label>
            <input type="text" name="nama_ruangan" id="nama_ruangan" class="w-full border px-4 py-2 rounded-lg" value="{{ old('nama_ruangan', $ruangan->nama_ruangan) }}" required>
        </div>

        <div class="mb-4">
            <label for="kelas_id" class="block font-medium">Kelas</label>
            <select name="kelas_id" id="kelas_id" class="w-full border px-4 py-2 rounded-lg" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach ($kelas as $k)
                    <option value="{{ $k->id }}" {{ $ruangan->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="jurusan_id" class="block font-medium">Jurusan</label>
            <select name="jurusan_id" id="jurusan_id" class="w-full border px-4 py-2 rounded-lg" required>
                <option value="">-- Pilih Jurusan --</option>
                @foreach ($jurusan as $j)
                    <option value="{{ $j->id }}" {{ $ruangan->jurusan_id == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('admin.ruangan.index') }}" class="mr-2 px-4 py-2 bg-gray-300 rounded-lg">Batal</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Update</button>
        </div>
    </form>
</div>

@endsection
