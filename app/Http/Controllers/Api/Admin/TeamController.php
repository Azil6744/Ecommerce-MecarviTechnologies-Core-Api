<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Get all teams
     */
    public function index(Request $request)
    {
        $teams = TeamMember::with('members')
            ->paginate($request->get('per_page', 15));

        return response()->json($teams);
    }

    /**
     * Create new team
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'members' => 'nullable|array',
        ]);

        $team = TeamMember::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->has('members') && is_array($request->members)) {
            $team->members()->sync($request->members);
        }

        return response()->json($team->load('members'), 201);
    }

    /**
     * Get team details
     */
    public function show(TeamMember $team)
    {
        return response()->json($team->load('members'));
    }

    /**
     * Update team
     */
    public function update(Request $request, TeamMember $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'members' => 'nullable|array',
        ]);

        $team->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->has('members')) {
            $team->members()->sync($request->members);
        }

        return response()->json($team->load('members'));
    }

    /**
     * Delete team
     */
    public function destroy(TeamMember $team)
    {
        $team->delete();
        return response()->json(['message' => 'Team deleted successfully']);
    }
}