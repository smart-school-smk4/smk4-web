<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BelController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\API\BellController;
use App\Http\Controllers\AnnouncementController;

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
Route::post('/presensi', [PresensiController::class, 'store']);

Route::prefix('admin')->group(function () {
    Route::apiResource('/siswa', SiswaController::class)->only(['index', 'store']);
});