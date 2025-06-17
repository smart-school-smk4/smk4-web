<?php

namespace App\Http\Controllers;

use App\Models\JadwalBel;
use App\Models\Status;
use App\Models\BellHistory;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BelController extends Controller
{
    protected $mqttService;
    protected $mqttConfig;
    protected const DAY_MAP = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    protected const DAY_ORDER = [
        'Senin' => 1,
        'Selasa' => 2,
        'Rabu' => 3,
        'Kamis' => 4,
        'Jumat' => 5,
        'Sabtu' => 6,
        'Minggu' => 7
    ];

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
        $this->mqttConfig = config('mqtt');
        $this->initializeMqttSubscriptions();
    }

    protected function initializeMqttSubscriptions(): void
    {
        try {
            $topics = $this->mqttConfig['topics']['responses'];
            $events = $this->mqttConfig['topics']['events'];
            
            $this->mqttService->subscribe($topics['status'], fn($t, $m) => $this->handleStatusResponse($m));
            $this->mqttService->subscribe($topics['ack'], fn($t, $m) => $this->handleAckResponse($m));
            $this->mqttService->subscribe($events['bell_manual'], fn($t, $m) => $this->handleBellEvent($m, 'manual'));
            $this->mqttService->subscribe($events['bell_schedule'], fn($t, $m) => $this->handleBellEvent($m, 'schedule'));

            Log::info('Successfully subscribed to MQTT topics');
        } catch (\Exception $e) {
            Log::error('Failed to initialize MQTT subscriptions: ' . $e->getMessage());
        }
    }

    protected function handleBellEvent(string $message, string $triggerType): void
    {
        Log::debug("Processing {$triggerType} bell", ['raw_message' => $message]);
        try {
            $data = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
            
            $requiredFields = ['hari', 'waktu', 'file_number'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new \Exception("Field {$field} tidak ditemukan");
                }
            }

            $history = BellHistory::create([
                'hari' => $data['hari'],
                'waktu' => $this->normalizeWaktu($data['waktu']),
                'file_number' => $data['file_number'],
                'trigger_type' => $triggerType,
                'ring_time' => now(),
                'volume' => $data['volume'] ?? 15,
                'repeat' => $data['repeat'] ?? 1
            ]);

            Log::info("Bell {$triggerType} tersimpan", [
                'id' => $history->id,
                'hari' => $data['hari'],
                'file' => $data['file_number']
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan bell {$triggerType}", [
                'error' => $e->getMessage(),
                'message' => $message,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function normalizeWaktu(?string $time): string
    {
        if (empty($time)) return '00:00:00';
        
        try {
            return Carbon::createFromFormat('H:i:s', $time)->format('H:i:s');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('H:i', $time)->format('H:i:s');
            } catch (\Exception $e) {
                return '00:00:00';
            }
        }
    }

    protected function handleStatusResponse(string $message): void
    {
        try {
            $data = json_decode($message, true);
            
            if (!is_array($data)) {
                throw new \Exception('Invalid status data format');
            }

            $requiredKeys = ['rtc', 'dfplayer', 'rtc_time', 'last_communication', 'last_sync'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $data)) {
                    throw new \Exception("Missing required key: {$key}");
                }
            }

            Status::updateOrCreate(
                ['id' => 1],
                [
                    'rtc' => $data['rtc'],
                    'dfplayer' => $data['dfplayer'],
                    'rtc_time' => $data['rtc_time'],
                    'last_communication' => Carbon::createFromTimestamp($data['last_communication']),
                    'last_sync' => Carbon::createFromTimestamp($data['last_sync'])
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error handling status response: ' . $e->getMessage());
        }
    }

    protected function handleAckResponse(string $message): void
    {
        try {
            $data = json_decode($message, true);
            
            if (!isset($data['action'])) {
                return;
            }

            $action = $data['action'];
            $message = $data['message'] ?? '';
            
            if ($action === 'sync_ack') {
                Status::updateOrCreate(['id' => 1], ['last_sync' => Carbon::now()]);
                Log::info('Schedule sync acknowledged: ' . $message);
            } elseif ($action === 'ring_ack') {
                Log::info('Bell ring acknowledged: ' . $message);
            }
        } catch (\Exception $e) {
            Log::error('Error handling ack response: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
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
            
            $today = Carbon::now()->isoFormat('dddd');
            $currentTime = Carbon::now()->format('H:i:s');
            
            return view('admin.bel.index', [
                'schedules' => $query->orderBy('hari')->orderBy('waktu')->paginate(10),
                'todaySchedules' => JadwalBel::where('hari', $today)
                    ->orderBy('waktu')
                    ->get(),
                'nextSchedule' => JadwalBel::where('hari', $today)
                    ->where('waktu', '>', $currentTime)
                    ->orderBy('waktu')
                    ->first(),
                'status' => Status::firstOrCreate(['id' => 1]),
                'mqttStatus' => $this->getMqttStatus()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in index method: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data jadwal');
        }
    }

    protected function getMqttStatus(): string
    {
        try {
            return $this->mqttService->isConnected() ? 'Connected' : 'Disconnected';
        } catch (\Exception $e) {
            Log::error('MQTT check failed: ' . $e->getMessage());
            return 'Disconnected';
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
            $this->syncSchedule();
            $this->logActivity('Jadwal dibuat', $schedule);
            
            return redirect()
                ->route('bel.index')
                ->with([
                    'success' => 'Jadwal berhasil ditambahkan',
                    'scroll_to' => 'schedule-'.$schedule->id
                ]);
        } catch (\Exception $e) {
            Log::error('Gagal menambah jadwal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambah jadwal: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        return view('admin.bel.edit', [
            'schedule' => JadwalBel::findOrFail($id),
            'days' => JadwalBel::DAYS
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateSchedule($request);
        $schedule = JadwalBel::findOrFail($id);
        
        try {
            $schedule->update($validated);
            $this->syncSchedule();
            $this->logActivity('Jadwal diperbarui', $schedule);
            
            return redirect()
                ->route('bel.index')
                ->with([
                    'success' => 'Jadwal berhasil diperbarui',
                    'scroll_to' => 'schedule-'.$schedule->id
                ]);
        } catch (\Exception $e) {
            Log::error('Gagal update jadwal ID '.$id.': ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $schedule = JadwalBel::findOrFail($id);
            $schedule->delete();
            $this->syncSchedule();
            $this->logActivity('Jadwal dihapus', $schedule);
            
            return redirect()
                ->route('bel.index')
                ->with('success', 'Jadwal berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal hapus jadwal ID '.$id.': ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    public function deleteAll()
    {
        try {
            JadwalBel::truncate();
            $this->syncSchedule();
            
            return redirect()
                ->route('bel.index')
                ->with('success', 'Semua jadwal berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal hapus semua jadwal: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus semua jadwal: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $schedule = JadwalBel::findOrFail($id);
            $newStatus = !$schedule->is_active;
            $schedule->update(['is_active' => $newStatus]);
    
            return response()->json([
                'success' => true,
                'message' => 'Status jadwal berhasil diubah',
                'is_active' => $newStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activateAll()
    {
        try {
            JadwalBel::query()->update(['is_active' => true]);
            $this->syncSchedule();
            
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
            $this->syncSchedule();
            
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
            'volume' => 'sometimes|integer|min:1|max:30',
        ]);

        // Konversi nama hari ke format enum Indonesia
        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hari = $dayMap[now()->format('l')]; // format('l') = nama hari dalam Bahasa Inggris

        try {
            $bellData = [
                'hari' => $hari,
                'waktu' => now()->format('H:i:s'),
                'file_number' => $validated['file_number'],
                'volume' => $validated['volume'],
            ];

            BellHistory::create(array_merge($bellData, [
                'trigger_type' => 'manual',
                'ring_time' => now()
            ]));

            $this->mqttService->publish(
                $this->mqttConfig['topics']['commands']['ring'],
                json_encode($validated),
                1
            );

            return response()->json([
                'success' => true,
                'message' => 'Bel manual berhasil diaktifkan',
                'data' => $bellData
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim bel manual: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan bel: ' . $e->getMessage()
            ], 500);
        }
    }

    

    public function status()
    {
        try {
            $this->mqttService->publish(
                $this->mqttConfig['topics']['commands']['status'],
                json_encode([
                    'action' => 'get_status',
                    'timestamp' => Carbon::now()->toDateTimeString()
                ]),
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
                    'status' => $status->status ?? 'unknown'
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

    protected function getFormattedSchedules()
    {
        return JadwalBel::active()
            ->get()
            ->map(function ($item) {
                return [
                    'hari' => $item->hari,
                    'waktu' => Carbon::parse($item->waktu)->format('H:i'), // Ensure "H:i" format
                    'file_number' => $item->file_number,
                    'volume' => (int)$item->volume ?? 15, // Force integer type
                    'repeat' => (int)$item->repeat ?? 1,  // Force integer type
                    'is_active' => (bool)$item->is_active // Force boolean
                ];
            })->toArray();
    }
    
    public function syncSchedule()
    {
        Log::debug('Sync request received from frontend');
        $payload = [
            'action' => 'sync',
            'timestamp' => now()->toDateTimeString(),
            'schedules' => $this->getFormattedSchedules()
        ];
    
        $this->mqttService->publish(
            'bel/sekolah/command/sync',
            json_encode($payload),
            1, // QoS 1
            false // Not retained
        );
    
        return response()->json([
            'success' => true,
            'message' => 'Sync command sent',
            'payload' => $payload // For debugging
        ]);
    }



    public function getNextSchedule()
    {
        $now = Carbon::now();
        $currentDay = self::DAY_MAP[$now->format('l')] ?? $now->format('l');
        $currentTime = $now->format('H:i:s');
    
        // 1. Try to find today's upcoming ACTIVE schedules
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
    
        $currentDayValue = self::DAY_ORDER[$currentDay] ?? 0;
        $closestSchedule = null;
        $minDayDiff = 8;
    
        foreach ($allSchedules as $schedule) {
            $scheduleDayValue = self::DAY_ORDER[$schedule->hari] ?? 0;
            $dayDiff = ($scheduleDayValue - $currentDayValue + 7) % 7;
            
            if ($dayDiff === 0 && $schedule->waktu <= $currentTime) {
                $dayDiff = 7;
            }
    
            if ($dayDiff < $minDayDiff) {
                $minDayDiff = $dayDiff;
                $closestSchedule = $schedule;
            }
        }
    
        return $closestSchedule 
            ? $this->formatScheduleResponse($closestSchedule)
            : response()->json([
                'success' => false,
                'message' => 'Tidak ada jadwal aktif yang akan datang'
            ]);
    }
    
    private function formatScheduleResponse(JadwalBel $schedule)
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

    // public function history(Request $request)
    // {
    //     try {
    //         $query = BellHistory::query()->latest('ring_time');
            
    //         if ($request->filled('date')) {
    //             $query->whereDate('ring_time', $request->date);
    //         }
            
    //         if ($request->filled('search')) {
    //             $query->where(function($q) use ($request) {
    //                 $q->where('hari', 'like', '%'.$request->search.'%')
    //                   ->orWhere('file_number', 'like', '%'.$request->search.'%')
    //                   ->orWhere('trigger_type', 'like', '%'.$request->search.'%');
    //             });
    //         }
            
    //         return view('admin.bel.history', [
    //             'histories' => $query->paginate(15)
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Error fetching history: ' . $e->getMessage());
    //         return back()->with('error', 'Gagal memuat riwayat bel');
    //     }
    // }
    
    public function logEvent(Request $request)
    {
        $validated = $request->validate([
            'hari' => 'required|string',
            'waktu' => 'required|date_format:H:i:s',
            'file_number' => 'required|string|size:4',
            'trigger_type' => 'required|in:schedule,manual',
            'volume' => 'sometimes|integer|min:1|max:30',
            'repeat' => 'sometimes|integer|min:1|max:5'
        ]);

        try {
            return response()->json([
                'success' => true,
                'message' => 'Bell event logged',
                'data' => BellHistory::create($validated)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log bell event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function validateSchedule(Request $request): array
{
    return $request->validate([
        'hari' => 'required|in:' . implode(',', JadwalBel::DAYS),
        'waktu' => 'required|date_format:H:i',
        'file_number' => 'required|string|size:4',
        'is_active' => 'sometimes|boolean'
    ]);
}

    protected function logActivity(string $action, JadwalBel $schedule): void
    {
        Log::info("{$action} - ID: {$schedule->id}, Hari: {$schedule->hari}, Waktu: {$schedule->waktu}, File: {$schedule->file_number}");
    }
}