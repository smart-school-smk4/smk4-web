<div class="bg-white shadow-md p-4 flex justify-between items-center">
    <div>
        <input type="text" placeholder="Search..." class="border rounded-lg px-4 py-2 w-64">
    </div>
    <div class="flex items-center space-x-4">
        <button class="p-2 rounded-full bg-gray-100">
            🔔
        </button>
        <div class="relative">
            <div class="flex items-center space-x-2 cursor-pointer" id="profile-btn">
                <span>Admin</span>
                <img src="{{ asset('assets/images/profile.svg') }}" class="w-10 h-10 rounded-full object-cover">
            </div>
            <!-- Dropdown -->
            <div id="profile-dropdown" class="absolute right-0 mt-2 bg-white border rounded-lg shadow-lg hidden">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</button>
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
