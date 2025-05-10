<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BellHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BellController extends Controller
{
    /**
     * Menyimpan data event bel manual dari ESP32
     */
    public function storeManualEvent(Request $request)
    {
        return $this->storeEvent($request, 'manual');
    }

    /**
     * Menyimpan data event bel schedule dari ESP32
     */
    public function storeScheduleEvent(Request $request)
    {
        return $this->storeEvent($request, 'schedule');
    }

    /**
     * Fungsi privat untuk menyimpan event (digunakan oleh manual dan schedule)
     */
    private function storeEvent(Request $request, string $triggerType)
    {
        $validated = $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu' => 'required|date_format:H:i:s',
            'file_number' => 'required|string|size:4',
            'volume' => 'sometimes|integer|min:0|max:30',
            'repeat' => 'sometimes|integer|min:1|max:5',
            'trigger_type' => 'sometimes|in:manual,schedule' // jika dikirim dari client
        ]);

        // Set default values jika tidak ada
        $validated['volume'] = $validated['volume'] ?? 15;
        $validated['repeat'] = $validated['repeat'] ?? 1;

        // Override trigger_type dengan nilai dari parameter
        $validated['trigger_type'] = $triggerType;
        $validated['ring_time'] = Carbon::now();

        $exists = BellHistory::where('hari', $validated['hari'])
            ->where('waktu', $validated['waktu'])
            ->where('file_number', $validated['file_number'])
            ->where('trigger_type', $triggerType)
            ->where('ring_time', '>=', Carbon::now()->subSeconds(60))
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Event already recorded recently, skipping duplicate.'
            ], 409);
        }

        $history = BellHistory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bell event recorded successfully',
            'data' => $history
        ], 201);
    }

    /**
     * Mengambil data history untuk ESP32
     */
    public function getHistory(Request $request)
    {
        $request->validate([
            'limit' => 'sometimes|integer|min:1|max:100',
            'days' => 'sometimes|integer|min:1|max:30',
            'trigger_type' => 'sometimes|in:manual,schedule'
        ]);

        $query = BellHistory::query()
            ->orderBy('ring_time', 'desc');

        if ($request->has('trigger_type')) {
            $query->where('trigger_type', $request->trigger_type);
        }

        if ($request->has('days')) {
            $query->where('ring_time', '>=', Carbon::now()->subDays($request->days));
        }

        $limit = $request->input('limit', 50);
        $histories = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'count' => $histories->count(),
            'data' => $histories
        ]);
    }
}
