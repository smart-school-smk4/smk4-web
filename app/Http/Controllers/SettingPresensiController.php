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
            // Panggil Flask API endpoint /set_mode/{mode}
            $url = "http://{$device->ip_address}:5000/set_mode/{$mode}";
            
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => "Mode device berhasil diubah ke: " . strtoupper($mode),
                    'data' => [
                        'device' => $device->nama_device,
                        'mode' => $mode
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengubah mode device. Response: ' . $response->body()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
