<?php

namespace App\Http\Controllers;

use App\Models\Devices;
use App\Models\Kelas;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DevicesController extends Controller
{
    public function index()
    {
        $devices = Devices::with(['kelas', 'ruangan'])->latest()->paginate(10);
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $ruangan = Ruangan::orderBy('nama_ruangan')->get();
        
        return view('admin.devices.index', compact('devices', 'kelas', 'ruangan'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_device' => 'required|string|max:255|unique:devices,nama_device',
            'ip_address' => 'nullable|ipv4',
            'id_kelas' => 'required|exists:kelas,id',
            'id_ruangan' => 'required|exists:ruangan,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Devices::create($request->all());

        return redirect()->route('admin.devices.index')->with('success', 'Device baru berhasil ditambahkan.');
    }

    public function update(Request $request, Devices $device)
    {
        $validator = Validator::make($request->all(), [
            'nama_device' => 'required|string|max:255|unique:devices,nama_device,' . $device->id,
            'ip_address' => 'nullable|ipv4',
            'id_kelas' => 'required|exists:kelas,id',
            'id_ruangan' => 'required|exists:ruangan,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $device->update($request->all());

        return redirect()->route('admin.devices.index')->with('success', 'Data device berhasil diperbarui.');
    }

    public function destroy(Devices $device)
    {
        $device->delete();
        return redirect()->route('admin.devices.index')->with('success', 'Device berhasil dihapus.');
    }
}
