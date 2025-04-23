<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Announcement;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    protected $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Display the announcement interface
     */
    public function index()
    {
        $rooms = Room::pluck('name')->toArray();
        $activeAnnouncements = Announcement::where('is_active', true)->get();
        $announcementHistory = Announcement::where('is_active', false)
            ->orderBy('sent_at', 'desc')
            ->paginate(10);

        if (empty($rooms)) {
            $rooms = ['ruang1', 'ruang2', 'ruang3'];
        }

        return view('admin.announcements.index', compact('rooms', 'activeAnnouncements', 'announcementHistory'));
    }

    /**
     * Get MQTT connection status
     */
    public function mqttStatus()
    {
        return response()->json([
            'connected' => $this->mqttService->isConnected(),
            'status' => Cache::get('mqtt_status', 'disconnected')
        ]);
    }

    /**
     * Check for active announcements
     */
    public function checkActive()
    {
        $active = Cache::get('active_announcement', false);
        
        return response()->json([
            'active' => $active,
            'type' => $active ? Cache::get('active_announcement_type') : null,
            'duration' => $active ? Cache::get('active_announcement_duration') : null,
            'remaining' => $active ? (Cache::get('active_announcement_end') - time()) : null
        ]);
    }

    /**
     * Get active announcements
     */
    public function activeAnnouncements()
    {
        $announcements = Announcement::where('is_active', true)
            ->orderBy('sent_at', 'desc')
            ->get();

        return response()->json($announcements);
    }

    /**
     * Get announcement history
     */
    public function announcementHistory(Request $request)
    {
        $query = Announcement::where('is_active', false)
            ->orderBy('sent_at', 'desc');

        if ($request->has('search')) {
            $query->where('content', 'like', '%'.$request->search.'%');
        }

        if ($request->has('filter') && $request->filter != 'all') {
            $query->where('type', $request->filter);
        }

        if ($request->has('sort')) {
            $query->orderBy('sent_at', $request->sort == 'oldest' ? 'asc' : 'desc');
        }

        return response()->json($query->paginate(10));
    }

    /**
     * Get announcement details
     */
    public function announcementDetails($id)
    {
        $announcement = Announcement::findOrFail($id);
        return response()->json($announcement);
    }

    /**
     * Send announcement to MQTT
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:tts,manual',
            'content' => 'nullable|required_if:type,tts|string|max:500',
            'rooms' => 'required|array|min:1',
            'rooms.*' => 'string',
            'duration' => 'nullable|integer|min:5|max:300',
        ]);

        try {
            $announcement = Announcement::create([
                'type' => $validated['type'],
                'content' => $validated['type'] === 'tts' ? $validated['content'] : null,
                'target_rooms' => $validated['rooms'],
                'duration' => $validated['type'] === 'manual' ? $validated['duration'] : null,
                'sent_at' => now(),
                'is_active' => true,
                'status' => 'processing'
            ]);

            $payload = [
                'type' => $validated['type'],
                'target_rooms' => $validated['rooms'],
                'timestamp' => now()->toDateTimeString(),
                'announcement_id' => $announcement->id
            ];

            if ($validated['type'] === 'tts') {
                $payload['content'] = $validated['content'];
                $payload['audio_url'] = $this->generateTtsAudio($validated['content']);
                $payload['auto_stop'] = true;
            } else {
                $payload['duration'] = $validated['duration'] ?? 60;
            }

            $this->mqttService->sendAnnouncement($payload);

            // Update cache for active announcement
            if ($validated['type'] === 'manual') {
                Cache::put('active_announcement', true);
                Cache::put('active_announcement_type', 'manual');
                Cache::put('active_announcement_duration', $validated['duration']);
                Cache::put('active_announcement_end', time() + $validated['duration']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dikirim!',
                'announcement_id' => $announcement->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Announcement error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pengumuman: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stop manual audio routing
     */
    public function stopManual()
    {
        try {
            Announcement::where('is_active', true)
                ->update([
                    'is_active' => false,
                    'status' => 'completed'
                ]);

            $payload = [
                'type' => 'stop_manual',
                'timestamp' => now()->toDateTimeString(),
            ];

            $this->mqttService->publish('bel/sekolah/pengumuman', json_encode($payload), 1, false);

            // Clear active announcement cache
            Cache::forget('active_announcement');
            Cache::forget('active_announcement_type');
            Cache::forget('active_announcement_duration');
            Cache::forget('active_announcement_end');

            return response()->json([
                'success' => true,
                'message' => 'Routing audio berhasil diputus!',
            ]);
        } catch (\Exception $e) {
            Log::error('Stop manual error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memutus routing audio: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stop specific announcement
     */
    public function stopAnnouncement(Request $request)
    {
        try {
            $announcement = Announcement::findOrFail($request->id);
            $announcement->update([
                'is_active' => false,
                'status' => 'stopped'
            ]);

            if ($announcement->type === 'manual') {
                $payload = [
                    'type' => 'stop_manual',
                    'timestamp' => now()->toDateTimeString(),
                ];
                $this->mqttService->publish('bel/sekolah/pengumuman', json_encode($payload), 1, false);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dihentikan!',
            ]);
        } catch (\Exception $e) {
            Log::error('Stop announcement error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan pengumuman: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Manage rooms (add, edit, delete)
     */
    public function manageRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:add,edit,delete',
            'room_name' => 'required|string|max:50',
            'old_room' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            switch ($request->action) {
                case 'add':
                    Room::create(['name' => $request->room_name]);
                    break;
                    
                case 'edit':
                    $room = Room::where('name', $request->old_room)->firstOrFail();
                    $room->update(['name' => $request->room_name]);
                    
                    // Update existing announcements that use this room
                    Announcement::whereJsonContains('target_rooms', $request->old_room)
                        ->each(function($announcement) use ($request) {
                            $updatedRooms = array_map(function($room) use ($request) {
                                return $room == $request->old_room ? $request->room_name : $room;
                            }, $announcement->target_rooms);
                            
                            $announcement->update(['target_rooms' => $updatedRooms]);
                        });
                    break;
                    
                case 'delete':
                    $room = Room::where('name', $request->room_name)->firstOrFail();
                    $room->delete();
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil di' . ($request->action == 'add' ? 'tambah' : ($request->action == 'edit' ? 'edit' : 'hapus')),
                'rooms' => Room::pluck('name')->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error('Room management error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengelola ruangan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate TTS audio using VoiceRSS
     */
    private function generateTtsAudio($text)
    {
        $apiKey = config('services.voicerss.api_key');
        
        if (!$apiKey) {
            throw new \Exception('VoiceRSS API Key tidak dikonfigurasi');
        }

        $response = Http::timeout(10)->get('https://api.voicerss.org', [
            'key' => $apiKey,
            'hl' => 'id-id',
            'src' => $text,
            'r' => '0',
            'c' => 'mp3',
            'f' => '44khz_16bit_stereo',
        ]);

        if ($response->successful()) {
            $filename = 'tts/' . uniqid() . '.mp3';
            Storage::disk('public')->put($filename, $response->body());
            return Storage::url($filename);
        }

        throw new \Exception('Gagal menghasilkan audio TTS');
    }
}