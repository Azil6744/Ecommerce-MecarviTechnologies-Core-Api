<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContentUpdateController extends Controller
{
    /**
     * Get the last content update timestamp
     * 
     * This endpoint is used for polling to check if content has been updated.
     * Frontend can poll this endpoint and refresh when timestamp changes.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLastUpdateTime()
    {
        $lastUpdate = cache()->get('content_updated_at');
        
        if (!$lastUpdate) {
            // Set initial timestamp if not exists
            $lastUpdate = now()->toIso8601String();
            cache()->forever('content_updated_at', $lastUpdate);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'last_updated_at' => $lastUpdate,
                'timestamp' => now()->timestamp,
            ],
        ], 200);
    }

    /**
     * Force update the content timestamp
     * 
     * This can be used to manually trigger a content refresh
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceUpdate()
    {
        $timestamp = now()->toIso8601String();
        cache()->forever('content_updated_at', $timestamp);

        return response()->json([
            'success' => true,
            'message' => 'Content update timestamp refreshed',
            'data' => [
                'last_updated_at' => $timestamp,
            ],
        ], 200);
    }
}

