@extends('layouts.dashboard')

@section('title', 'Smart School | Tambah Siswa')

@section('content')

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Tambah Siswa</h2>

    <form action="{{ route('siswa.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md">
        @csrf
        
        <div class="mb-4">
            <label class="block font-semibold">Nama Siswa</label>
            <input type="text" name="nama_siswa" class="border p-2 w-full rounded" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">NISN</label>
            <input type="number" name="nisn" class="border p-2 w-full rounded" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" class="border p-2 w-full rounded" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Foto Siswa</label>
            <input type="file" name="foto_siswa" class="border p-2 w-full rounded" accept="image/*" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="border p-2 w-full rounded" required>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Alamat</label>
            <textarea name="alamat" class="border p-2 w-full rounded" required></textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">No HP</label>
            <input type="number" name="no_hp" class="border p-2 w-full rounded" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Email</label>
            <input type="email" name="email" class="border p-2 w-full rounded" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Jurusan</label>
            <select name="id_jurusan" class="border p-2 w-full rounded" required>
                @foreach ($jurusan as $item)
                    <option value="{{ $item->id }}">{{ $item->nama_jurusan }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Kelas</label>
            <select name="id_kelas" class="border p-2 w-full rounded" required>
                @foreach ($kelas as $item)
                    <option value="{{ $item->id }}">{{ $item->nama_kelas }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-4">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
            <a href="{{ route('admin.siswa') }}" class="ml-2 text-gray-600">Batal</a>
        </div>
    </form>
</div>

@endsection
