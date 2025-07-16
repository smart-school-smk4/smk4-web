<?php

namespace App\Http\Controllers;

use App\Models\SettingPresensi;
use Illuminate\Http\Request;

class SettingPresensiController extends Controller
{
    public function index()
    {
        $settings = SettingPresensi::all();
        return view('admin.setting_presensi.index', compact('settings'));
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
}
