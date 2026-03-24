<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TeamMemberController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all team members.
     * 
     * Returns all team members ordered by order field.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $teamMembers = TeamMember::orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'team_members' => $teamMembers->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name,
                        'role' => $member->role,
                        'email' => $member->email,
                        'picture' => $member->picture_url,
                        'order' => $member->order,
                        'created_at' => $member->created_at,
                        'updated_at' => $member->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific team member by ID.
     * 
     * Returns a single team member configuration.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $member = TeamMember::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Team member not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'team_member' => [
                    'id' => $member->id,
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'role' => $member->role,
                    'email' => $member->email,
                    'picture' => $member->picture_url,
                    'order' => $member->order,
                    'created_at' => $member->created_at,
                    'updated_at' => $member->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new team member.
     * 
     * Creates a new team member.
     * Only super admin and editor can access this.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Check if user has admin access (super_admin or editor)
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can create team members.',
                ], 403);
            }

            // Prepare validation rules
            $rules = [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'role' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'order' => ['nullable', 'integer', 'min:0'],
            ];

            // Validate picture only if file is actually uploaded
            if ($request->hasFile('picture')) {
                $rules['picture'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
            }

            $validated = $request->validate($rules);

            // Handle picture upload
            if ($request->hasFile('picture')) {
                try {
                    // Verify file was uploaded successfully
                    if (!$request->file('picture')->isValid()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Picture upload failed. File may be too large or corrupted.',
                            'errors' => [
                                'picture' => ['The picture file could not be uploaded. Please check file size (max 15MB) and try again.']
                            ],
                        ], 422);
                    }

                    $picturePath = $request->file('picture')->store('team-members', 'public');
                    $validated['picture'] = $picturePath;
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Picture upload failed.',
                        'errors' => [
                            'picture' => [config('app.debug') ? $e->getMessage() : 'The picture failed to upload. Please check file size (max 15MB) and try again.']
                        ],
                    ], 422);
                }
            }

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $maxOrder = TeamMember::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Create team member
            $member = TeamMember::create($validated);

            // Broadcast content update
            $this->broadcastContentUpdate('team-member', 'created', [
                'id' => $member->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member created successfully',
                'data' => [
                    'team_member' => [
                        'id' => $member->id,
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name,
                        'role' => $member->role,
                        'email' => $member->email,
                        'picture' => $member->picture_url,
                        'order' => $member->order,
                        'created_at' => $member->created_at,
                        'updated_at' => $member->updated_at,
                    ],
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Team member creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during team member creation.',
            ], 500);
        }
    }

    /**
     * Update team member content.
     * 
     * Updates the existing team member configuration.
     * Only super admin and editor can access this.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Check if user has admin access (super_admin or editor)
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can update team members.',
                ], 403);
            }

            $member = TeamMember::find($id);

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team member not found.',
                ], 404);
            }

            // Prepare validation rules
            $rules = [
                'first_name' => ['sometimes', 'required', 'string', 'max:255'],
                'last_name' => ['sometimes', 'required', 'string', 'max:255'],
                'role' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => ['sometimes', 'required', 'email', 'max:255'],
                'order' => ['nullable', 'integer', 'min:0'],
            ];

            // Validate picture only if file is actually uploaded
            if ($request->hasFile('picture')) {
                $rules['picture'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
            }

            $validated = $request->validate($rules);

            // Handle picture upload
            if ($request->hasFile('picture')) {
                try {
                    // Verify file was uploaded successfully
                    if (!$request->file('picture')->isValid()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Picture upload failed. File may be too large or corrupted.',
                            'errors' => [
                                'picture' => ['The picture file could not be uploaded. Please check file size (max 15MB) and try again.']
                            ],
                        ], 422);
                    }

                    // Delete old picture if exists
                    if ($member->picture) {
                        Storage::disk('public')->delete($member->picture);
                    }

                    $picturePath = $request->file('picture')->store('team-members', 'public');
                    $validated['picture'] = $picturePath;
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Picture upload failed.',
                        'errors' => [
                            'picture' => [config('app.debug') ? $e->getMessage() : 'The picture failed to upload. Please check file size (max 15MB) and try again.']
                        ],
                    ], 422);
                }
            }

            // Handle picture deletion
            if ($request->has('picture') && ($request->input('picture') === null || $request->input('picture') === 'delete' || $request->input('picture') === '')) {
                if ($member->picture) {
                    Storage::disk('public')->delete($member->picture);
                }
                $validated['picture'] = null;
            }

            // Update team member
            $member->update($validated);

            // Broadcast content update
            $this->broadcastContentUpdate('team-member', 'updated', [
                'id' => $member->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member updated successfully',
                'data' => [
                    'team_member' => [
                        'id' => $member->id,
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name,
                        'role' => $member->role,
                        'email' => $member->email,
                        'picture' => $member->picture_url,
                        'order' => $member->order,
                        'created_at' => $member->created_at,
                        'updated_at' => $member->updated_at,
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Team member update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during team member update.',
            ], 500);
        }
    }

    /**
     * Delete a team member.
     * 
     * Deletes a team member and its associated picture.
     * Only super admin and editor can access this.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Check if user has admin access (super_admin or editor)
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete team members.',
                ], 403);
            }

            $member = TeamMember::find($id);

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team member not found.',
                ], 404);
            }

            // Delete picture if exists
            if ($member->picture) {
                Storage::disk('public')->delete($member->picture);
            }

            // Delete team member
            $member->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('team-member', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Team member deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during team member deletion.',
            ], 500);
        }
    }

    /**
     * Delete specific field from team member.
     * 
     * Deletes a single field (e.g., picture) from team member without deleting the entire member.
     * Only super admin and editor can access this.
     * 
     * @param Request $request
     * @param int $id
     * @param string $field
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteField(Request $request, $id, $field)
    {
        try {
            // Check if user has admin access (super_admin or editor)
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete team member fields.',
                ], 403);
            }

            $member = TeamMember::find($id);

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team member not found.',
                ], 404);
            }

            $allowedFields = ['picture'];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field. Allowed fields: ' . implode(', ', $allowedFields),
                ], 400);
            }

            // Delete file if exists
            if ($member->$field) {
                Storage::disk('public')->delete($member->$field);
            }

            // Update field to null
            $member->update([$field => null]);

            // Broadcast content update
            $this->broadcastContentUpdate('team-member', 'updated', [
                'id' => $member->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Team member {$field} deleted successfully",
                'data' => [
                    'team_member' => [
                        'id' => $member->id,
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name,
                        'role' => $member->role,
                        'email' => $member->email,
                        'picture' => $member->picture_url,
                        'order' => $member->order,
                        'created_at' => $member->created_at,
                        'updated_at' => $member->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Field deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during field deletion.',
            ], 500);
        }
    }
}
