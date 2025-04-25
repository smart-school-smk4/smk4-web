<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    // Mode constants
    const MODE_REGULER = 'reguler';
    const MODE_TTS = 'tts';

    // TTS API constants
    const TTS_API_URL = 'http://api.voicerss.org/';
    const TTS_API_KEY = '90927de8275148d79080facd20fb486c';
    const TTS_DEFAULT_VOICE = 'id-id';
    const TTS_DEFAULT_SPEED = 0; // -10 to 10
    const TTS_DEFAULT_FORMAT = 'wav';

    /**
     * Show the announcement form
     */
    public function index()
    {
        $ruangan = Ruangan::with(['kelas', 'jurusan'])->get();
        $announcements = Announcement::with(['user', 'ruangans'])
                            ->latest()
                            ->paginate(10);
        
        return view('admin.announcement.index', [
            'ruangan' => $ruangan,
            'announcements' => $announcements,
            'modes' => [self::MODE_REGULER, self::MODE_TTS]
        ]);
    }

    /**
     * Handle the announcement request
     */
    public function store(Request $request)
    {
        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $mode = $request->input('mode');

        try {
            if ($mode === self::MODE_REGULER) {
                $this->handleRegularAnnouncement($request);
                $message = 'Pengumuman reguler berhasil dikirim ke ruangan terpilih.';
            } elseif ($mode === self::MODE_TTS) {
                $this->handleTTSAnnouncement($request);
                $message = 'Pengumuman TTS berhasil diproses dan dikirim.';
            }

            return redirect()->route('admin.announcement.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Announcement error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses pengumuman: ' . $e->getMessage())
                ->withInput();
        }
    }

    
    /**
    * Validate the announcement request
    */
    private function validateRequest(Request $request)
    {
        $rules = [
            'mode' => 'required|in:reguler,tts',
        ];

        if ($request->input('mode') === self::MODE_REGULER) {
            $rules['ruangan'] = 'required|array';
            $rules['ruangan.*'] = 'exists:ruangan,id';
            // Remove message requirement for regular mode
        } elseif ($request->input('mode') === self::MODE_TTS) {
            $rules['tts_text'] = 'required|string|max:1000';
            $rules['tts_voice'] = 'nullable|string';
            $rules['tts_speed'] = 'nullable|integer|min:-10|max:10';
            $rules['tts_ruangan'] = 'required|array';
            $rules['tts_ruangan.*'] = 'exists:ruangan,id';
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * Handle regular announcement
     */
    private function handleRegularAnnouncement(Request $request)
    {
        $ruanganIds = $request->input('ruangan');
        
        $ruangan = Ruangan::whereIn('id', $ruanganIds)->get();

        $payload = [
            'type' => 'reguler',
            'action' => 'activate', // Add action type
            'ruangan' => $ruangan->pluck('nama_ruangan')->toArray(),
            'timestamp' => now()->toDateTimeString()
        ];

        $this->publishToMQTT($payload);

        // Simpan ke database tanpa message
        $announcement = Announcement::create([
            'mode' => self::MODE_REGULER,
            'message' => 'Aktivasi ruangan', // Default message or empty
            'ruangan' => $ruangan->pluck('nama_ruangan')->toArray(),
            'user_id' => auth()->id,
            'sent_at' => now()
        ]);

        // Attach ruangan ke pengumuman
        $announcement->ruangans()->attach($ruanganIds);

        Log::info('Regular announcement sent', [
            'announcement_id' => $announcement->id,
            'ruangan' => $ruanganIds
        ]);
    }
    
    private function handleTTSAnnouncement(Request $request)
    {
        $text = $request->input('tts_text');
        $ruanganIds = $request->input('tts_ruangan');
        $voice = $request->input('tts_voice', self::TTS_DEFAULT_VOICE);
        $speed = $request->input('tts_speed', self::TTS_DEFAULT_SPEED);
    
        $audioContent = $this->generateTTS($text, $voice, $speed);
    
        if (!$audioContent) {
            throw new \Exception('Gagal menghasilkan audio TTS');
        }
    
        // Simpan file audio
        $fileName = 'tts/' . now()->format('YmdHis') . '.wav';
        Storage::disk('public')->put($fileName, $audioContent);
    
        $ruangan = Ruangan::whereIn('id', $ruanganIds)->get();
    
        $payload = [
            'type' => 'tts',
            'audio_url' => asset('storage/' . $fileName),
            'ruangan' => $ruangan->pluck('nama_ruangan')->toArray(),
            'timestamp' => now()->toDateTimeString()
        ];
    
        $this->publishToMQTT($payload);
    
        // Simpan ke database
        $announcement = Announcement::create([
            'mode' => self::MODE_TTS,
            'message' => $text,
            'audio_path' => $fileName,
            'voice' => $voice,
            'speed' => $speed,
            'ruangan' => $ruangan->pluck('nama_ruangan')->toArray(),
            'user_id' => auth()->id,
            'sent_at' => now()
        ]);
    
        // Attach ruangan ke pengumuman
        $announcement->ruangans()->attach($ruanganIds);
    
        Log::info('TTS announcement sent', [
            'announcement_id' => $announcement->id, // Diubah dari id() ke id
            'ruangan' => $ruanganIds,
            'text' => $text,
            'voice' => $voice,
            'speed' => $speed
        ]);
    }

    /**
     * Generate TTS audio using VoiceRSS API
     */
    private function generateTTS($text, $voice, $speed)
    {
        try {
            $response = Http::get(self::TTS_API_URL, [
                'key' => self::TTS_API_KEY,
                'hl' => $voice,
                'src' => $text,
                'r' => $speed,
                'c' => self::TTS_DEFAULT_FORMAT,
                'f' => '8khz_8bit_mono' // Lower quality for faster transmission
            ]);

            if ($response->successful()) {
                return $response->body();
            }

            Log::error('TTS API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('TTS Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Publish message to MQTT broker
     */
    private function publishToMQTT(array $payload)
    {
        try {
            $mqtt = MQTT::connection();
            $mqtt->publish('announcement/channel', json_encode($payload), 0);
            $mqtt->disconnect();
        } catch (\Exception $e) {
            Log::error('MQTT Publish Error: ' . $e->getMessage());
            throw new \Exception('Gagal mengirim pesan ke MQTT broker');
        }
    }

    /**
     * Get available TTS voices
     */
    public function getTTSVoices()
    {
        return [
            'id-id' => 'Indonesian',
            'en-us' => 'English (US)',
            'en-gb' => 'English (UK)',
            'ja-jp' => 'Japanese',
            'es-es' => 'Spanish',
            'fr-fr' => 'French',
            'de-de' => 'German'
        ];
    }

    /**
     * Show announcement history
     */
    public function history(Request $request)
    {
        $search = $request->input('search');
        $mode = $request->input('mode');
        
        $announcements = Announcement::with(['user', 'ruangans'])
            ->when($search, function($query) use ($search) {
                return $query->where('message', 'like', "%{$search}%")
                            ->orWhereHas('ruangans', function($q) use ($search) {
                                $q->where('nama_ruangan', 'like', "%{$search}%");
                            })
                            ->orWhereHas('user', function($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
            })
            ->when($mode, function($query) use ($mode) {
                return $query->where('mode', $mode);
            })
            ->latest()
            ->paginate(10);
        
        return view('announcement.history', [
            'announcements' => $announcements,
            'search' => $search,
            'mode' => $mode,
            'modes' => [self::MODE_REGULER, self::MODE_TTS]
        ]);
    }

    /**
     * Show announcement detail
     */
    public function show(Announcement $announcement)
    {
        return view('announcement.show', [
            'announcement' => $announcement->load(['user', 'ruangans'])
        ]);
    }

    /**
     * Delete an announcement
     */
    public function destroy(Announcement $announcement)
    {
        try {
            // Hapus file audio jika ada
            if ($announcement->audio_path && Storage::disk('public')->exists($announcement->audio_path)) {
                Storage::disk('public')->delete($announcement->audio_path);
            }
            
            $announcement->delete();
            
            return redirect()->route('announcement.history')
                ->with('success', 'Pengumuman berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting announcement: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus pengumuman: ' . $e->getMessage());
        }
    }

    /**
     * TTS Preview endpoint
     */
    public function ttsPreview(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:1000',
            'voice' => 'nullable|string',
            'speed' => 'nullable|integer|min:-10|max:10'
        ]);

        $audioContent = $this->generateTTS(
            $validated['text'],
            $validated['voice'] ?? self::TTS_DEFAULT_VOICE,
            $validated['speed'] ?? self::TTS_DEFAULT_SPEED
        );

        if (!$audioContent) {
            return response()->json(['message' => 'Gagal menghasilkan audio'], 500);
        }

        $fileName = 'tts/previews/' . uniqid() . '.wav';
        Storage::disk('public')->put($fileName, $audioContent);

        return response()->json([
            'audio_url' => asset('storage/' . $fileName)
        ]);
    }
}