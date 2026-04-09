<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Get calendar events
     */
    public function index(Request $request)
    {
        $events = collect([
            [
                'id' => 1,
                'title' => 'Team Meeting',
                'category' => 'meeting',
                'date' => '2026-04-10',
                'time' => '10:00 AM',
                'description' => 'Weekly team sync',
                'attendees' => ['John Doe', 'Jane Smith'],
            ],
        ]);

        if ($request->has('category')) {
            $events = $events->filter(function($e) use ($request) {
                return $e['category'] == $request->category;
            });
        }

        return response()->json($events->values()->all());
    }

    /**
     * Create event
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:task,meeting,birthday,holiday,deadline',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'description' => 'nullable|string',
            'attendees' => 'nullable|array',
        ]);

        // TODO: Save to database

        return response()->json(['message' => 'Event created'], 201);
    }

    /**
     * Update event
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement update

        return response()->json(['message' => 'Event updated']);
    }

    /**
     * Delete event
     */
    public function destroy($id)
    {
        return response()->json(['message' => 'Event deleted']);
    }
}