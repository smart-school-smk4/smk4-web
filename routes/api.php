<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Api\AbsensiApiController;
use App\Http\Controllers\Api\AbsensiLaporanController;
use App\Http\Controllers\Api\AbsensiSiswaApiController;
use App\Http\Controllers\Api\BellController as ApiBellController;
use App\Http\Controllers\Api\DevicesApiController;
use App\Http\Controllers\Api\DeviceStudentController;
use App\Http\Controllers\Api\SiswaApiController;
use App\Http\Controllers\belController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingPresensiController;

Route::prefix('bel')->group(function () {
    Route::post('/ring', [belController::class, 'ring'])->name('api.bel.ring');
    Route::post('/sync', [belController::class, 'syncSchedule'])->name('api.bel.sync');
    Route::get('/status', [belController::class, 'status'])->name('api.bel.status');
    Route::get('/next-schedule', [belController::class, 'getNextSchedule'])->name('api.bel.next-schedule');
    Route::put('/{id}/toggle-status', [belController::class, 'toggleStatus'])->name('api.bel.toggle-status');
    Route::post('/activate-all', [belController::class, 'activateAll'])->name('api.bel.activate-all');
    Route::post('/deactivate-all', [belController::class, 'deactivateAll'])->name('api.bel.deactivate-all');

});

// Endpoint untuk menerima data bel dari ESP32
Route::post('/bell-events/manual', [ApiBellController::class, 'storeManualEvent']);
Route::post('/bell-events/schedule', [ApiBellController::class, 'storeScheduleEvent']);
Route::get('/bell-history', [ApiBellController::class, 'getHistory']);

Route::prefix('announcements')->group(function () {
    // CRUD operations
    Route::post('/store', [AnnouncementController::class, 'store']);
    Route::get('/{id}', [AnnouncementController::class, 'details']);
    Route::delete('/{id}', [AnnouncementController::class, 'destroy']);

    // Status checks
    Route::get('/mqtt/status', [AnnouncementController::class, 'checkMqtt']);

    // Relay control
    Route::post('/relay/control', [AnnouncementController::class, 'controlRelay']);
    Route::get('/relay/status', [AnnouncementController::class, 'relayStatus']);

    // Announcement status
    Route::post('/status', [AnnouncementController::class, 'announcementStatus']);
});

#Presensi

Route::get('/siswa', [SiswaApiController::class, 'index']);
Route::get('/devices', [DevicesApiController::class, 'index']);

Route::post('/absensi-siswa', [AbsensiApiController::class, 'store']);
Route::get('/absensi-siswa/{siswa}/status', [AbsensiApiController::class, 'getStatus']);

// Legacy API (untuk backward compatibility)
Route::post('/absensi-siswa-legacy', [AbsensiSiswaApiController::class, 'store']);

Route::get('/devices/{device}/students', [DeviceStudentController::class, 'index']);

// Device mode polling endpoint (untuk Flask pull mode dari server)
Route::get('/devices/{device}/mode', [SettingPresensiController::class, 'getDeviceMode'])->name('api.devices.mode');

// Get attendance schedule for validation
Route::get('/schedule', [SettingPresensiController::class, 'getSchedule'])->name('api.schedule');

Route::get('/laporan-absensi', AbsensiLaporanController::class);
