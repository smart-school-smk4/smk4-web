<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devices;

class DevicesApiController extends Controller
{
    public function index()
    {
        $devices = Devices::with(['kelas', 'ruangan'])->get();

        return response()->json([
            'success' => true,
            'data' => $devices
        ]);
    }
}