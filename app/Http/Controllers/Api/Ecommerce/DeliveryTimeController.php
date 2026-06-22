<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTime;

class DeliveryTimeController extends Controller
{
    public function index()
    {
        try {
            $deliveryTimes = DeliveryTime::where('status', true)
                ->orderBy('priority')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $deliveryTimes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch delivery times.',
            ], 500);
        }
    }
}
