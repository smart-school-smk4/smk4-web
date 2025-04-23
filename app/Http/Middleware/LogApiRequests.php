<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogApiRequests
{
    public function handle($request, Closure $next)
    {
        // Log info sebelum proses request
        Log::info('API Request Received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'payload' => $request->all(),
        ]);

        $response = $next($request);

        // Log info setelah proses request selesai
        if ($response->getStatusCode() >= 400) {
            Log::error('API Request Failed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
                'response' => $response->getContent(),
            ]);
        } else {
            Log::info('API Request Completed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }
}