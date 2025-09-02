<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Api\AbsensiApiController;
use App\Http\Controllers\Api\AbsensiLaporanController;
use App\Http\Controllers\Api\AbsensiSiswaApiController;
use App\Http\Controllers\API\BellController;
use App\Http\Controllers\Api\DevicesApiController;
use App\Http\Controllers\Api\DeviceStudentController;
use App\Http\Controllers\Api\SiswaApiController;
use App\Http\Controllers\BelController;
use Illuminate\Support\Facades\Route;

Route::prefix('bel')->group(function () {
    Route::post('/ring', [BelController::class, 'ring'])->name('api.bel.ring');
    Route::post('/sync', [BelController::class, 'syncSchedule'])->name('api.bel.sync');
    Route::get('/status', [BelController::class, 'status'])->name('api.bel.status');
    Route::get('/next-schedule', [BelController::class, 'getNextSchedule'])->name('api.bel.next-schedule');
    Route::put('/{id}/toggle-status', [BelController::class, 'toggleStatus'])->name('api.bel.toggle-status');
    Route::post('/activate-all', [BelController::class, 'activateAll'])->name('api.bel.activate-all');
    Route::post('/deactivate-all', [BelController::class, 'deactivateAll'])->name('api.bel.deactivate-all');

});

// Endpoint untuk menerima data bel dari ESP32
Route::post('/bell-events/manual', [BellController::class, 'storeManualEvent']);
Route::post('/bell-events/schedule', [BellController::class, 'storeScheduleEvent']);
Route::get('/bell-history', [BellController::class, 'getHistory']);

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

Route::get('/laporan-absensi', AbsensiLaporanController::class);
