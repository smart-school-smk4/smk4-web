<?php

namespace App\Http\Controllers;

use App\Models\JadwalBel;
use App\Models\Status;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BelController extends Controller
{
    protected $mqttService;
    protected $mqttConfig;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
        $this->mqttConfig = config('mqtt');
        
    }

    protected function initializeMqttSubscriptions()
    {
        try {
            // Subscribe ke topik status response
            $this->mqttService->subscribe(
                $this->mqttConfig['topics']['responses']['status'],
                function (string $topic, string $message) {
                    $this->handleStatusResponse($message);
                }
            );

            // Subscribe ke topik acknowledgment
            $this->mqttService->subscribe(
                $this->mqttConfig['topics']['responses']['ack'],
                function (string $topic, string $message) {
                    $this->handleAckResponse($message);
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to initialize MQTT subscriptions: ' . $e->getMessage());
        }
    }

    protected function handleStatusResponse(string $message)
    {
        try {
            $data = json_decode($message, true);
            // Validasi payload
            if (!is_array($data)) {
                Log::error('Invalid status data format');
                return;
            }
            // Pastikan semua kunci penting ada
            $requiredKeys = ['rtc', 'dfplayer', 'rtc_time', 'last_communication', 'last_sync'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $data)) {
                    Log::error("Missing required key in status data: {$key}");
                    return;
                }
            }
            // Simpan data ke database
            Status::updateOrCreate(
                ['id' => 1],
                [
                    'rtc' => $data['rtc'] ?? false,
                    'dfplayer' => $data['dfplayer'] ?? false,
                    'rtc_time' => $data['rtc_time'] ?? null,
                    'last_communication' => Carbon::createFromTimestamp($data['last_communication'] ?? 0),
                    'last_sync' => Carbon::createFromTimestamp($data['last_sync'] ?? 0)
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error handling status response: ' . $e->getMessage());
        }
    }

    protected function handleAckResponse(string $message)
    {
        try {
            $data = json_decode($message, true);
            
            if (isset($data['action'])) {
                $action = $data['action'];
                $message = $data['message'] ?? '';
                
                if ($action === 'sync_ack') {
                    Status::updateOrCreate(
                        ['id' => 1],
                        ['last_sync' => Carbon::now()]
                    );
                    Log::info('Schedule sync acknowledged: ' . $message);
                } elseif ($action === 'ring_ack') {
                    Log::info('Bell ring acknowledged: ' . $message);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error handling ack response: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            // Ambil data utama terlepas dari koneksi MQTT
            $query = JadwalBel::query();
            
            if ($request->filled('hari')) {
                $query->where('hari', $request->hari);
            }
            
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('hari', 'like', '%'.$request->search.'%')
                      ->orWhere('file_number', 'like', '%'.$request->search.'%');
                });
            }
            
            $schedules = $query->orderBy('hari')->orderBy('waktu')->paginate(10);
            $status = Status::firstOrCreate(['id' => 1]);
            
            // Jadwal hari ini
            $todaySchedules = JadwalBel::where('hari', Carbon::now()->isoFormat('dddd'))
                ->orderBy('waktu')
                ->get();
                
            // Jadwal berikutnya
            $nextSchedule = JadwalBel::where('hari', Carbon::now()->isoFormat('dddd'))
                ->where('waktu', '>', Carbon::now()->format('H:i:s'))
                ->orderBy('waktu')
                ->first();
    
            // Cek koneksi MQTT tanpa menghentikan eksekusi jika error
            try {
                $mqttStatus = $this->mqttService->isConnected() ? 'Connected' : 'Disconnected';
            } catch (\Exception $e) {
                $mqttStatus = 'Disconnected';
                Log::error('MQTT check failed: ' . $e->getMessage());
            }
    
            return view('admin.bel.index', [
                'schedules' => $schedules,
                'todaySchedules' => $todaySchedules,
                'nextSchedule' => $nextSchedule,
                'status' => $status,
                'mqttStatus' => $mqttStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in index method: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data jadwal');
        }
    }


    public function create()
    {
        return view('admin.bel.create', [
            'days' => JadwalBel::DAYS,
            'default_file' => '0001'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSchedule($request);
        
        try {
            $schedule = JadwalBel::create($validated);
            
            $this->syncSchedules();
            $this->logActivity('Jadwal dibuat', $schedule);
            
            return redirect()
                ->route('bel.index')
                ->with([
                    'success' => 'Jadwal berhasil ditambahkan',
                    'scroll_to' => 'schedule-'.$schedule->id
                ]);
        } catch (\Exception $e) {
            Log::error('Gagal menambah jadwal: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal menambah jadwal: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $schedule = JadwalBel::findOrFail($id);
        
        return view('admin.bel.edit', [
            'schedule' => $schedule,
            'days' => JadwalBel::DAYS
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateSchedule($request);
        $schedule = JadwalBel::findOrFail($id);
        
        try {
            $schedule->update($validated);
            
            $this->syncSchedules();
            $this->logActivity('Jadwal diperbarui', $schedule);
            
            return redirect()
                ->route('bel.index')
                ->with([
                    'success' => 'Jadwal berhasil diperbarui',
                    'scroll_to' => 'schedule-'.$schedule->id
                ]);
        } catch (\Exception $e) {
            Log::error('Gagal update jadwal ID '.$schedule->id.': ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $schedule = JadwalBel::findOrFail($id);
        
        try {
            $schedule->delete();
            
            $this->syncSchedules();
            $this->logActivity('Jadwal dihapus', $schedule);
            
            return redirect()
                ->route('bel.index')
                ->with('success', 'Jadwal berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal hapus jadwal ID '.$schedule->id.': ' . $e->getMessage());
            return back()
                ->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    public function deleteAll()
    {
        try {
            JadwalBel::truncate();
            $this->syncSchedules();
            
            return redirect()
                ->route('bel.index')
                ->with('success', 'Semua jadwal berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal hapus semua jadwal: ' . $e->getMessage());
            return back()
                ->with('error', 'Gagal menghapus semua jadwal: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $schedule = JadwalBel::findOrFail($id);
            $newStatus = !$schedule->is_active;
            $schedule->is_active = $newStatus;
            $schedule->save();
    
            // Return JSON response for AJAX requests
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status jadwal berhasil diubah',
                    'is_active' => $newStatus
                ]);
            }
    
            // Fallback for non-AJAX requests
            return redirect()->back()->with('success', 'Status jadwal berhasil diubah');
            
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengubah status: ' . $e->getMessage()
                ], 500);
            }
    
            return redirect()->back()->with('error', 'Gagal mengubah status');
        }
    }

    public function activateAll()
    {
        try {
            JadwalBel::query()->update(['is_active' => true]);
            $this->syncSchedules();
            
            return response()->json([
                'success' => true,
                'message' => 'Semua jadwal diaktifkan'
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal aktifkan semua jadwal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan semua jadwal'
            ], 500);
        }
    }

    public function deactivateAll()
    {
        try {
            JadwalBel::query()->update(['is_active' => false]);
            $this->syncSchedules();
            
            return response()->json([
                'success' => true,
                'message' => 'Semua jadwal dinonaktifkan'
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal nonaktifkan semua jadwal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menonaktifkan semua jadwal'
            ], 500);
        }
    }

    public function ring(Request $request)
    {
        $validated = $request->validate([
            'file_number' => 'required|string|size:4',
            'repeat' => 'sometimes|integer|min:1|max:10',
            'volume' => 'sometimes|integer|min:0|max:30'
        ]);
        try {
            $message = json_encode([
                'action' => 'ring',
                'timestamp' => Carbon::now()->toDateTimeString(),
                'file_number' => $validated['file_number'],
                'repeat' => $validated['repeat'] ?? 1,
                'volume' => $validated['volume'] ?? 15
            ]);
            $this->mqttService->publish(
                $this->mqttConfig['topics']['commands']['ring'],
                $message,
                1
            );
            return response()->json([
                'success' => true,
                'message' => 'Perintah bel berhasil dikirim',
                'data' => [
                    'file_number' => $validated['file_number'],
                    'timestamp' => Carbon::now()->toDateTimeString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim perintah bel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function status()
    {
        try {
            $message = json_encode([
                'action' => 'get_status',
                'timestamp' => Carbon::now()->toDateTimeString()
            ]);
            $this->mqttService->publish(
                $this->mqttConfig['topics']['commands']['status'],
                $message,
                1
            );
            $status = Status::firstOrCreate(['id' => 1]);
            return response()->json([
                'success' => true,
                'message' => 'Permintaan status terkirim',
                'data' => [
                    'rtc' => $status->rtc,
                    'dfplayer' => $status->dfplayer,
                    'rtc_time' => $status->rtc_time,
                    'last_communication' => $status->last_communication,
                    'last_sync' => $status->last_sync,
                    'mqtt_status' => $this->mqttService->isConnected(),
                    'status' => $status->status ?? 'unknown' // Default value jika kolom kosong
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal meminta status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal meminta status perangkat: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function syncJadwalToEsp($schedules)
    {
        try {
            $message = json_encode([
                'action' => 'sync',
                'timestamp' => Carbon::now()->toDateTimeString(),
                'schedules' => $schedules
            ]);

            $this->mqttService->publish(
                $this->mqttConfig['topics']['commands']['sync'],
                $message,
                1
            );

            Log::info("Sync schedules sent to MQTT", ['count' => count($schedules)]);
        } catch (\Exception $e) {
            Log::error('Error syncing schedules to MQTT: ' . $e->getMessage());
        }
    }

    public function syncSchedule()
    {
        try {
            $schedules = JadwalBel::active()
                ->get()
                ->map(function ($item) {
                    return [
                        'hari' => $item->hari,
                        'waktu' => Carbon::parse($item->waktu)->format('H:i'), // Pastikan format waktu sesuai
                        'file_number' => $item->file_number
                    ];
                });

            $message = json_encode([
                'action' => 'sync',
                'timestamp' => Carbon::now()->toDateTimeString(),
                'schedules' => $schedules
            ]);

            Log::info("Sync message sent to MQTT", ['message' => $message]); // Debugging

            $this->mqttService->publish(
                $this->mqttConfig['topics']['commands']['sync'],
                $message,
                1
            );

            Status::updateOrCreate(
                ['id' => 1], 
                ['last_sync' => Carbon::now()]
            );

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil disinkronisasi',
                'data' => [
                    'count' => $schedules->count(),
                    'last_sync' => Carbon::now()->toDateTimeString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal sync jadwal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyinkronisasi jadwal: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function syncSchedules()
    {
        try {
            $schedules = JadwalBel::active()
                ->get()
                ->map(function ($item) {
                    return [
                        'hari' => $item->hari,
                        'waktu' => Carbon::parse($item->waktu)->format('H:i:s'),
                        'file_number' => $item->file_number
                    ];
                });

            $this->syncJadwalToEsp($schedules);

            Log::info("Auto sync: " . count($schedules) . " jadwal");
        } catch (\Exception $e) {
            Log::error('Gagal auto sync: ' . $e->getMessage());
        }
    }

    public function getNextSchedule()
    {
        $now = now();
        
        // Mapping for English to Indonesian day names
        $dayMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis', 
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu'
        ];
        
        $currentDayEnglish = $now->format('l'); // Get English day name (e.g. "Saturday")
        $currentDay = $dayMap[$currentDayEnglish] ?? $currentDayEnglish; // Convert to Indonesian
        
        $currentTime = $now->format('H:i:s');
    
        // Correct day order (Monday-Sunday)
        $dayOrder = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 7
        ];
    
        // 1. First try to find today's upcoming ACTIVE schedules
        $todaysSchedule = JadwalBel::where('is_active', true)
            ->where('hari', $currentDay)
            ->where('waktu', '>', $currentTime)
            ->orderBy('waktu')
            ->first();
    
        if ($todaysSchedule) {
            return $this->formatScheduleResponse($todaysSchedule);
        }
    
        // 2. If no more today, find the next ACTIVE schedule in the week
        $allSchedules = JadwalBel::where('is_active', true)
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('waktu')
            ->get();
    
        $currentDayValue = $dayOrder[$currentDay] ?? 0;
        $closestSchedule = null;
        $minDayDiff = 8; // More than 7 days
    
        foreach ($allSchedules as $schedule) {
            $scheduleDayValue = $dayOrder[$schedule->hari] ?? 0;
            
            // Calculate days difference
            $dayDiff = ($scheduleDayValue - $currentDayValue + 7) % 7;
            
            // If same day but time passed, add 7 days
            if ($dayDiff === 0 && $schedule->waktu <= $currentTime) {
                $dayDiff = 7;
            }
    
            // Find schedule with smallest day difference
            if ($dayDiff < $minDayDiff) {
                $minDayDiff = $dayDiff;
                $closestSchedule = $schedule;
            }
        }
    
        if ($closestSchedule) {
            return $this->formatScheduleResponse($closestSchedule);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada jadwal aktif yang akan datang'
        ]);
    }
    
    private function formatScheduleResponse($schedule)
    {
        return response()->json([
            'success' => true,
            'next_schedule' => [
                'hari' => $schedule->hari,
                'time' => $schedule->waktu,
                'file_number' => $schedule->file_number,
                'is_active' => $schedule->is_active
            ]
        ]);
    }

    protected function validateSchedule(Request $request)
    {
        return $request->validate([
            'hari' => 'required|in:' . implode(',', JadwalBel::DAYS),
            'waktu' => 'required|date_format:H:i',
            'file_number' => 'required|string|size:4',
            'is_active' => 'sometimes|boolean'
        ]);
    }

    protected function logActivity($action, JadwalBel $schedule)
    {
        Log::info("{$action} - ID: {$schedule->id}, Hari: {$schedule->hari}, Waktu: {$schedule->waktu}, File: {$schedule->file_number}");
    }

    protected function logMqttActivity($action, $message)
    {
        $this->mqttService->publish(
            $this->mqttConfig['topics']['system']['logs'],
            json_encode([
                'action' => $action,
                'message' => $message,
                'timestamp' => Carbon::now()->toDateTimeString()
            ]),
            0
        );
    }
    
}