<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetectionLog;
use App\Models\Devices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DetectionLogController extends Controller
{
    /**
     * Store detection log dari Flask app.py
     * 
     * Endpoint: POST /api/detection-logs
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|exists:devices,id',
            'student_id' => 'nullable|string',
            'student_name' => 'required|string',
            'nis' => 'nullable|string',
            'probability' => 'required|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $log = DetectionLog::create([
                'device_id' => $request->device_id,
                'student_id' => $request->student_id,
                'student_name' => $request->student_name,
                'nis' => $request->nis,
                'probability' => $request->probability,
                'detected_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Detection log saved successfully',
                'data' => $log
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error saving detection log: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save detection log'
            ], 500);
        }
    }

    /**
     * Get latest detection logs untuk device tertentu
     * 
     * Endpoint: GET /api/detection-logs/{device_id}
     * Query params: limit (default: 50)
     */
    public function getLatestLogs($device_id, Request $request)
    {
        try {
            // Validasi device exists
            $device = Devices::findOrFail($device_id);
            
            $limit = $request->query('limit', 50);
            
            $logs = DetectionLog::where('device_id', $device_id)
                ->orderBy('detected_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'student_id' => $log->student_id,
                        'student_name' => $log->student_name,
                        'nis' => $log->nis,
                        'probability' => (float) $log->probability,
                        'detected_at' => $log->detected_at->format('Y-m-d H:i:s'),
                        'time_ago' => $log->detected_at->diffForHumans(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $logs,
                'device' => [
                    'id' => $device->id,
                    'name' => $device->nama_device,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching detection logs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch detection logs'
            ], 500);
        }
    }

    /**
     * Clear old detection logs (untuk maintenance)
     * Hapus log lebih dari 7 hari
     * 
     * Endpoint: DELETE /api/detection-logs/cleanup
     */
    public function cleanup()
    {
        try {
            $deleted = DetectionLog::where('detected_at', '<', Carbon::now()->subDays(7))->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$deleted} old detection logs"
            ]);
        } catch (\Exception $e) {
            \Log::error('Error cleaning up detection logs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup detection logs'
            ], 500);
        }
    }
}
