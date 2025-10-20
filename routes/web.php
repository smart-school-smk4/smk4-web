<?php

use App\Http\Controllers\AbsensiGuruController;
use App\Http\Controllers\AbsensiSiswaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\BelController;
use App\Http\Controllers\BellHistoryController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanAbsensiController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\SettingPresensiController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::redirect('/', '/login');

// Authentication routes
Route::controller(LoginController::class)->group(function () {
    Route::get('login', 'index')->name('login');
    Route::post('login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Admin prefix routes
    Route::prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

        // Siswa
        Route::controller(SiswaController::class)->group(function () {
            Route::get('/siswa', 'index')->name('admin.siswa.index');             // halaman utama siswa
            Route::get('/siswa/create', 'create')->name('admin.siswa.create');    // form tambah siswa
            Route::post('/siswa', 'store')->name('admin.siswa.store');            // simpan siswa baru
            Route::get('/siswa/{id}/detail', 'detail')->name('admin.siswa.detail'); // detail siswa
            Route::get('/siswa/{id}/edit', 'edit')->name('admin.siswa.edit');     // form edit siswa
            Route::put('/siswa/{id}', 'update')->name('admin.siswa.update');      // simpan update siswa
            Route::delete('/siswa/{id}', 'destroy')->name('admin.siswa.destroy'); // hapus siswa
        });

        // Guru
        Route::controller(GuruController::class)->group(function () {
            Route::get('/guru', 'index')->name('admin.guru.index');             // halaman utama guru
            Route::get('/guru/create', 'create')->name('admin.guru.create');    // form tambah guru
            Route::post('/guru', 'store')->name('admin.guru.store');            // simpan guru baru
            Route::get('/guru/{id}/edit', 'edit')->name('admin.guru.edit');     // form edit guru
            Route::put('/guru/{id}', 'update')->name('admin.guru.update');      // simpan update guru
            Route::delete('/guru/{id}', 'destroy')->name('admin.guru.destroy'); // hapus guru
        });

        // Setting Presensi
        Route::controller(SettingPresensiController::class)->group(function () {
            Route::get('/setting_presensi', 'index')->name('admin.setting_presensi.index');
            Route::get('/setting_presensi/create', 'create')->name('admin.setting_presensi.create');
            Route::get('/setting_presensi/{id}/edit', 'edit')->name('admin.setting_presensi.edit');
            Route::post('/setting_presensi', 'store')->name('admin.setting_presensi.store');
            Route::put('/setting_presensi/{id}', 'update')->name('admin.setting_presensi.update');
            Route::delete('/setting_presensi/{id}', 'destroy')->name('admin.setting_presensi.destroy');
        });

        // Kelas
        Route::resource('kelas', KelasController::class)->parameters([
            'kelas' => 'kelas',
        ])->names([
            'index'   => 'admin.kelas.index',
            'create'  => 'admin.kelas.create',
            'store'   => 'admin.kelas.store',
            'edit'    => 'admin.kelas.edit',
            'update'  => 'admin.kelas.update',
            'destroy' => 'admin.kelas.destroy',
        ]);
        // Jurusan
        Route::resource('jurusan', JurusanController::class)->names([
            'index'   => 'admin.jurusan.index',
            'create'  => 'admin.jurusan.create',
            'store'   => 'admin.jurusan.store',
            'edit'    => 'admin.jurusan.edit',
            'update'  => 'admin.jurusan.update',
            'destroy' => 'admin.jurusan.destroy',
        ]);
        // Ruangan
        Route::resource('ruangan', RuanganController::class)->names([
            'index'   => 'admin.ruangan.index',
            'create'  => 'admin.ruangan.create',
            'store'   => 'admin.ruangan.store',
            'edit'    => 'admin.ruangan.edit',
            'update'  => 'admin.ruangan.update',
            'destroy' => 'admin.ruangan.destroy',
        ]);

        Route::resource('devices', DevicesController::class)->names([
            'index'   => 'admin.devices.index',
            'create'  => 'admin.devices.create',
            'store'   => 'admin.devices.store',
            'edit'    => 'admin.devices.edit',
            'update'  => 'admin.devices.update',
            'destroy' => 'admin.devices.destroy',
        ]);

        // Presensi Siswa
        Route::controller(AbsensiSiswaController::class)->group(function () {
            Route::get('/presensi/siswa', 'index')->name('admin.presensi.siswa');
            Route::get('/presensi/siswa/data', 'getAbsensiData')->name('admin.presensi.siswa.data');
        });

        // Presensi Guru
        Route::controller(AbsensiGuruController::class)->group(function () {
            Route::get('/presensi/guru', 'index')->name('admin.presensi.guru');
        });

        // Laporan
        Route::controller(LaporanAbsensiController::class)->group(function () {
            Route::get('/laporan', 'index')->name('admin.laporan');
            Route::get('/laporan/absensi', 'index')->name('admin.laporan.absensi');
            Route::get('/laporan/export', 'export')->name('admin.laporan.export');
        });

        // Bell System
        Route::prefix('bel')->controller(BelController::class)->group(function () {
            Route::get('/', 'index')->name('bel.index');
            Route::get('/create', 'create')->name('bel.create');
            Route::post('/', 'store')->name('bel.store');
            Route::get('/{id}/edit', 'edit')->name('bel.edit');
            Route::put('/{id}', 'update')->name('bel.update');
            Route::delete('/{id}', 'destroy')->name('bel.delete');
            Route::delete('/', 'deleteAll')->name('bel.delete-all');
            // Pindahkan history ke dalam group bel untuk konsistensi
            Route::prefix('history')->controller(BellHistoryController::class)->group(function () {
                Route::get('/', 'history')->name('bel.history.index');
                Route::get('/filter', 'filterHistory')->name('bel.history.filter');
                Route::delete('/{id}', 'destroy')->name('bel.history.destroy');
            });
        });

        // Announcement System
        Route::prefix('announcement')->controller(AnnouncementController::class)->group(function () {
            Route::get('/', 'index')->name('admin.announcement.index');
            Route::get('/history', 'history')->name('admin.announcement.history');
            Route::delete('/{id}', 'destroy')->name('announcement.destroy');
            Route::get('{id}/details', 'details')->name('announcement.details');

        });

    });
});
