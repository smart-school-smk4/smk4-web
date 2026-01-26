<div class="flex">
    <!-- Sidebar -->
    <div class="w-64 bg-gradient-to-b from-primary-600 to-primary-700 shadow-2xl h-screen p-6 fixed top-0 left-0">
        <!-- Logo & Brand -->
        <div class="flex items-center space-x-3 mb-8 pb-6 border-b border-primary-500">
            <img src="{{ asset('assets/images/logo_smk.svg') }}" alt="Logo" class="w-12 h-12">
            <div>
                <h2 class="text-lg font-bold text-white">Smart School</h2>
                <p class="text-xs text-blue-200">SMKN 4 Jember</p>
            </div>
        </div>
        
        <!-- Navigation Menu -->
        <ul class="space-y-2">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('admin/dashboard') ? 'bg-white text-primary-600 shadow-lg' : 'text-white hover:bg-primary-500' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>

            <!-- Data Sekolah Dropdown -->
            <li>
                <button
                    class="dropdown-btn w-full text-left px-4 py-3 rounded-xl flex justify-between items-center transition-all duration-200 {{ request()->is('admin/siswa*') || request()->is('admin/kelas*') || request()->is('admin/jurusan*') || request()->is('admin/ruangan*') || request()->is('admin/devices*') ? 'bg-primary-500 text-white' : 'text-white hover:bg-primary-500' }}"
                    data-dropdown="data-sekolah">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="font-medium">Data Sekolah</span>
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <ul class="dropdown hidden mt-2 ml-4 space-y-1">
                    <li><a href="{{ route('admin.siswa.index') }}"
                            class="block px-4 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('admin/siswa*') ? 'bg-white text-primary-600 shadow-md font-medium' : 'text-blue-100 hover:bg-primary-500 hover:text-white' }}">Siswa</a>
                    </li>
                    <li><a href="{{ route('admin.kelas.index') }}"
                            class="block px-4 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('admin/kelas*') ? 'bg-white text-primary-600 shadow-md font-medium' : 'text-blue-100 hover:bg-primary-500 hover:text-white' }}">Kelas</a>
                    </li>
                    <li><a href="{{ route('admin.jurusan.index') }}"
                            class="block px-4 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('admin/jurusan*') ? 'bg-white text-primary-600 shadow-md font-medium' : 'text-blue-100 hover:bg-primary-500 hover:text-white' }}">Jurusan</a>
                    </li>
                    <li><a href="{{ route('admin.ruangan.index') }}"
                            class="block px-4 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('admin/ruangan*') ? 'bg-white text-primary-600 shadow-md font-medium' : 'text-blue-100 hover:bg-primary-500 hover:text-white' }}">Ruangan</a>
                    </li>
                    <li><a href="{{ route('admin.devices.index') }}"
                            class="block px-4 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('admin/devices*') ? 'bg-white text-primary-600 shadow-md font-medium' : 'text-blue-100 hover:bg-primary-500 hover:text-white' }}">Devices</a>
                    </li>
                </ul>
            </li>

            <!-- Presensi Dropdown -->
            <li>
                <button
                    class="dropdown-btn w-full text-left px-4 py-3 rounded-xl flex justify-between items-center transition-all duration-200 {{ request()->is('admin/presensi*') || request()->is('admin/setting_presensi*') ? 'bg-primary-500 text-white' : 'text-white hover:bg-primary-500' }}"
                    data-dropdown="presensi">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <span class="font-medium">Presensi</span>
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <ul class="dropdown hidden mt-2 ml-4 space-y-1">
                    <li><a href="{{ route('admin.presensi.siswa') }}"
                            class="block px-4 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('admin/presensi/siswa*') ? 'bg-white text-primary-600 shadow-md font-medium' : 'text-blue-100 hover:bg-primary-500 hover:text-white' }}">Siswa</a>
                    </li>
                    <li><a href="{{ route('admin.setting_presensi.index') }}"
                            class="block px-4 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('admin/setting_presensi*') ? 'bg-white text-primary-600 shadow-md font-medium' : 'text-blue-100 hover:bg-primary-500 hover:text-white' }}">Setting Presensi</a>                 
                    </li>
                </ul>
            </li>
            
            <!-- Laporan -->
            <li>
                <a href="{{ route('admin.laporan') }}"
                    class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('admin/laporan*') ? 'bg-white text-primary-600 shadow-lg' : 'text-white hover:bg-primary-500' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-medium">Laporan</span>
                </a>
            </li>
            
            <!-- Bel & Pengumuman Dropdown -->
            <li>
                <button
                    class="dropdown-btn w-full text-left px-4 py-3 rounded-xl flex justify-between items-center transition-all duration-200 {{ request()->is('admin/bel*') || request()->is('admin/announcement*') ? 'bg-primary-500 text-white' : 'text-white hover:bg-primary-500' }}"
                    data-dropdown="bel-pengumuman">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="font-medium">Bel & Pengumuman</span>
                    </span>
                    <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <ul class="dropdown hidden mt-2 ml-4 space-y-1">
                    <li><a href="{{ route('bel.index') }}"
                            class="block px-4 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('admin/bel*') ? 'bg-white text-primary-600 shadow-md font-medium' : 'text-blue-100 hover:bg-primary-500 hover:text-white' }}">Bel</a>
                    </li>
                    <li><a href="{{ route('admin.announcement.index') }}"
                            class="block px-4 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('admin/announcement*') ? 'bg-white text-primary-600 shadow-md font-medium' : 'text-blue-100 hover:bg-primary-500 hover:text-white' }}">Pengumuman</a>
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
