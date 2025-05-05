<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Ruangan;
use App\Http\Requests\StoreAnnouncementRequest;
use Illuminate\Http\Request;
use PhpMqtt\Client\Facades\MQTT;

class AnnouncementController extends Controller
{
    public function index()
    {
        $ruangans = Ruangan::orderBy('nama_ruangan')->get();
        $mqttStatus = $this->checkMqttConnection();
        return view('admin.announcement.index', compact('ruangans', 'mqttStatus'));
    }
    
    public function history()
    {
        $announcements = Announcement::with('ruangans')
            ->when(request('mode'), function($query, $mode) {
                return $query->where('mode', $mode);
            })
            ->when(request('date'), function($query, $date) {
                return $query->whereDate('sent_at', $date);
            })
            ->orderBy('sent_at', 'desc')
            ->paginate(10);
            
        return view('admin.announcement.history', compact('announcements'));
    }
    
    private function checkMqttConnection()
    {
        try {
            $mqtt = MQTT::connection();
            return $mqtt->isConnected();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $announcementData = [
            'mode' => $request->mode,
            'sent_at' => now(),
        ];
    
        // Hanya tambahkan message jika mode TTS
        if ($request->mode === 'tts') {
            $announcementData['message'] = $request->message;
        }
    
        $announcement = Announcement::create($announcementData);
        $announcement->ruangans()->sync($request->ruangans);
    
        try {
            if ($request->mode === 'tts') {
                MQTT::publish('control/relay', json_encode([
                    'mode' => 'tts',
                    'ruang' => $request->ruangans
                ]));
    
                MQTT::publish('tts/play', json_encode([
                    'ruang' => $request->ruangans,
                    'teks' => $request->message
                ]));
            } else {
                MQTT::publish('control/relay', json_encode([
                    'mode' => 'reguler',
                    'ruang' => $request->ruangans
                ]));
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim ke perangkat: ' . $e->getMessage()
            ], 500);
        }
    
        return response()->json(['success' => true]);
    }

        public function details($id)
    {
        $announcement = Announcement::with('ruangans')->findOrFail($id);
        return response()->json([
            'mode' => $announcement->mode,
            'formatted_sent_at' => $announcement->formatted_sent_at,
            'message' => $announcement->message,
            'ruangans' => $announcement->ruangans->map(function($ruangan) {
                return ['nama_ruangan' => $ruangan->nama_ruangan];
            })
        ]);
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();
        return response()->json(['success' => true]);
    }

    public function checkMqtt()
    {
        return response()->json([
            'connected' => $this->checkMqttConnection()
        ]);
    }

    public function controlRelay(Request $request)
    {
        $request->validate([
            'ruangans' => 'required|array|min:1',
            'ruangans.*' => 'exists:ruangan,id',
            'action' => 'required|in:activate,deactivate',
            'mode' => 'required|in:manual,tts'
        ]);

        $ruangans = Ruangan::whereIn('id', $request->ruangans)->get();
        $state = $request->action === 'activate' ? 'on' : 'off';

        try {
            // Kirim perintah ke ESP32 via MQTT
            MQTT::publish('control/relay', json_encode([
                'action' => $request->action,
                'ruang' => $request->ruangans,
                'mode' => $request->mode
            ]));

            // Update status relay di database
            Ruangan::whereIn('id', $request->ruangans)->update(['relay_state' => $state]);

            // Jika mengaktifkan relay, simpan sebagai pengumuman manual
            if ($request->action === 'activate' && $request->mode === 'manual') {
                $announcement = Announcement::create([
                    'mode' => 'manual',
                    'message' => 'Pengumuman via microphone manual',
                    'sent_at' => now()
                ]);
                
                $announcement->ruangans()->sync($request->ruangans);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengontrol relay: ' . $e->getMessage()
            ], 500);
        }
    }

    public function relayStatus()
    {
        $ruangans = Ruangan::select('id', 'relay_state')->get();
        return response()->json($ruangans);
    }
}