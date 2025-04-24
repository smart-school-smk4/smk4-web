@extends('layouts.dashboard')

@section('title', 'Edit Kelas')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Edit Kelas</h1>
    <form action="{{ route('admin.kelas.update', $kelas->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1 font-medium">Nama Kelas</label>
            <input type="text" name="nama_kelas" value="{{ $kelas->nama_kelas }}" class="w-full border px-4 py-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-medium">Jurusan</label>
            <select name="id_jurusan" class="w-full border px-4 py-2 rounded" required>
                <option value="">-- Pilih Jurusan --</option>
                @foreach($jurusan as $item)
                    <option value="{{ $item->id }}" {{ $kelas->id_jurusan == $item->id ? 'selected' : '' }}>
                        {{ $item->nama_jurusan }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('admin.kelas.index') }}" class="bg-gray-300 px-4 py-2 rounded mr-2">Batal</a>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
        </div>
    </form>
</div>
@endsection
