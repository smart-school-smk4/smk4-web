@extends('layouts.auth')

@section('title', 'Smart School | Login')

@section('content')
    <div class="flex flex-col md:flex-row min-h-screen w-full bg-gradient-to-br from-blue-50 to-blue-100">
        <!-- Bagian Kiri: Gambar (Sembunyi di Mobile) -->
        <div class="hidden md:flex md:w-1/2 bg-gradient-to-br from-primary-500 to-primary-700 items-center justify-center p-12">
            <div class="text-white text-center max-w-md">
                <div class="mb-8">
                    <img src="/assets/images/logo_smk.svg" alt="Logo Smart School" class="w-32 h-32 mx-auto mb-6 drop-shadow-lg">
                    <h1 class="text-4xl font-bold mb-3">SMART SCHOOL</h1>
                    <p class="text-xl text-blue-100">SMKN 04 JEMBER</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 mt-8">
                    <p class="text-lg leading-relaxed">Sistem manajemen sekolah yang modern dan terintegrasi untuk kemudahan administrasi</p>
                </div>
            </div>
        </div>

        <!-- Bagian Kanan: Form Login -->
        <div class="w-full md:w-1/2 flex items-center justify-center px-6 py-12 md:px-8 lg:px-12">
            <div class="w-full max-w-md">
                <!-- Logo Mobile -->
                <div class="md:hidden mb-8 text-center">
                    <img src="/assets/images/logo_smk.svg" alt="Logo Smart School" class="w-20 h-20 mx-auto mb-4">
                    <h2 class="text-2xl font-bold text-primary-600">SMART SCHOOL</h2>
                    <p class="text-sm text-gray-600">SMKN 04 JEMBER</p>
                </div>

                <!-- Card Form -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <!-- Salam Pembuka -->
                    <div class="mb-8 text-center">
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Selamat Datang!</h2>
                        <p class="text-gray-600">Masuk untuk mengakses <span class="text-primary-500 font-semibold">dashboard</span></p>
                    </div>

                    <!-- Notifikasi Error -->
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg" role="alert">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-semibold text-red-800">Error!</p>
                                    <p class="text-sm text-red-700">{{ $errors->first('email') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Form Login -->
                    <form action="{{ route('login') }}" method="POST" class="space-y-5">
                        @csrf
                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input type="text" name="email" id="email" value="{{ old('email') }}"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-gray-50 placeholder-gray-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition duration-200 outline-none"
                                    placeholder="Masukkan email Anda" required />
                            </div>
                        </div>
                        
                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input type="password" name="password" id="password"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl bg-gray-50 placeholder-gray-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition duration-200 outline-none"
                                    placeholder="Masukkan password Anda" required />
                            </div>
                        </div>
                        
                        <!-- Login Button -->
                        <div class="pt-2">
                            <button type="submit"
                                class="w-full py-3.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl shadow-lg hover:from-primary-600 hover:to-primary-700 hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200">
                                Masuk
                            </button>
                        </div>
                    </form>

                    <!-- Lupa Password -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">Lupa password? <a href="#" class="text-primary-600 font-semibold hover:text-primary-700 transition">Klik disini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
