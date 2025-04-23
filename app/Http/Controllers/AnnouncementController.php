<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
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
        $ruangans = Ruangan::pluck('nama_ruangan')->toArray();
        $activeAnnouncements = Announcement::where('is_active', true)->get();

        if (empty($ruangans)) {
            $ruangans = ['ruang1', 'ruang2', 'ruang3'];
        }

        return view('admin.announcements.index', compact('ruangans', 'activeAnnouncements'));
    }

    /**
     * Display announcement history page
     */
    public function history()
    {
        return view('admin.announcements.history');
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
            'remaining' => $active ? (Cache::get('active_announcement_end') - time()) : null
        ]);
    }

    /**
     * Get active announcements
     */
    public function activeAnnouncements()
    {
        $announcements = Announcement::with('ruangans')
            ->where('is_active', true)
            ->orderBy('sent_at', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'content' => $item->content,
                    'target_ruangans' => $item->target_ruangans,
                    'sent_at' => $item->sent_at,
                    'status' => $item->status,
                    'ruangans' => $item->ruangans
                ];
            });

        return response()->json($announcements);
    }

    /**
     * Get announcement history (for API)
     */
    public function getHistory(Request $request)
    {
        $perPage = 10;
        $query = Announcement::where('is_active', false)
            ->orderBy('sent_at', 'desc');

        // Filter by type
        if ($request->filter && in_array($request->filter, ['tts', 'manual'])) {
            $query->where('type', $request->filter);
        }

        // Filter by status
        if ($request->status && in_array($request->status, ['completed', 'stopped'])) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->search) {
            $query->where('content', 'like', '%'.$request->search.'%');
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'from' => $paginated->firstItem(),
            'to' => $paginated->lastItem(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage()
        ]);
    }

    /**
     * Get announcement details
     */
    public function announcementDetails($id)
    {
        $announcement = Announcement::with('ruangans')->findOrFail($id);
        
        return response()->json([
            'id' => $announcement->id,
            'type' => $announcement->type,
            'content' => $announcement->content,
            'target_ruangans' => $announcement->target_ruangans,
            'sent_at' => $announcement->sent_at,
            'status' => $announcement->status,
            'audio_url' => $announcement->audio_url,
            'ruangans' => $announcement->ruangans
        ]);
    }

    /**
     * Send announcement to MQTT
     */
    public function send(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'type' => 'required|in:tts,manual',
            'content' => 'nullable|required_if:type,tts|string|max:500',
            'ruangans' => 'required|array|min:1',
            'ruangans.*' => 'string',
        ]);

        if ($validated['type'] === 'tts' && empty(config('services.voicerss.key'))) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan Text-to-Speech tidak tersedia saat ini.'
            ], 400);
        }
    
        try {
            // Persiapkan data awal
            $announcementData = [
                'type' => $validated['type'],
                'target_ruangans' => $validated['ruangans'],
                'sent_at' => now(),
                'is_active' => true,
                'status' => 'processing'
            ];
    
            // Handle TTS
            if ($validated['type'] === 'tts') {
                $announcementData['content'] = $validated['content'];
                
                // Generate audio dan simpan URL
                $audioUrl = $this->generateTtsAudio($validated['content']);
                $announcementData['audio_url'] = $audioUrl;
                
                // TTS langsung dianggap selesai
                $announcementData['is_active'] = false;
                $announcementData['status'] = 'completed';
            }
    
            // Buat pengumuman
            $announcement = Announcement::create($announcementData);
    
            // Siapkan payload MQTT
            $payload = [
                'type' => $validated['type'],
                'target_ruangans' => $validated['ruangans'],
                'timestamp' => now()->toDateTimeString(),
                'announcement_id' => $announcement->id
            ];
    
            // Tambahkan data khusus TTS
            if ($validated['type'] === 'tts') {
                $payload['content'] = $validated['content'];
                $payload['audio_url'] = $audioUrl; // Gunakan variabel yang sudah digenerate
            }
    
            // Kirim via MQTT
            $this->mqttService->sendAnnouncement($payload);
    
            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dikirim!',
                'audio_url' => $audioUrl ?? null // Sertakan audio_url dalam response
            ]);
    
        } catch (\Exception $e) {
            Log::error('Announcement error: ' . $e->getMessage());
            
            // Hapus record jika gagal setelah create
            if (isset($announcement)) {
                $announcement->delete();
            }
    
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
                ->where('type', 'manual')
                ->update([
                    'is_active' => false,
                    'status' => 'completed'
                ]);

            $payload = [
                'type' => 'manual',
                'action' => 'deactivate_relay',
                'timestamp' => now()->toDateTimeString(),
            ];

            $this->mqttService->publish('bel/sekolah/pengumuman', json_encode($payload), 1, false);

            // Clear active announcement cache
            Cache::forget('active_announcement');
            Cache::forget('active_announcement_type');

            return response()->json([
                'success' => true,
                'message' => 'Relay ruangan berhasil dimatikan!',
            ]);
        } catch (\Exception $e) {
            Log::error('Stop manual error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mematikan relay: ' . $e->getMessage(),
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
                    'type' => 'manual',
                    'action' => 'deactivate_relay',
                    'announcement_id' => $announcement->id,
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
     * Generate TTS audio using VoiceRSS
     */
    private function generateTtsAudio($text)
    {
        $apiKey = config('services.voicerss.key');
        
        // Validate API key configuration
        if (empty($apiKey)) {
            Log::error('VoiceRSS API Key not configured');
            return $this->getTtsFallback($text); // Use fallback instead of throwing exception
        }

        try {
            // Generate unique filename
            $filename = 'tts/'.md5($text.'_'.microtime()).'.mp3';
            $storagePath = Storage::disk('public')->path($filename);
            
            // Create directory if not exists
            Storage::disk('public')->makeDirectory('tts');

            $response = Http::timeout(20)
                ->retry(3, 500)
                ->get('https://api.voicerss.org/', [
                    'key' => $apiKey,
                    'hl' => 'id-id',
                    'src' => $text,
                    'r' => '0',
                    'c' => 'mp3',
                    'f' => '44khz_16bit_stereo',
                ]);

            if (!$response->successful()) {
                Log::error('VoiceRSS API Error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return $this->getTtsFallback($text);
            }

            // Save audio file
            Storage::disk('public')->put($filename, $response->body());

            // Verify file was saved
            if (!Storage::disk('public')->exists($filename)) {
                Log::error('Failed to save TTS file', ['path' => $filename]);
                return $this->getTtsFallback($text);
            }

            return Storage::url($filename);

        } catch (\Exception $e) {
            Log::error('TTS Generation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getTtsFallback($text);
        }
    }

    private function getTtsFallback($text)
    {
        try {
            // Create simple fallback audio
            $filename = 'tts/fallback_'.md5($text).'.mp3';
            $path = Storage::disk('public')->path($filename);
            
            // Generate basic audio file using shell_exec or other method
            if (!Storage::disk('public')->exists($filename)) {
                $command = "text2wave -o {$path} -eval '(language_indonesian)'";
                shell_exec("echo \"{$text}\" | {$command}");
            }
            
            return Storage::url($filename);
        } catch (\Exception $e) {
            Log::error('Fallback TTS failed', ['error' => $e->getMessage()]);
            return null; // Return null if both main and fallback fail
        }
    }
}