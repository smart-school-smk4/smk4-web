@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold">Detail Pengumuman</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pengumuman</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Waktu:</strong> {{ $announcement->sent_at->format('d M Y H:i:s') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Mode:</strong> 
                            <span class="badge bg-{{ $announcement->mode === 'reguler' ? 'primary' : 'success' }}">
                                {{ strtoupper($announcement->mode) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Ruangan Tujuan:</strong>
                        <div class="mt-2">
                            @foreach($announcement->ruangans as $ruangan)
                                <span class="badge bg-secondary mb-1">{{ $ruangan->nama_ruangan }}</span>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Isi Pengumuman:</strong>
                        <div class="p-3 bg-light rounded mt-2">
                            @if($announcement->mode === 'tts')
                                <p>{{ $announcement->message }}</p>
                                @if($announcement->audio_path)
                                    <audio controls class="w-100 mt-3">
                                        <source src="{{ $announcement->audio_url }}" type="audio/wav">
                                        Browser Anda tidak mendukung pemutar audio.
                                    </audio>
                                @endif
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <strong>Suara:</strong> {{ $announcement->voice }} | 
                                        <strong>Kecepatan:</strong> {{ $announcement->speed }}
                                    </small>
                                </div>
                            @else
                                <p>{{ $announcement->message }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Dibuat Oleh:</strong> {{ $announcement->user->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Dibuat Pada:</strong> {{ $announcement->created_at->format('d M Y H:i:s') }}
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('admin.announcement.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection