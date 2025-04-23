@extends('layouts.dashboard')

@section('title', 'Smart School | Presensi Siswa')

@section('content')

    <div class="container">
        <h2 class="mb-6 text-3xl font-bold text-gray-800">Presensi Siswa</h2>

        <!-- Pilihan Kelas -->
        <div class="mb-4">
            <label for="kelas" class="block text-lg font-semibold text-gray-700">Pilih Kelas:</label>
            <select id="kelas"
                class="form-select w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="all">Semua Kelas</option>
                <option value="kelas_10">Kelas 10</option>
                <option value="kelas_11">Kelas 11</option>
                <option value="kelas_12">Kelas 12</option>
            </select>
        </div>

        <!-- Filter Tanggal -->
        <div class="mb-4">
            <label for="tanggal" class="block text-lg font-semibold text-gray-700">Filter Tanggal:</label>
            <input type="date" id="tanggal"
                class="form-control w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Kotak Video Stream -->
        <div class="mb-6 text-center">
            <h5 class="text-xl font-semibold text-blue-600">Live Camera</h5>
            <div id="cameraContainer"
                class="border rounded-lg shadow-lg mx-auto w-[640px] h-[480px] flex items-center justify-center bg-gray-200">
                <img id="cameraFeed" src="http://localhost:5000/video_feed" width="640" height="480" class="hidden">
                <p id="cameraStatus" class="text-gray-500">Memeriksa kamera...</p>
            </div>
        </div>


        <!-- Tabel Presensi -->
        <h5 class="text-xl font-semibold text-gray-800 mb-4">Hasil Presensi</h5>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200 shadow-md rounded-lg">
                <thead class="bg-gray-100">
                    <tr class="text-left text-gray-700">
                        <th class="border px-4 py-2">No</th>
                        <th class="border px-4 py-2">Nama Siswa</th>
                        <th class="border px-4 py-2">Kelas</th>
                        <th class="border px-4 py-2">Waktu Presensi</th>
                        <th class="border px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border hover:bg-gray-50">
                        <td class="border px-4 py-2">1</td>
                        <td class="border px-4 py-2">Ahmad Fauzi</td>
                        <td class="border px-4 py-2">Kelas 10</td>
                        <td class="border px-4 py-2">07:10 AM</td>
                        <td class="border px-4 py-2 text-green-600 font-semibold">Hadir</td>
                    </tr>
                    <tr class="border hover:bg-gray-50">
                        <td class="border px-4 py-2">2</td>
                        <td class="border px-4 py-2">Siti Aisyah</td>
                        <td class="border px-4 py-2">Kelas 11</td>
                        <td class="border px-4 py-2">07:15 AM</td>
                        <td class="border px-4 py-2 text-green-600 font-semibold">Hadir</td>
                    </tr>
                    <tr class="border hover:bg-gray-50">
                        <td class="border px-4 py-2">3</td>
                        <td class="border px-4 py-2">Budi Santoso</td>
                        <td class="border px-4 py-2">Kelas 12</td>
                        <td class="border px-4 py-2">07:30 AM</td>
                        <td class="border px-4 py-2 text-red-600 font-semibold">Terlambat</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let cameraFeed = document.getElementById("cameraFeed");
            let cameraStatus = document.getElementById("cameraStatus");

            cameraFeed.onload = function() {
                cameraFeed.classList.remove("hidden");
                cameraStatus.style.display = "none"; // Sembunyikan teks jika kamera aktif
            };

            cameraFeed.onerror = function() {
                cameraFeed.style.display = "none";
                cameraStatus.innerText = "Kamera Tidak Aktif";
            };
            // Event listener untuk dropdown kelas
            document.getElementById("kelas").addEventListener("change", function() {
                let kelas = this.value;
                console.log("Filter kelas:", kelas);
                // TODO: Filter tabel berdasarkan kelas
            });

            // Event listener untuk filter tanggal
            document.getElementById("tanggal").addEventListener("change", function() {
                let tanggal = this.value;
                console.log("Filter tanggal:", tanggal);
                // TODO: Filter tabel berdasarkan tanggal
            });
        });
    </script>

@endsection
