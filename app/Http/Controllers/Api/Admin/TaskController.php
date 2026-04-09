<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Get all tasks
     */
    public function index(Request $request)
    {
        $query = collect([
            [
                'id' => 1,
                'title' => 'Setup Database',
                'description' => 'Setup PostgreSQL database',
                'taskId' => 'TASK-001',
                'priority' => 'high',
                'status' => 'completed',
                'assignedTo' => 'John Doe',
                'assignedTeam' => 'Backend',
                'createdAt' => '2026-01-10',
                'startDate' => '2026-01-10',
            ],
        ]);

        if ($request->has('search')) {
            $query = $query->filter(function($item) use ($request) {
                return stripos($item['title'], $request->search) !== false;
            });
        }

        if ($request->has('status')) {
            $query = $query->filter(function($item) use ($request) {
                return $item['status'] == $request->status;
            });
        }

        return response()->json([
            'data' => $query->values()->all(),
            'total' => $query->count(),
        ]);
    }

    /**
     * Create task
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'string|in:low,medium,high,urgent',
            'status' => 'string|in:new,todo,in_progress,review,done',
            'assignedTo' => 'nullable|string',
            'assignedTeam' => 'nullable|string',
            'startDate' => 'date',
            'dueDate' => 'nullable|date',
        ]);

        // TODO: Implement database storage

        return response()->json(['message' => 'Task created successfully'], 201);
    }

    /**
     * Get task details
     */
    public function show($id)
    {
        return response()->json(['id' => $id]);
    }

    /**
     * Update task
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement update

        return response()->json(['message' => 'Task updated successfully']);
    }

    /**
     * Delete task
     */
    public function destroy($id)
    {
        return response()->json(['message' => 'Task deleted successfully']);
    }
}