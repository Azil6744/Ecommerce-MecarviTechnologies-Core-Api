<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\GlobalAttribute;
use Illuminate\Http\Request;

class PublicAttributeController extends Controller
{
    public function index(Request $request)
    {
        $attributes = GlobalAttribute::with(['values' => function ($q) {
            $q->where('status', 'active')->orderBy('sort_order');
        }])
        ->where('status', 'active')
        ->orderBy('sort_order')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $attributes,
        ]);
    }
}
