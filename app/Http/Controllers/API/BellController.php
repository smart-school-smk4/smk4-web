<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BellHistory;
use Illuminate\Http\Request;

class BellController extends Controller
{
    /**
     * Menyimpan data event bel dari ESP32
     */
    public function storeScheduleEvent(Request $request)
    {
        $validated = $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu' => 'required|date_format:H:i:s',
            'file_number' => 'required|string|size:4',
            'volume' => 'sometimes|integer|min:0|max:30',
            'repeat' => 'sometimes|integer|min:1|max:5'
        ]);

        // Tambahkan trigger_type secara otomatis
        $validated['trigger_type'] = 'schedule';
        $validated['ring_time'] = now();

        $history = BellHistory::create($validated);

        return response()->json([
            'success' => true,
            'data' => $history
        ], 201);
    }

    /**
     * Mengambil data history untuk ESP32 (jika diperlukan)
     */
    public function getHistory(Request $request)
    {
        $histories = BellHistory::orderBy('ring_time', 'desc')
                     ->limit(50)
                     ->get();

        return response()->json([
            'success' => true,
            'data' => $histories
        ]);
    }
}