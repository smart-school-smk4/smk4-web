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
            Route::get('/siswa', 'index2')->name('admin.siswa');
            Route::get('/siswa/create', 'create')->name('siswa.create');
            Route::post('/siswa', 'store')->name('siswa.store');
        });

        // Guru
        Route::get('/guru', [GuruController::class, 'index'])->name('admin.guru');

        // Kelas
        Route::get('/kelas', [KelasController::class, 'index'])->name('admin.kelas');

        // Jurusan
        Route::get('/jurusan', [JurusanController::class, 'index'])->name('admin.jurusan');

        // Ruangan
        Route::get('/ruangan', [RuanganController::class, 'index'])->name('admin.ruangan');

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
        });

        // Announcement System
        Route::prefix('pengumuman')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index'])->name('announcements.index');
            Route::post('/send', [AnnouncementController::class, 'send'])->name('announcements.send');
            Route::post('/stop-manual', [AnnouncementController::class, 'stopManual'])->name('announcements.stop-manual');
            Route::get('/active', [AnnouncementController::class, 'checkActive'])->name('announcements.active');
            Route::get('/mqtt-status', [AnnouncementController::class, 'mqttStatus'])->name('announcements.mqttStatus');
            Route::get('/history', [AnnouncementController::class, 'history'])->name('announcements.history');
        });
    });
});