@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto p-6">
    <!-- Judul Halaman -->
    <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-yellow-600 mb-8 text-center">
        Hentikan Mic Manual
    </h1>

    <!-- Card Container -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden max-w-md mx-auto">
        <div class="p-6">
            <!-- Deskripsi -->
            <p class="text-gray-700 text-center mb-6">
                Tekan tombol di bawah ini untuk menghentikan mic manual di semua ruangan.
            </p>

            <!-- Tombol Aksi -->
            <form id="stop-manual-form" action="{{ route('announcements.stopManual') }}" method="POST" class="flex justify-center">
                @csrf

                <button type="submit" id="submit-btn"
                    class="relative inline-flex items-center justify-center px-8 py-3 font-medium text-white rounded-lg transition duration-300 ease-in-out hover:scale-105 shadow-md bg-gradient-to-r from-red-600 to-yellow-600">
                    <svg id="spinner" class="hidden animate-spin mr-2 h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Hentikan Mic Manual
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    const form = document.getElementById('stop-manual-form');
    const submitBtn = document.getElementById('submit-btn');
    const spinner = document.getElementById('spinner');

    // Handle form submission
    form.addEventListener('submit', function () {
        // Tampilkan loading SweetAlert
        Swal.fire({
            title: 'Menghentikan Mic Manual...',
            text: 'Mohon tunggu sebentar.',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => Swal.showLoading()
        });

        // Tampilkan spinner dan disable tombol
        spinner.classList.remove('hidden');
        submitBtn.setAttribute('disabled', true);
    });
</script>
@endsection