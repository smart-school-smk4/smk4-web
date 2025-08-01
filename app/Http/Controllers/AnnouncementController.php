<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Ruangan;
use App\Http\Requests\StoreAnnouncementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\MqttService;

class AnnouncementController extends Controller
{
    // Constants for modes and actions
    const MODE_TTS = 'tts';
    const MODE_MANUAL = 'manual';
    const ACTION_ACTIVATE = 'activate';
    const ACTION_DEACTIVATE = 'deactivate';

    public function index()
    {
        try {
            $ruangans = Ruangan::orderBy('nama_ruangan')->get();
            $mqttService = app(MqttService::class);
            
            return view('admin.announcement.index', [
                'ruangans' => $ruangans,
                'mqttStatus' => $mqttService->isConnected(),
                'modes' => [self::MODE_TTS, self::MODE_MANUAL]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to load announcement index: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat halaman pengumuman');
        }
    }
    
    public function history()
    {
        try {
            $announcements = Announcement::with('ruangans')
                ->when(request('mode'), function($query, $mode) {
                    return $query->where('mode', $mode);
                })
                ->when(request('date'), function($query, $date) {
                    return $query->whereDate('sent_at', $date);
                })
                ->when(request('ruangan'), function($query, $ruanganName) {
                    return $query->whereHas('ruangans', function($q) use ($ruanganName) {
                        $q->where('nama_ruangan', $ruanganName); // Filter by name
                    });
                })
                ->orderBy('sent_at', 'desc')
                ->paginate(10)
                ->withQueryString();
                
            $ruangans = Ruangan::orderBy('nama_ruangan')->get();
            
            return view('admin.announcement.history', [
                'announcements' => $announcements,
                'ruangans' => $ruangans,
                'modes' => [self::MODE_TTS, self::MODE_MANUAL]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to load announcement history: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat riwayat pengumuman');
        }
    }

    public function store(StoreAnnouncementRequest $request)
    {
        DB::beginTransaction();
        
        try {
            // 1. Konversi nama ruangan ke ID
            $ruanganNames = $request->ruangans;
            $ruanganRecords = Ruangan::whereIn('nama_ruangan', $ruanganNames)->get();
            
            // Validasi semua nama ruangan ditemukan
            if ($ruanganRecords->count() !== count($ruanganNames)) {
                $missingRooms = array_diff(
                    $ruanganNames,
                    $ruanganRecords->pluck('nama_ruangan')->toArray()
                );
                
                throw new \Exception(
                    'Ruangan tidak ditemukan: ' . implode(', ', $missingRooms)
                );
            }

            $ruanganIds = $ruanganRecords->pluck('id')->toArray();

            // 2. Persiapkan data pengumuman
            $announcementData = [
                'mode' => $request->mode,
                'sent_at' => now(),
            ];
        
            if ($request->mode === self::MODE_TTS) {
                $announcementData['message'] = $request->message;
            }
        
            // 3. Simpan pengumuman dan relasinya
            $announcement = Announcement::create($announcementData);
            $announcement->ruangans()->sync($ruanganIds);
        
            // 4. Kirim perintah ke MQTT
            $mqttService = app(MqttService::class);
            $success = false;
            
            if ($request->mode === self::MODE_TTS) {
                $success = $mqttService->sendTTSAnnouncement(
                    $ruanganNames, // Kirim nama ruangan (bukan ID)
                    $request->message
                );
            } else {
                $success = $mqttService->sendRelayControl(
                    self::ACTION_ACTIVATE,
                    $ruanganNames, // Kirim nama ruangan (bukan ID)
                    $request->mode
                );
            }
            
            if (!$success) {
                throw new \Exception('Gagal mengirim perintah ke perangkat MQTT');
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dikirim',
                'data' => [
                    'announcement_id' => $announcement->id,
                    'ruangan' => $ruanganNames
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to store announcement', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pengumuman: ' . $e->getMessage(),
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    public function details($id)
    {
        try {
            $announcement = Announcement::with('ruangans')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'mode' => $announcement->mode,
                    'sent_at' => $announcement->sent_at->format('Y-m-d H:i:s'),
                    'message' => $announcement->message,
                    'ruangans' => $announcement->ruangans->map(function($ruangan) {
                        return [
                            'id' => $ruangan->id,
                            'nama_ruangan' => $ruangan->nama_ruangan
                        ];
                    })
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get announcement details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pengumuman'
            ], 404);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $announcement = Announcement::findOrFail($id);
            
            // Hapus relasi terlebih dahulu
            $announcement->ruangans()->detach();
            
            // Kemudian hapus pengumuman
            $announcement->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete announcement: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengumuman: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkMqtt()
    {
        $mqttService = app(MqttService::class);
        return response()->json([
            'connected' => $mqttService->isConnected()
        ]);
    }

    public function controlRelay(Request $request)
    {
        $validated = $request->validate([
            'ruangans' => 'required|array|min:1',
            'ruangans.*' => 'string|exists:ruangan,nama_ruangan', // Pastikan validasi string
            'action' => 'required|in:'.self::ACTION_ACTIVATE.','.self::ACTION_DEACTIVATE,
            'mode' => 'required|in:'.self::MODE_MANUAL.','.self::MODE_TTS
        ]);

        DB::beginTransaction();
        
        try {
            // Dapatkan ID untuk operasi database
            $ruanganIds = Ruangan::whereIn('nama_ruangan', $validated['ruangans'])
                ->pluck('id')
                ->toArray();

            $mqttService = app(MqttService::class);
            $success = $mqttService->sendRelayControl(
                $validated['action'],
                $validated['ruangans'], // Kirim NAMA ruangan ke MQTT
                $validated['mode']
            );

            if (!$success) {
                throw new \Exception('Gagal mengirim perintah ke perangkat');
            }

            // Update database menggunakan ID
            $state = $validated['action'] === self::ACTION_ACTIVATE ? 'on' : 'off';
            Ruangan::whereIn('id', $ruanganIds)->update(['relay_state' => $state]);

            // Log manual activations
            if ($validated['action'] === self::ACTION_ACTIVATE && $validated['mode'] === self::MODE_MANUAL) {
                $announcement = Announcement::create([
                    'mode' => self::MODE_MANUAL,
                    'message' => 'Pengumuman via microphone manual',
                    'sent_at' => now()
                ]);
                
                $announcement->ruangans()->sync($ruanganIds); // Gunakan ID untuk relasi
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Relay berhasil dikontrol',
                'ruangans' => $validated['ruangans'] // Kembalikan nama ruangan
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to control relay: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengontrol relay: ' . $e->getMessage()
            ], 500);
        }
    }

    public function relayStatus()
    {
        try {
            $ruangans = Ruangan::select('nama_ruangan', 'relay_state')->get(); // Hanya ambil nama dan status
            
            return response()->json([
                'success' => true,
                'data' => $ruangans->map(function($ruangan) {
                    return [
                        'nama_ruangan' => $ruangan->nama_ruangan,
                        'relay_state' => $ruangan->relay_state
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get relay status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status relay'
            ], 500);
        }
    }

    public function announcementStatus(Request $request)
    {
        try {
            $request->validate([
                'ruangans' => 'required|array|min:1',
                'ruangans.*' => 'exists:ruangan,nama_ruangan'
            ]);

            $mqttService = app(MqttService::class);
            $statuses = $mqttService->getAnnouncementStatus($request->ruangans);
            
            return response()->json([
                'success' => true,
                'data' => $statuses
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get announcement status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status pengumuman'
            ], 500);
        }
    }
}