<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Get all clients
     */
    public function index(Request $request)
    {
        $clients = collect([
            [
                'id' => 1,
                'name' => 'Acme Corporation',
                'company' => 'Acme Corp',
                'handle' => '@acmecorp',
                'address' => '123 Business St',
                'phone' => '+1-555-0123',
                'email' => 'contact@acme.com',
                'website' => 'www.acme.com',
                'code' => 'ACME-001',
                'active' => true,
            ],
        ]);

        if ($request->has('search')) {
            $clients = $clients->filter(function($item) use ($request) {
                return stripos($item['name'], $request->search) !== false ||
                       stripos($item['company'], $request->search) !== false;
            });
        }

        return response()->json($clients->values()->all());
    }

    /**
     * Create client
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'handle' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email',
            'website' => 'nullable|string',
            'code' => 'required|string|unique:clients',
            'active' => 'boolean',
        ]);

        // TODO: Implement database storage

        return response()->json(['message' => 'Client created successfully'], 201);
    }

    /**
     * Get client details
     */
    public function show($id)
    {
        return response()->json(['id' => $id]);
    }

    /**
     * Update client
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement update

        return response()->json(['message' => 'Client updated successfully']);
    }

    /**
     * Delete client
     */
    public function destroy($id)
    {
        return response()->json(['message' => 'Client deleted successfully']);
    }
}