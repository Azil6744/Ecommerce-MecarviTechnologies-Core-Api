<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PopupTemplate;
use Illuminate\Http\Request;

class PopupController extends Controller
{
    public function show($eventKey)
    {
        try {
            $template = PopupTemplate::where('event_key', $eventKey)
                ->where('status', 'published')
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Popup not found or inactive.',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'popup' => $template
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching popup.',
            ], 500);
        }
    }
}
