@extends('layouts.dashboard')

@section('title', 'Detail Siswa')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-3xl mx-auto">
        <div class="flex items-center mb-6">
            <div class="h-20 w-20 rounded-full overflow-hidden border border-gray-200 mr-6">
                @if($siswa->fotos && $siswa->fotos->isNotEmpty())
                    <img class="h-full w-full object-cover" src="{{ asset('storage/' . $siswa->fotos->first()->path) }}" alt="Foto {{ $siswa->nama_siswa }}">
                @else
                    <div class="bg-gray-200 h-full w-full flex items-center justify-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                @endif
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $siswa->nama_siswa }}</h2>
                <div class="text-gray-500">NISN: {{ $siswa->nisn }}</div>
                <div class="text-gray-500">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }} • {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->age }} tahun</div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Data Pribadi</h3>
                <div><span class="text-gray-500">Tanggal Lahir:</span> {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d-m-Y') }}</div>
                <div><span class="text-gray-500">Email:</span> {{ $siswa->email }}</div>
                <div><span class="text-gray-500">No HP:</span> {{ $siswa->no_hp }}</div>
                <div><span class="text-gray-500">Alamat:</span> {{ $siswa->alamat }}</div>
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Akademik</h3>
                <div><span class="text-gray-500">Kelas:</span> {{ $siswa->kelas->nama_kelas ?? '-' }}</div>
                <div><span class="text-gray-500">Jurusan:</span> {{ $siswa->jurusan->nama_jurusan ?? '-' }}</div>
            </div>
        </div>
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700">Semua Foto Wajah</h3>
                @if($siswa->fotos && $siswa->fotos->count() > 0)
                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                        {{ $siswa->fotos->count() }} Foto
                    </span>
                @endif
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @if($siswa->fotos && $siswa->fotos->count() > 0)
                    @foreach($siswa->fotos as $index => $foto)
                        <div class="relative group">
                            <img src="{{ asset('storage/' . $foto->path) }}" 
                                 class="w-full h-32 object-cover rounded-lg shadow-md hover:shadow-xl transition duration-200" 
                                 alt="Foto {{ $index + 1 }}">
                        </div>
                    @endforeach
                @else
                    <div class="text-gray-400 col-span-2">Tidak ada foto.</div>
                @endif
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <a href="{{ route('admin.siswa.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Kembali</a>
        </div>
    </div>
</div>
@endsection
