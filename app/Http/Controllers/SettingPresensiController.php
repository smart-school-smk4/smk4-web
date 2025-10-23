<?php

namespace App\Http\Controllers;

use App\Models\SettingPresensi;
use App\Models\Devices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingPresensiController extends Controller
{
    public function index()
    {
        $settings = SettingPresensi::all();
        $devices = Devices::all();
        return view('admin.setting_presensi.index', compact('settings', 'devices'));
    }

    public function create()
    {
        return view('admin.setting_presensi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waktu_masuk_mulai' => 'required',
            'waktu_masuk_selesai' => 'required',
            'waktu_pulang_mulai' => 'required',
            'waktu_pulang_selesai' => 'required',
        ]);

        SettingPresensi::create($validated);

        return redirect()->route('admin.setting_presensi.index')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function edit($id)
    {
        $setting = SettingPresensi::findOrFail($id);
        return view('admin.setting_presensi.edit', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'waktu_masuk_mulai' => 'required',
            'waktu_masuk_selesai' => 'required',
            'waktu_pulang_mulai' => 'required',
            'waktu_pulang_selesai' => 'required',
        ]);

        $setting = SettingPresensi::findOrFail($id);
        $setting->update($validated);

        return redirect()->route('admin.setting_presensi.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $setting = SettingPresensi::findOrFail($id);
        $setting->delete();

        return redirect()->route('admin.setting_presensi.index')->with('success', 'Pengaturan berhasil dihapus.');
    }

    /**
     * Set mode absensi ke Flask device (masuk/keluar)
     */
    public function setDeviceMode(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'mode' => 'required|in:masuk,keluar'
        ]);

        $device = Devices::findOrFail($request->device_id);
        $mode = $request->mode;

        if (empty($device->ip_address)) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak memiliki IP address'
            ], 400);
        }

        try {
            // Deteksi environment - di production gunakan IP internal/localhost jika Flask di server yang sama
            // atau gunakan IP LAN jika Flask di device terpisah
            $ipAddress = $device->ip_address;
            
            // Jika IP adalah 127.0.0.1 dan kita di production, gunakan localhost dari server
            // Karena 127.0.0.1 di database berarti Flask ada di server yang sama dengan Laravel
            if ($ipAddress === '127.0.0.1' && config('app.env') === 'production') {
                $ipAddress = 'localhost';
            }
            
            // Panggil Flask API endpoint /set_mode/{mode}
            // Selalu gunakan HTTP karena Flask API internal (tidak perlu HTTPS untuk komunikasi server-to-server)
            $url = "http://{$ipAddress}:5000/set_mode/{$mode}";
            
            \Log::info("Attempting to connect to Flask API: {$url}");
            
            // Timeout lebih panjang untuk production (10 detik)
            $response = Http::timeout(10)
                ->withOptions([
                    'verify' => false, // Disable SSL verification untuk internal API
                    'connect_timeout' => 5
                ])
                ->get($url);

            if ($response->successful()) {
                \Log::info("Successfully changed mode to {$mode} for device {$device->nama_device}");
                
                return response()->json([
                    'success' => true,
                    'message' => "Mode device berhasil diubah ke: " . strtoupper($mode),
                    'data' => [
                        'device' => $device->nama_device,
                        'mode' => $mode,
                        'ip' => $ipAddress
                    ]
                ]);
            } else {
                \Log::error("Flask API returned error: " . $response->body());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengubah mode device. Status: ' . $response->status()
                ], 500);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error("Connection failed to Flask API: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat terhubung ke device. Pastikan Flask API aktif di ' . ($ipAddress ?? $device->ip_address),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        } catch (\Exception $e) {
            \Log::error("Error in setDeviceMode: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
