<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Get all projects
     */
    public function index(Request $request)
    {
        $query = collect([
            [
                'id' => 1,
                'name' => 'Website Redesign',
                'description' => 'Complete redesign of company website',
                'projectId' => 'PRJ-001',
                'completionPercentage' => 75,
                'priorityStatus' => 'high',
                'startDate' => '2026-01-15',
                'status' => 'in_progress',
                'team' => ['John Doe', 'Jane Smith'],
            ],
            [
                'id' => 2,
                'name' => 'Mobile App Development',
                'description' => 'Development of new mobile application',
                'projectId' => 'PRJ-002',
                'completionPercentage' => 50,
                'priorityStatus' => 'medium',
                'startDate' => '2026-02-01',
                'status' => 'in_progress',
                'team' => ['John Doe', 'Bob Wilson'],
            ],
        ]);

        if ($request->has('search')) {
            $query = $query->filter(function($item) use ($request) {
                return stripos($item['name'], $request->search) !== false;
            });
        }

        return response()->json([
            'data' => $query->values()->paginate($request->get('per_page', 15))->items(),
            'total' => $query->count(),
        ]);
    }

    /**
     * Create new project
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'projectId' => 'required|string|unique:projects,project_id',
            'completionPercentage' => 'integer|min:0|max:100',
            'priorityStatus' => 'string|in:low,medium,high,urgent',
            'startDate' => 'date',
            'status' => 'string|in:pending,in_progress,on_hold,completed,cancelled',
            'team' => 'nullable|array',
        ]);

        // TODO: Implement database storage

        return response()->json([
            'message' => 'Project created successfully',
        ], 201);
    }

    /**
     * Get project details
     */
    public function show($id)
    {
        // TODO: Fetch from database
        return response()->json([
            'id' => $id,
            'name' => 'Website Redesign',
        ]);
    }

    /**
     * Update project
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'status' => 'string|in:pending,in_progress,on_hold,completed,cancelled',
        ]);

        // TODO: Implement database update

        return response()->json(['message' => 'Project updated successfully']);
    }

    /**
     * Delete project
     */
    public function destroy($id)
    {
        // TODO: Implement database delete

        return response()->json(['message' => 'Project deleted successfully']);
    }
}