@extends('layouts.auth')

@section('title', 'Smart School | Login')

@section('content')
    <div class="flex flex-col md:flex-row min-h-screen w-full">
        <!-- Bagian Kiri: Gambar (Sembunyi di Mobile) -->
        <div class="hidden md:block md:w-1/2 bg-cover bg-center"
            style="background-image: url('/assets/images/bg_login.svg');">
        </div>

        <!-- Bagian Kanan: Form Login -->
        <div class="w-full md:w-1/2 flex items-center justify-center h-screen md:h-auto px-5 bg-white">
            <div class="w-full max-w-md text-center">
                <!-- Logo dan Nama Aplikasi -->
                <div class="mb-6 flex items-center justify-center space-x-3">
                    <img src="/assets/images/logo_smk.svg" alt="Logo Smart School" class="w-16 h-16">
                    <div class="text-left">
                        <h2 class="text-lg font-bold text-blue-800">SMART SCHOOL</h2>
                        <p class="text-sm text-gray-500">SMKN 04 JEMBER</p>
                    </div>
                </div>
                
                <!-- Salam Pembuka -->
                <h2 class="text-2xl font-bold text-blue-900 mb-2">Hai, selamat datang kembali</h2>
                <p class="text-gray-500 mb-6">Login untuk membuka <span class="text-blue-500">dashboard</span></p>

                <!-- Notifikasi Error -->
                @if ($errors->any())
                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                        <strong>Error!</strong> {{ $errors->first('email') }}
                    </div>
                @endif

                <!-- Form Login -->
                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf
                    <!-- Username Input -->
                    <div class="text-left">
                        <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
                        <input type="text" name="email" value="{{ old('email') }}"
                            class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Email" required />
                    </div>
                    <!-- Password Input -->
                    <div class="text-left">
                        <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
                        <input type="password" name="password"
                            class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Password" required />
                    </div>
                    <!-- Login Button -->
                    <div>
                        <button type="submit"
                            class="w-full p-3 bg-blue-800 text-white font-bold rounded-lg hover:opacity-90">
                            Masuk
                        </button>
                    </div>
                </form>

                <!-- Lupa Password -->
                <p class="text-sm text-gray-600 mt-4">Lupa password? <a href="#" class="text-blue-600 font-semibold">Klik disini</a></p>
            </div>
        </div>
    </div>
@endsection
