<?php

namespace App\Http\Controllers\Api\Admin\aboutpage;

use App\Http\Controllers\Controller;
use App\Models\AboutCompanySection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AboutCompanySectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get about company section content.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = AboutCompanySection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'about_company_section' => null,
                    'message' => 'About company section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'about_company_section' => [
                    'id' => $section->id,
                    'company_title' => $section->company_title,
                    'company_description' => $section->company_description,
                    'company_image' => $section->company_image_url,
                    'left_background_color' => $section->left_background_color,
                    'right_background_color' => $section->right_background_color,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update about company section content.
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
                    'message' => 'Unauthorized. Only admins can manage about company section content.',
                ], 403);
            }

            $validated = $request->validate([
                'company_title' => ['nullable', 'string', 'max:255'],
                'company_description' => ['nullable', 'string'],
                'company_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'left_background_color' => ['nullable', 'string', 'max:50'],
                'right_background_color' => ['nullable', 'string', 'max:50'],
            ]);

            $existingSection = AboutCompanySection::first();

            if ($request->hasFile('company_image')) {
                if ($existingSection && $existingSection->company_image) {
                    Storage::disk('public')->delete($existingSection->company_image);
                }
                $imagePath = $request->file('company_image')->store('about-company-section', 'public');
                $validated['company_image'] = $imagePath;
            } else {
                $validated['company_image'] = $existingSection->company_image ?? null;
            }

            $section = AboutCompanySection::updateOrCreate(
                ['id' => AboutCompanySection::first()?->id ?? 0],
                $validated
            );

            $this->broadcastContentUpdate('about-company-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'About company section updated successfully',
                'data' => [
                    'about_company_section' => [
                        'id' => $section->id,
                    'company_title' => $section->company_title,
                    'company_description' => $section->company_description,
                    'company_image' => $section->company_image_url,
                    'left_background_color' => $section->left_background_color,
                    'right_background_color' => $section->right_background_color,
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
                'message' => 'About company section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about company section update.',
            ], 500);
        }
    }

    /**
     * Update about company section content.
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
                    'message' => 'Unauthorized. Only admins can manage about company section content.',
                ], 403);
            }

            $section = AboutCompanySection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'About company section not found.',
                ], 404);
            }

            $dataToUpdate = [];

            if ($request->filled('company_title')) {
                $dataToUpdate['company_title'] = $request->input('company_title');
            } elseif ($request->has('company_title')) {
                $dataToUpdate['company_title'] = $request->input('company_title');
            }

            if ($request->filled('company_description')) {
                $dataToUpdate['company_description'] = $request->input('company_description');
            } elseif ($request->has('company_description')) {
                $dataToUpdate['company_description'] = $request->input('company_description');
            }

            if ($request->hasFile('company_image')) {
                if ($section->company_image) {
                    Storage::disk('public')->delete($section->company_image);
                }
                $imagePath = $request->file('company_image')->store('about-company-section', 'public');
                $dataToUpdate['company_image'] = $imagePath;
            } else {
                $fieldValue = $request->input('company_image');
                $fieldExists = $request->has('company_image') || array_key_exists('company_image', $request->all());
                
                if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                    if ($section->company_image) {
                        Storage::disk('public')->delete($section->company_image);
                    }
                    $dataToUpdate['company_image'] = null;
                }
            }

            $colorFields = ['left_background_color', 'right_background_color'];
            foreach ($colorFields as $colorField) {
                if ($request->filled($colorField)) {
                    $dataToUpdate[$colorField] = $request->input($colorField);
                } elseif ($request->has($colorField)) {
                    $dataToUpdate[$colorField] = $request->input($colorField);
                }
            }

            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['company_title'])) {
                    $rules['company_title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['company_description'])) {
                    $rules['company_description'] = ['nullable', 'string'];
                }
                if (isset($dataToUpdate['left_background_color'])) {
                    $rules['left_background_color'] = ['nullable', 'string', 'max:50'];
                }
                if (isset($dataToUpdate['right_background_color'])) {
                    $rules['right_background_color'] = ['nullable', 'string', 'max:50'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                $this->broadcastContentUpdate('about-company-section', 'updated', [
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
                'message' => 'About company section updated successfully',
                'data' => [
                    'about_company_section' => [
                        'id' => $section->id,
                    'company_title' => $section->company_title,
                    'company_description' => $section->company_description,
                    'company_image' => $section->company_image_url,
                    'left_background_color' => $section->left_background_color,
                    'right_background_color' => $section->right_background_color,
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
                'message' => 'About company section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about company section update.',
            ], 500);
        }
    }

    /**
     * Show a specific about company section by ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = AboutCompanySection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'About company section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'about_company_section' => [
                    'id' => $section->id,
                    'company_title' => $section->company_title,
                    'company_description' => $section->company_description,
                    'company_image' => $section->company_image_url,
                    'left_background_color' => $section->left_background_color,
                    'right_background_color' => $section->right_background_color,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete about company section content.
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
                    'message' => 'Unauthorized. Only admins can delete about company section content.',
                ], 403);
            }

            $section = AboutCompanySection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'About company section not found.',
                ], 404);
            }

            if ($section->company_image) {
                Storage::disk('public')->delete($section->company_image);
            }

            $sectionId = $section->id;
            $section->delete();

            $this->broadcastContentUpdate('about-company-section', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'About company section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'About company section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about company section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from about company section.
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
                    'message' => 'Unauthorized. Only admins can delete about company section fields.',
                ], 403);
            }

            $section = AboutCompanySection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'About company section not found.',
                ], 404);
            }

            $allowedFields = [
                'company_title',
                'company_description',
                'company_image',
                'left_background_color',
                'right_background_color',
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            if ($field === 'company_image' && $section->company_image) {
                Storage::disk('public')->delete($section->company_image);
            }

            $section->$field = null;
            $section->save();
            $section->refresh();

            $this->broadcastContentUpdate('about-company-section', 'updated', [
                'id' => $section->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'about_company_section' => [
                        'id' => $section->id,
                    'company_title' => $section->company_title,
                    'company_description' => $section->company_description,
                    'company_image' => $section->company_image_url,
                    'left_background_color' => $section->left_background_color,
                    'right_background_color' => $section->right_background_color,
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
