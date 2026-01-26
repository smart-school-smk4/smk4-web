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
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h1 class="text-3xl font-bold mb-2 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Manajemen Device
                    </h1>
                    <p class="text-primary-100">Total {{ $devices->total() }} device terpasang</p>
                </div>
                <button onclick="openModal('createModal')" class="bg-white text-primary-600 hover:bg-primary-50 px-6 py-3 rounded-xl font-semibold flex items-center transition duration-200 shadow-lg hover:shadow-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Device
                </button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="p-6">
            <div class="overflow-hidden rounded-xl border border-gray-200 shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Device ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Device</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Ruangan</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($devices as $key => $device)
                        <tr class="hover:bg-blue-50 transition duration-150">
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 inline-flex text-xs font-mono font-semibold rounded-lg bg-gray-100 text-gray-700">{{ $device->id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ $device->nama_device }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-mono text-gray-600">{{ $device->ip_address ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg bg-blue-100 text-blue-700">{{ $device->kelas->nama_kelas ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg bg-purple-100 text-purple-700">{{ $device->ruangan->nama_ruangan ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end space-x-2">
                                    <button onclick="openModal('editModal-{{$device->id}}')" 
                                            class="p-2 text-primary-600 hover:text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition duration-200"
                                            title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <form id="delete-form-{{ $device->id }}" action="{{ route('admin.devices.destroy', $device->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete('{{ $device->id }}', '{{ $device->nama_device }}')" 
                                                class="p-2 text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition duration-200"
                                                title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Belum ada device yang ditambahkan</p>
                                </div>
                            </td>
                        </tr>
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
