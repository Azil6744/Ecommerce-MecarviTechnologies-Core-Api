<?php

namespace App\Http\Controllers\Api\Admin\aboutpage;

use App\Http\Controllers\Controller;
use App\Models\AboutFounderSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AboutFounderSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get about founder section content.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = AboutFounderSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'about_founder_section' => null,
                    'message' => 'About founder section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'about_founder_section' => [
                    'id' => $section->id,
                    'founder_title' => $section->founder_title,
                    'founder_description' => $section->founder_description,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update about founder section content.
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
                    'message' => 'Unauthorized. Only admins can manage about founder section content.',
                ], 403);
            }

            $validated = $request->validate([
                'founder_title' => ['nullable', 'string', 'max:255'],
                'founder_description' => ['nullable', 'string'],
            ]);

            $section = AboutFounderSection::updateOrCreate(
                ['id' => AboutFounderSection::first()?->id ?? 0],
                $validated
            );

            $this->broadcastContentUpdate('about-founder-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'About founder section updated successfully',
                'data' => [
                    'about_founder_section' => [
                        'id' => $section->id,
                        'founder_title' => $section->founder_title,
                        'founder_description' => $section->founder_description,
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
                'message' => 'About founder section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about founder section update.',
            ], 500);
        }
    }

    /**
     * Update about founder section content.
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
                    'message' => 'Unauthorized. Only admins can manage about founder section content.',
                ], 403);
            }

            $section = AboutFounderSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'About founder section not found.',
                ], 404);
            }

            $dataToUpdate = [];

            if ($request->filled('founder_title')) {
                $dataToUpdate['founder_title'] = $request->input('founder_title');
            } elseif ($request->has('founder_title')) {
                $dataToUpdate['founder_title'] = $request->input('founder_title');
            }

            if ($request->filled('founder_description')) {
                $dataToUpdate['founder_description'] = $request->input('founder_description');
            } elseif ($request->has('founder_description')) {
                $dataToUpdate['founder_description'] = $request->input('founder_description');
            }

            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['founder_title'])) {
                    $rules['founder_title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['founder_description'])) {
                    $rules['founder_description'] = ['nullable', 'string'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                $this->broadcastContentUpdate('about-founder-section', 'updated', [
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
                'message' => 'About founder section updated successfully',
                'data' => [
                    'about_founder_section' => [
                        'id' => $section->id,
                        'founder_title' => $section->founder_title,
                        'founder_description' => $section->founder_description,
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
                'message' => 'About founder section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about founder section update.',
            ], 500);
        }
    }

    /**
     * Show a specific about founder section by ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = AboutFounderSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'About founder section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'about_founder_section' => [
                    'id' => $section->id,
                    'founder_title' => $section->founder_title,
                    'founder_description' => $section->founder_description,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete about founder section content.
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
                    'message' => 'Unauthorized. Only admins can delete about founder section content.',
                ], 403);
            }

            $section = AboutFounderSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'About founder section not found.',
                ], 404);
            }

            $sectionId = $section->id;
            $section->delete();

            $this->broadcastContentUpdate('about-founder-section', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'About founder section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'About founder section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about founder section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from about founder section.
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
                    'message' => 'Unauthorized. Only admins can delete about founder section fields.',
                ], 403);
            }

            $section = AboutFounderSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'About founder section not found.',
                ], 404);
            }

            $allowedFields = [
                'founder_title',
                'founder_description',
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

            $this->broadcastContentUpdate('about-founder-section', 'updated', [
                'id' => $section->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'about_founder_section' => [
                        'id' => $section->id,
                        'founder_title' => $section->founder_title,
                        'founder_description' => $section->founder_description,
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

