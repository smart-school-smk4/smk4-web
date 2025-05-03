<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Ruangan;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    // Mode constants
    const MODE_REGULER = 'reguler';
    const MODE_TTS = 'tts';

    // Relay constants
    const RELAY_ON = 'ON';
    const RELAY_OFF = 'OFF';

    // TTS API constants
    const TTS_API_URL = 'http://api.voicerss.org/';
    const TTS_API_KEY = '90927de8275148d79080facd20fb486c';
    const TTS_DEFAULT_VOICE = 'id-id';
    const TTS_DEFAULT_SPEED = 0;
    const TTS_DEFAULT_FORMAT = 'wav';

    protected $mqttService;
    protected $mqttConfig;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
        $this->mqttConfig = config('mqtt');
        $this->initializeMqttSubscriptions();
    }
    
    protected function initializeMqttSubscriptions()
    {
        try {
            $this->mqttService->subscribe(
                $this->mqttConfig['topics']['responses']['announcement_ack'],
                function (string $topic, string $message) {
                    $this->handleAnnouncementAck($message);
                }
            );
    
            $this->mqttService->subscribe(
                $this->mqttConfig['topics']['responses']['announcement_error'],
                function (string $topic, string $message) {
                    $this->handleAnnouncementError($message);
                }
            );

            $this->mqttService->subscribe(
                $this->mqttConfig['topics']['responses']['relay_status'],
                function (string $topic, string $message) {
                    $this->handleRelayStatusUpdate($message);
                }
            );
        } catch (\Exception $e) {
            Log::error('MQTT Subscription Error: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $ruangan = Ruangan::with(['kelas', 'jurusan'])->get();
        $announcements = Announcement::with(['ruangans'])
                            ->latest()
                            ->paginate(10);
        
        try {
            $mqttStatus = $this->mqttService->isConnected() ? 'Connected' : 'Disconnected';
        } catch (\Exception $e) {
            $mqttStatus = 'Disconnected';
            Log::error('MQTT check failed: ' . $e->getMessage());
        }
        
        return view('admin.announcement.index', [
            'ruangans' => $ruangan,
            'announcements' => $announcements,
            'modes' => [self::MODE_REGULER, self::MODE_TTS],
            'relayStates' => [self::RELAY_ON, self::RELAY_OFF],
            'mqttStatus' => $mqttStatus
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mode' => 'required|in:reguler,tts',
            'ruangans' => 'required|array',
            'ruangans.*' => 'exists:ruangan,id',
            'relay_action' => 'required_if:mode,reguler|in:ON,OFF',
            'tts_text' => 'required_if:mode,tts|string|max:1000',
            'tts_voice' => 'required_if:mode,tts',
            'tts_speed' => 'required_if:mode,tts|integer|min:-10|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $announcement = new Announcement();
            $announcement->mode = $request->mode;
            
            if ($request->mode === self::MODE_REGULER) {
                $announcement->message = $request->relay_action === self::RELAY_ON 
                    ? 'Aktivasi Relay Ruangan' 
                    : 'Deaktivasi Relay Ruangan';
                $announcement->is_active = $request->relay_action === self::RELAY_ON;
                $announcement->relay_state = $request->relay_action;
            } else {
                $audioContent = $this->generateTTS(
                    $request->tts_text,
                    $request->tts_voice,
                    $request->tts_speed
                );

                if (!$audioContent) {
                    throw new \Exception('Failed to generate TTS audio');
                }

                $fileName = 'tts/' . now()->format('YmdHis') . '.wav';
                Storage::disk('public')->put($fileName, $audioContent);
                
                $announcement->message = $request->tts_text;
                $announcement->audio_path = $fileName;
                $announcement->voice = $request->tts_voice;
                $announcement->speed = $request->tts_speed;
                $announcement->relay_state = self::RELAY_OFF; // Default untuk TTS
            }

            $announcement->sent_at = now();
            $announcement->status = 'pending';
            
            if (!$announcement->save()) {
                throw new \Exception('Failed to save announcement');
            }
            
            $existingRuangan = Ruangan::whereIn('id', $request->ruangans)->pluck('id');
            if ($existingRuangan->count() != count($request->ruangans)) {
                throw new \Exception('Some selected ruangan not found');
            }
            
            $announcement->ruangans()->sync($existingRuangan);

            $this->publishAnnouncement($announcement);

            return redirect()->route('announcement.index')
                ->with('success', 'Pengumuman berhasil dikirim');

        } catch (\Exception $e) {
            Log::error('Announcement Error: ' . $e->getMessage());
            if (isset($announcement) && $announcement->exists) {
                $announcement->delete();
            }
            return redirect()->back()
                ->with('error', 'Gagal: ' . $e->getMessage())
                ->withInput();
        }
    }

    protected function publishAnnouncement(Announcement $announcement)
    {
        $payload = [
            'mode' => $announcement->mode,
            'announcement_id' => $announcement->id,
            'ruangans' => $announcement->ruangans->pluck('nama_ruangan')->toArray(),
            'timestamp' => now()->toDateTimeString()
        ];

        if ($announcement->mode === self::MODE_REGULER) {
            $payload['relay_state'] = $announcement->relay_state;
            
            // Kirim perintah relay ke masing-masing ruangan
            foreach ($announcement->ruangans as $ruangan) {
                $topic = $ruangan->mqtt_topic ?? "ruangan/{$ruangan->id}/relay/control";
                
                $this->mqttService->publish(
                    $topic,
                    json_encode([
                        'state' => $announcement->relay_state,
                        'announcement_id' => $announcement->id
                    ]),
                    1 // QoS level
                );

                // Update status relay di database
                $ruangan->update(['relay_state' => $announcement->relay_state]);
            }
        } else {
            $payload['message'] = $announcement->message;
            $payload['audio_url'] = asset('storage/' . $announcement->audio_path);
            $payload['voice'] = $announcement->voice;
            $payload['speed'] = $announcement->speed;
        }

        // Publis ke topic announcement umum
        $this->mqttService->publish(
            $this->mqttConfig['topics']['commands']['announcement'],
            json_encode($payload),
            1
        );
    }

    protected function generateTTS($text, $voice, $speed)
    {
        $response = Http::get(self::TTS_API_URL, [
            'key' => self::TTS_API_KEY,
            'hl' => $voice,
            'src' => $text,
            'r' => $speed,
            'c' => self::TTS_DEFAULT_FORMAT,
            'f' => '8khz_8bit_mono'
        ]);

        if ($response->successful()) {
            return $response->body();
        }

        Log::error('TTS API Error: ' . $response->body());
        return null;
    }

    protected function handleAnnouncementAck(string $message)
    {
        try {
            $data = json_decode($message, true);
            
            if (isset($data['announcement_id'])) {
                Announcement::where('id', $data['announcement_id'])
                    ->update(['status' => 'delivered']);
                
                Log::info('Announcement delivered', $data);
            }
        } catch (\Exception $e) {
            Log::error('ACK Handler Error: ' . $e->getMessage());
        }
    }
    
    protected function handleAnnouncementError(string $message)
    {
        try {
            $data = json_decode($message, true);
            
            if (isset($data['announcement_id'])) {
                Announcement::where('id', $data['announcement_id'])
                    ->update([
                        'status' => 'failed',
                        'error_message' => $data['error'] ?? 'Unknown error'
                    ]);
                
                Log::error('Announcement failed', $data);
            }
        } catch (\Exception $e) {
            Log::error('Error Handler Error: ' . $e->getMessage());
        }
    }

    protected function handleRelayStatusUpdate(string $message)
    {
        try {
            $data = json_decode($message, true);
            
            if (isset($data['ruangan_id'], $data['state'])) {
                Ruangan::where('id', $data['ruangan_id'])
                    ->update(['relay_state' => $data['state']]);
                
                Log::info('Relay status updated', $data);
            }
        } catch (\Exception $e) {
            Log::error('Relay Status Handler Error: ' . $e->getMessage());
        }
    }

    public function ttsPreview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
            'voice' => 'required|string',
            'speed' => 'required|integer|min:-10|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 400);
        }

        try {
            $audioContent = $this->generateTTS(
                $request->text,
                $request->voice,
                $request->speed
            );

            if (!$audioContent) {
                throw new \Exception('Failed to generate TTS audio');
            }

            $fileName = 'tts/previews/' . uniqid() . '.wav';
            Storage::disk('public')->put($fileName, $audioContent);

            return response()->json([
                'audio_url' => asset('storage/' . $fileName)
            ]);

        } catch (\Exception $e) {
            Log::error('TTS Preview Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate preview'
            ], 500);
        }
    }
    
    public function history(Request $request)
    {
        $search = $request->input('search');
        $mode = $request->input('mode');
        $relayState = $request->input('relay_state');
        
        $announcements = Announcement::with(['ruangans'])
            ->when($search, function($query) use ($search) {
                return $query->where('message', 'like', "%{$search}%")
                            ->orWhereHas('ruangans', function($q) use ($search) {
                                $q->where('nama_ruangan', 'like', "%{$search}%");
                            });
            })
            ->when($mode, function($query) use ($mode) {
                return $query->where('mode', $mode);
            })
            ->when($relayState, function($query) use ($relayState) {
                return $query->where('relay_state', $relayState);
            })
            ->latest()
            ->paginate(10);
        
        return view('admin.announcement.history', [
            'announcements' => $announcements,
            'search' => $search,
            'mode' => $mode,
            'relay_state' => $relayState,
            'modes' => [self::MODE_REGULER, self::MODE_TTS],
            'relayStates' => [self::RELAY_ON, self::RELAY_OFF]
        ]);
    }
}