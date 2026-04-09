<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DealController extends Controller
{
    /**
     * Get all deals
     */
    public function index(Request $request)
    {
        $deals = collect([
            [
                'id' => 1,
                'companyName' => 'TechStart Inc',
                'amount' => 50000,
                'stage' => 'final_review',
                'tag' => 'enterprise',
                'assignedUser' => 'John Doe',
                'date' => '2026-03-15',
            ],
        ]);

        if ($request->has('search')) {
            $deals = $deals->filter(function($item) use ($request) {
                return stripos($item['companyName'], $request->search) !== false;
            });
        }

        return response()->json($deals->values()->all());
    }

    /**
     * Create deal
     */
    public function store(Request $request)
    {
        $request->validate([
            'companyName' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'stage' => 'required|string',
            'tag' => 'nullable|string',
            'assignedUser' => 'nullable|string',
        ]);

        return response()->json(['message' => 'Deal created successfully'], 201);
    }

    /**
     * Update deal status
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'stage' => 'string',
            'status' => 'string',
        ]);

        return response()->json(['message' => 'Deal updated successfully']);
    }

    /**
     * Delete deal
     */
    public function destroy($id)
    {
        return response()->json(['message' => 'Deal deleted successfully']);
    }
}