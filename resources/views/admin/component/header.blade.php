<div class="bg-white shadow-md px-4 sm:px-6 py-4 flex justify-between items-center border-b-2 border-gray-100">
    <div class="flex items-center gap-3 flex-1">
        <!-- Hamburger Button (Mobile Only) -->
        <button id="hamburgerBtn" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition duration-200">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        
        <!-- Search Bar - Hidden on mobile, visible on tablet+ -->
        <div class="hidden sm:block flex-1 max-w-md">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" placeholder="Cari..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition duration-200">
            </div>
        </div>
    </div>
    
    <div class="flex items-center space-x-2 sm:space-x-4">
        <!-- Notification Bell -->
        <button class="relative p-2 sm:p-2.5 rounded-xl bg-gray-50 hover:bg-gray-100 transition duration-200">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
        
        <!-- Profile Dropdown -->
        <div class="relative">
            <div class="flex items-center space-x-2 sm:space-x-3 cursor-pointer bg-gray-50 rounded-xl px-2 sm:px-3 py-2 hover:bg-gray-100 transition duration-200" id="profile-btn">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-semibold text-gray-800">Admin</p>
                    <p class="text-xs text-gray-500">Administrator</p>
                </div>
                <div class="relative">
                    <img src="{{ asset('assets/images/profile.svg') }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-primary-200">
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 sm:w-3 sm:h-3 bg-green-500 rounded-full border-2 border-white"></span>
                </div>
            </div>
            
            <!-- Dropdown Menu -->
            <div id="profile-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 hidden overflow-hidden">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 transition duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const profileBtn = document.getElementById("profile-btn");
        const dropdown = document.getElementById("profile-dropdown");

        profileBtn.addEventListener("click", function () {
            dropdown.classList.toggle("hidden");
        });

        // Menutup dropdown jika klik di luar
        document.addEventListener("click", function (event) {
            if (!profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add("hidden");
            }
        });
    });
</script>
