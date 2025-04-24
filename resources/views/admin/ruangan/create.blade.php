@extends('layouts.dashboard')

@section('title', 'Smart School | Tambah Ruangan')

@section('content')

<div class="bg-white shadow-md rounded-lg p-6 max-w-xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Tambah Ruangan</h1>

    <form action="{{ route('admin.ruangan.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="nama_ruangan" class="block font-medium">Nama Ruangan</label>
            <input type="text" name="nama_ruangan" id="nama_ruangan" class="w-full border px-4 py-2 rounded-lg" required value="{{ old('nama_ruangan') }}">
        </div>

        <div class="mb-4">
            <label for="id_kelas" class="block font-medium">Kelas</label>
            <select name="id_kelas" id="id_kelas" class="w-full border px-4 py-2 rounded-lg" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach ($kelas as $k)
                    <option value="{{ $k->id }}" {{ old('id_kelas') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="id_jurusan" class="block font-medium">Jurusan</label>
            <select name="id_jurusan" id="id_jurusan" class="w-full border px-4 py-2 rounded-lg" required>
                <option value="">-- Pilih Jurusan --</option>
                @foreach ($jurusan as $j)
                    <option value="{{ $j->id }}" {{ old('id_jurusan') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('admin.ruangan.index') }}" class="mr-2 px-4 py-2 bg-gray-300 rounded-lg">Batal</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Simpan</button>
        </div>
    </form>
</div>

@endsection
