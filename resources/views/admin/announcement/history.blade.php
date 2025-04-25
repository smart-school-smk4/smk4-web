@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold">Riwayat Pengumuman</h2>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form action="{{ route('admin.announcement.history') }}" method="GET" class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="Cari pengumuman..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="mode" class="form-select">
                                <option value="">Semua Mode</option>
                                <option value="reguler" {{ request('mode') === 'reguler' ? 'selected' : '' }}>Reguler</option>
                                <option value="tts" {{ request('mode') === 'tts' ? 'selected' : '' }}>TTS</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.announcement.history') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-sync-alt me-2"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="announcements-table">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Waktu</th>
                                    <th width="10%">Mode</th>
                                    <th width="20%">Ruangan Tujuan</th>
                                    <th>Isi Pengumuman</th>
                                    <th width="15%">Pengirim</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($announcements as $announcement)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $announcement->sent_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $announcement->mode === 'reguler' ? 'primary' : 'success' }}">
                                                {{ strtoupper($announcement->mode) }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach($announcement->ruangans as $ruangan)
                                                <span class="badge bg-secondary mb-1">{{ $ruangan->nama_ruangan }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($announcement->mode === 'tts')
                                                <i class="fas fa-volume-up text-success me-2"></i>
                                                <small>TTS: {{ Str::limit($announcement->message, 50) }}</small>
                                                @if($announcement->audio_path)
                                                    <a href="{{ $announcement->audio_url }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                                        <i class="fas fa-play"></i>
                                                    </a>
                                                @endif
                                            @else
                                                {{ Str::limit($announcement->message, 50) }}
                                            @endif
                                        </td>
                                        <td>{{ $announcement->user->name }}</td>
                                        <td>
                                            <a href="{{ route('admin.announcement.show', $announcement->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.announcement.destroy', $announcement->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pengumuman ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada pengumuman</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $announcements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection