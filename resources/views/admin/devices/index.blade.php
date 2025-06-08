@extends('layouts.dashboard')

@section('title', 'Smart School | Manajemen Device')

@section('content')
<div class="container mx-auto px-4 py-6 relative">
    
    <!-- Toast Notification Container -->
    <div class="absolute top-4 right-4 space-y-3 z-50">
        @if (session('success'))
            <div id="toast-success" class="flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg"><svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/></svg></div>
                <div class="ml-3 text-sm font-normal">{{ session('success') }}</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg p-1.5" data-dismiss-target="#toast-success" aria-label="Close"><svg class="w-3 h-3" fill="none" viewBox="0 0 14 14" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg></button>
            </div>
        @endif
        @if ($errors->any())
            <div id="toast-error" class="flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg"><svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z"/></svg></div>
                <div class="ml-3 text-sm font-normal">Gagal! Periksa kembali data Anda.</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg p-1.5" data-dismiss-target="#toast-error" aria-label="Close"><svg class="w-3 h-3" fill="none" viewBox="0 0 14 14" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg></button>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header Card -->
        <div class="bg-gradient-to-br from-blue-600 to-cyan-500 px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Manajemen Device
                    </h1>
                    <p class="text-blue-100 mt-1">Total {{ $devices->total() }} device terpasang</p>
                </div>
                <button onclick="openModal('createModal')" class="flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg shadow hover:bg-gray-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Device
                </button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="p-6">
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($devices as $key => $device)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">{{ $devices->firstItem() + $key }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $device->nama_device }}</td>
                            <td class="px-6 py-4 text-gray-500 font-mono">{{ $device->ip_address ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $device->kelas->nama_kelas ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $device->ruangan->nama_ruangan ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end space-x-2">
                                    <button onclick="openModal('editModal-{{$device->id}}')" class="text-indigo-600 hover:text-indigo-900 p-1">Edit</button>
                                    <form id="delete-form-{{ $device->id }}" action="{{ route('admin.devices.destroy', $device->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete('{{ $device->id }}', '{{ $device->nama_device }}')" class="text-red-600 hover:text-red-900 p-1">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada device yang ditambahkan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $devices->links() }}</div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <form action="{{ route('admin.devices.store') }}" method="POST">
            @csrf
            <div class="p-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Tambah Device Baru</h3>
                <div class="space-y-4">
                    {{-- Form fields --}}
                    <div>
                        <label for="nama_device_create" class="block text-sm font-medium text-gray-700">Nama Device</label>
                        <input type="text" name="nama_device" id="nama_device_create" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label for="ip_address_create" class="block text-sm font-medium text-gray-700">IP Address</label>
                        <input type="text" name="ip_address" id="ip_address_create" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Contoh: 192.168.1.101">
                    </div>
                    <div>
                        <label for="id_kelas_create" class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="id_kelas" id="id_kelas_create" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach($kelas as $item) <option value="{{ $item->id }}">{{ $item->nama_kelas }}</option> @endforeach
                        </select>
                    </div>
                     <div>
                        <label for="id_ruangan_create" class="block text-sm font-medium text-gray-700">Ruangan</label>
                        <select name="id_ruangan" id="id_ruangan_create" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach($ruangan as $item) <option value="{{ $item->id }}">{{ $item->nama_ruangan }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('createModal')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modals -->
@foreach($devices as $device)
<div id="editModal-{{$device->id}}" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <form action="{{ route('admin.devices.update', $device->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="p-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Edit Device</h3>
                <div class="space-y-4">
                    {{-- Form fields --}}
                    <div>
                        <label for="nama_device_edit_{{$device->id}}" class="block text-sm font-medium text-gray-700">Nama Device</label>
                        <input type="text" name="nama_device" id="nama_device_edit_{{$device->id}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ $device->nama_device }}" required>
                    </div>
                    <div>
                        <label for="ip_address_edit_{{$device->id}}" class="block text-sm font-medium text-gray-700">IP Address</label>
                        <input type="text" name="ip_address" id="ip_address_edit_{{$device->id}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ $device->ip_address }}" placeholder="Contoh: 192.168.1.101">
                    </div>
                    <div>
                        <label for="id_kelas_edit_{{$device->id}}" class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="id_kelas" id="id_kelas_edit_{{$device->id}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach($kelas as $item) <option value="{{ $item->id }}" {{ $device->id_kelas == $item->id ? 'selected' : '' }}>{{ $item->nama_kelas }}</option> @endforeach
                        </select>
                    </div>
                     <div>
                        <label for="id_ruangan_edit_{{$device->id}}" class="block text-sm font-medium text-gray-700">Ruangan</label>
                        <select name="id_ruangan" id="id_ruangan_edit_{{$device->id}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach($ruangan as $item) <option value="{{ $item->id }}" {{ $device->id_ruangan == $item->id ? 'selected' : '' }}>{{ $item->nama_ruangan }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('editModal-{{$device->id}}')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Perbarui</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openModal(modalId) { document.getElementById(modalId).style.display = 'block'; }
    function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
    window.setTimeout(() => document.querySelectorAll('[id^="toast-"]').forEach(toast => toast.remove()), 5000);
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Device?',
            html: `Anda akan menghapus device: <strong>${name}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
        }).then((result) => result.isConfirmed && document.getElementById(`delete-form-${id}`).submit());
    }
</script>
@endsection
