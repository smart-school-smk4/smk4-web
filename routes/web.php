<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BelController;
use App\Http\Controllers\GuruController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\RuanganController;

// Public routes
Route::get('/', [IndexController::class, 'index'])->name('index');

// Authentication routes
Route::controller(LoginController::class)->group(function () {
    Route::get('login', 'index')->name('login');
    Route::post('login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Admin prefix routes
    Route::prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');


        // Siswa
        Route::controller(SiswaController::class)->group(function () {
            Route::get('/siswa', 'index')->name('admin.siswa.index'); // halaman utama siswa
            Route::get('/siswa/create', 'create')->name('admin.siswa.create'); // form tambah siswa
            Route::post('/siswa', 'store')->name('admin.siswa.store'); // simpan siswa baru
            Route::get('/siswa/{id}/edit', 'edit')->name('admin.siswa.edit'); // form edit siswa
            Route::put('/siswa/{id}', 'update')->name('admin.siswa.update'); // simpan update siswa
            Route::delete('/siswa/{id}', 'destroy')->name('admin.siswa.destroy'); // hapus siswa
        });

        

        // Guru
        Route::get('/guru', [GuruController::class, 'index'])->name('admin.guru');

        // Kelas
        Route::resource('kelas', KelasController::class)->parameters([
            'kelas' => 'kelas',
        ])->names([
            'index' => 'admin.kelas.index',
            'create' => 'admin.kelas.create',
            'store' => 'admin.kelas.store',
            'edit' => 'admin.kelas.edit',
            'update' => 'admin.kelas.update',
            'destroy' => 'admin.kelas.destroy',
        ]);
        // Jurusan
        Route::resource('jurusan', JurusanController::class)->names([
            'index' => 'admin.jurusan.index',
            'create' => 'admin.jurusan.create',
            'store' => 'admin.jurusan.store',
            'edit' => 'admin.jurusan.edit',
            'update' => 'admin.jurusan.update',
            'destroy' => 'admin.jurusan.destroy',
        ]);
        // Ruangan
        Route::resource('ruangan', RuanganController::class)->names([
            'index' => 'admin.ruangan.index',
            'create' => 'admin.ruangan.create',
            'store' => 'admin.ruangan.store',
            'edit' => 'admin.ruangan.edit',
            'update' => 'admin.ruangan.update',
            'destroy' => 'admin.ruangan.destroy',
        ]);
        // Presensi
        Route::controller(PresensiController::class)->group(function () {
            Route::get('/presensi/siswa', 'indexSiswa')->name('admin.presensi.siswa');
            Route::get('/presensi/guru', 'indexGuru')->name('admin.presensi.guru');
        });

        // Laporan
        Route::get('/laporan', [LaporanController::class, 'index'])->name('admin.laporan');

        // Bell System
        Route::prefix('bel')->controller(BelController::class)->group(function () {
            Route::get('/', 'index')->name('bel.index');
            Route::get('/create', 'create')->name('bel.create');
            Route::post('/', 'store')->name('bel.store');
            Route::get('/{id}/edit', 'edit')->name('bel.edit');
            Route::put('/{id}', 'update')->name('bel.update');
            Route::delete('/{id}', 'destroy')->name('bel.delete');
            Route::delete('/', 'deleteAll')->name('bel.delete-all');
            Route::get('/history', 'history')->name('bel.history');
        });

        // Announcement System
        Route::prefix('announcement')->controller(AnnouncementController::class)->group(function () {
            Route::get('/', 'index')->name('announcement.index');
            Route::post('/', 'store')->name('announcement.store');
            Route::get('/history', 'history')->name('announcement.history');
            Route::get('/{announcement}', 'show')->name('announcement.show');
            Route::delete('/{announcement}', 'destroy')->name('announcement.destroy');
            Route::post('/tts-preview', 'ttsPreview')->name('announcement.ttsPreview');
        });
    });
});