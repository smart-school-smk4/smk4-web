<div class="flex">
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-md h-screen p-5 fixed top-0 left-0">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('assets/images/logo_smk.svg') }}" alt="Logo" class="w-10 h-10">
            <h2 class="text-xl font-semibold text-black-600 font-poppins">Smart School SMKN 4 Jember</h2>
        </div>
        <ul class="mt-5">
            <li class="mb-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center p-2 rounded-lg {{ request()->is('admin/dashboard') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7m-9-5v12a1 1 0 001 1h3m10 0a1 1 0 001-1V10m-4 4h4"></path>
                    </svg>
                    Dashboard
                </a>
            </li>

            <!-- Data Sekolah Dropdown -->
            <li class="mb-2">
                <button
                    class="dropdown-btn w-full text-left p-2 bg-gray-100 rounded-lg flex justify-between items-center"
                    data-dropdown="data-sekolah">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16m-7 6h7"></path>
                        </svg>
                        Data Sekolah
                    </span>
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <ul class="dropdown hidden mt-2 ml-5 space-y-2">
                    <li><a href="{{ route('admin.siswa.index') }}"
                            class="block p-2 rounded-lg {{ request()->is('admin/siswa') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">Siswa</a>
                    </li>
                    <li><a href="{{ route('admin.guru.index') }}"
                            class="block p-2 rounded-lg {{ request()->is('admin/guru') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">Guru</a>
                    </li>
                    <li><a href="{{ route('admin.kelas.index') }}"
                            class="block p-2 rounded-lg {{ request()->is('admin/kelas/index') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">Kelas</a>
                    </li>
                    <li><a href="{{ route('admin.jurusan.index') }}"
                            class="block p-2 rounded-lg {{ request()->is('admin/jurusan/index') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">Jurusan</a>
                    </li>
                    <li><a href="{{ route('admin.ruangan.index') }}"
                            class="block p-2 rounded-lg {{ request()->is('admin/ruangan/index') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">Ruangan</a>
                    </li>
                </ul>
            </li>

            <!-- Presensi Dropdown -->
            <li class="mb-2">
                <button
                    class="dropdown-btn w-full text-left p-2 bg-gray-100 rounded-lg flex justify-between items-center"
                    data-dropdown="presensi">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                        Presensi
                    </span>
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <ul class="dropdown hidden mt-2 ml-5 space-y-2">
                    <li><a href="{{ route('admin.presensi.siswa') }}"
                            class="block p-2 rounded-lg {{ request()->is('admin/presensi/siswa') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">Siswa</a>
                    </li>
                    <li><a href="{{ route('admin.presensi.guru') }}"
                            class="block p-2 rounded-lg {{ request()->is('admin/presensi/guru') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">Guru</a>
                    </li>
                </ul>
            </li>
            <li class="mb-2">
                <a href="{{ route('admin.laporan') }}"
                    class="flex items-center p-2 rounded-lg {{ request()->is('admin/laporan') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h3m-3-8h6M5 3v18a2 2 0 002 2h10a2 2 0 002-2V3a2 2 0 00-2-2H7a2 2 0 00-2 2z" />
                    </svg>
                    Laporan
                </a>
            </li>
            <li class="mb-2">
                <button
                    class="dropdown-btn w-full text-left p-2 bg-gray-100 rounded-lg flex justify-between items-center"
                    data-dropdown="presensi">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                        Bel & Pengumuman
                    </span>
                    <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <ul class="dropdown hidden mt-2 ml-5 space-y-2">
                    <li><a href="{{ route('bel.index') }}"
                            class="block p-2 rounded-lg {{ request()->is('admin/bel') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">Bel</a>
                    </li>
                    <li><a href="{{ route('admin.announcement.index') }}"
                            class="block p-2 rounded-lg {{ request()->is('admin/pengumuman') ? 'bg-blue-100 text-blue-600' : 'bg-gray-50 text-gray-800' }}">Pengumuman</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>


</div>

<!-- JavaScript untuk Dropdown -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dropdownButtons = document.querySelectorAll(".dropdown-btn");

        dropdownButtons.forEach((button) => {
            const dropdownMenu = button.nextElementSibling;
            const icon = button.querySelector("svg:last-child");

            // Cek apakah ada link aktif dalam dropdown ini
            const isActive = dropdownMenu.querySelector(".bg-blue-100");

            if (isActive) {
                dropdownMenu.classList.remove("hidden");
                icon.classList.add("rotate-180");
            }

            button.addEventListener("click", function() {
                const isOpen = !dropdownMenu.classList.contains("hidden");

                // Tutup semua dropdown terlebih dahulu
                document.querySelectorAll(".dropdown").forEach(menu => menu.classList.add(
                    "hidden"));
                document.querySelectorAll(".dropdown-btn svg:last-child").forEach(i => i
                    .classList.remove("rotate-180"));

                // Jika sebelumnya tertutup, buka yang diklik
                if (!isOpen) {
                    dropdownMenu.classList.remove("hidden");
                    icon.classList.add("rotate-180");
                }
            });
        });
    });
</script>
