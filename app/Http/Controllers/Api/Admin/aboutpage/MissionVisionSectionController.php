<?php

namespace App\Http\Controllers\Api\Admin\aboutpage;

use App\Http\Controllers\Controller;
use App\Models\MissionVisionSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MissionVisionSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get mission and vision section content.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = MissionVisionSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'mission_vision_section' => null,
                    'message' => 'Mission and vision section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mission_vision_section' => [
                    'id' => $section->id,
                    'mission_title' => $section->mission_title,
                    'vision_title' => $section->vision_title,
                    'mission_description' => $section->mission_description,
                    'vision_description' => $section->vision_description,
                    'mission_background_color' => $section->mission_background_color,
                    'vision_background_color' => $section->vision_background_color,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update mission and vision section content.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage mission and vision section content.',
                ], 403);
            }

            $validated = $request->validate([
                'mission_title' => ['nullable', 'string', 'max:255'],
                'vision_title' => ['nullable', 'string', 'max:255'],
                'mission_description' => ['nullable', 'string'],
                'vision_description' => ['nullable', 'string'],
                'mission_background_color' => ['nullable', 'string', 'max:50'],
                'vision_background_color' => ['nullable', 'string', 'max:50'],
            ]);

            $section = MissionVisionSection::updateOrCreate(
                ['id' => MissionVisionSection::first()?->id ?? 0],
                $validated
            );

            $this->broadcastContentUpdate('mission-vision-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mission and vision section updated successfully',
                'data' => [
                    'mission_vision_section' => [
                        'id' => $section->id,
                    'mission_title' => $section->mission_title,
                    'vision_title' => $section->vision_title,
                    'mission_description' => $section->mission_description,
                    'vision_description' => $section->vision_description,
                    'mission_background_color' => $section->mission_background_color,
                    'vision_background_color' => $section->vision_background_color,
                    'updated_at' => $section->updated_at,
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
                'message' => 'Mission and vision section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during mission and vision section update.',
            ], 500);
        }
    }

    /**
     * Update mission and vision section content.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage mission and vision section content.',
                ], 403);
            }

            $section = MissionVisionSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mission and vision section not found.',
                ], 404);
            }

            $dataToUpdate = [];

            $fields = [
                'mission_title',
                'vision_title',
                'mission_description',
                'vision_description',
                'mission_background_color',
                'vision_background_color',
            ];

            foreach ($fields as $field) {
                if ($request->filled($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                } elseif ($request->has($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            if (!empty($dataToUpdate)) {
                $rules = [];
                
                foreach ($fields as $field) {
                    if (isset($dataToUpdate[$field])) {
                        if (in_array($field, ['mission_title', 'vision_title'])) {
                            $rules[$field] = ['nullable', 'string', 'max:255'];
                        } elseif (in_array($field, ['mission_background_color', 'vision_background_color'])) {
                            $rules[$field] = ['nullable', 'string', 'max:50'];
                        } else {
                            $rules[$field] = ['nullable', 'string'];
                        }
                    }
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                $this->broadcastContentUpdate('mission-vision-section', 'updated', [
                    'id' => $section->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mission and vision section updated successfully',
                'data' => [
                    'mission_vision_section' => [
                        'id' => $section->id,
                    'mission_title' => $section->mission_title,
                    'vision_title' => $section->vision_title,
                    'mission_description' => $section->mission_description,
                    'vision_description' => $section->vision_description,
                    'mission_background_color' => $section->mission_background_color,
                    'vision_background_color' => $section->vision_background_color,
                    'updated_at' => $section->updated_at,
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
                'message' => 'Mission and vision section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during mission and vision section update.',
            ], 500);
        }
    }

    /**
     * Show a specific mission and vision section by ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = MissionVisionSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Mission and vision section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mission_vision_section' => [
                    'id' => $section->id,
                    'mission_title' => $section->mission_title,
                    'vision_title' => $section->vision_title,
                    'mission_description' => $section->mission_description,
                    'vision_description' => $section->vision_description,
                    'mission_background_color' => $section->mission_background_color,
                    'vision_background_color' => $section->vision_background_color,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete mission and vision section content.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete mission and vision section content.',
                ], 403);
            }

            $section = MissionVisionSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mission and vision section not found.',
                ], 404);
            }

            $sectionId = $section->id;
            $section->delete();

            $this->broadcastContentUpdate('mission-vision-section', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mission and vision section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mission and vision section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during mission and vision section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from mission and vision section.
     * 
     * @param Request $request
     * @param int $id
     * @param string $field
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteField(Request $request, $id, $field)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete mission and vision section fields.',
                ], 403);
            }

            $section = MissionVisionSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mission and vision section not found.',
                ], 404);
            }

            $allowedFields = [
                'mission_title',
                'vision_title',
                'mission_description',
                'vision_description',
                'mission_background_color',
                'vision_background_color',
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            $section->$field = null;
            $section->save();
            $section->refresh();

            $this->broadcastContentUpdate('mission-vision-section', 'updated', [
                'id' => $section->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'mission_vision_section' => [
                        'id' => $section->id,
                    'mission_title' => $section->mission_title,
                    'vision_title' => $section->vision_title,
                    'mission_description' => $section->mission_description,
                    'vision_description' => $section->vision_description,
                    'mission_background_color' => $section->mission_background_color,
                    'vision_background_color' => $section->vision_background_color,
                    'updated_at' => $section->updated_at,
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
