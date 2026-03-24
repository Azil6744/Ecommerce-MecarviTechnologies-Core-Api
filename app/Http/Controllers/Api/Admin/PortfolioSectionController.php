<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PortfolioSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get portfolio section content.
     * 
     * Returns the current portfolio section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = PortfolioSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'portfolio_section' => null,
                    'message' => 'Portfolio section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'portfolio_section' => [
                    'id' => $section->id,
                    'main_heading' => $section->main_heading,
                    'description' => $section->description,
                    'background_image' => $section->background_image_url,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update portfolio section content.
     * 
     * Creates a new section if none exists, or updates the existing one.
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
                    'message' => 'Unauthorized. Only admins can manage portfolio section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'main_heading' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            ]);

            $existingSection = PortfolioSection::first();

            // Handle background image upload (only if provided)
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($existingSection && $existingSection->background_image) {
                    Storage::disk('public')->delete($existingSection->background_image);
                }

                // Store new image
                $imagePath = $request->file('background_image')->store('portfolio-section', 'public');
                $validated['background_image'] = $imagePath;
            } else {
                // Keep existing image if not updating
                $validated['background_image'] = $existingSection->background_image ?? null;
            }

            // Update or create section
            $section = PortfolioSection::updateOrCreate(
                ['id' => PortfolioSection::first()?->id ?? 0],
                $validated
            );

            // Broadcast content update
            $this->broadcastContentUpdate('portfolio-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Portfolio section updated successfully',
                'data' => [
                    'portfolio_section' => [
                        'id' => $section->id,
                        'main_heading' => $section->main_heading,
                        'description' => $section->description,
                        'background_image' => $section->background_image_url,
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
                'message' => 'Portfolio section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during portfolio section update.',
            ], 500);
        }
    }

    /**
     * Update portfolio section content.
     * 
     * Updates the existing section configuration.
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
                    'message' => 'Unauthorized. Only admins can manage portfolio section content.',
                ], 403);
            }

            $section = PortfolioSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Portfolio section not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // For PUT/PATCH with form-data
            $isMultipart = $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data');
            
            if ($isMultipart) {
                $allInput = $request->all();
                if (!empty($allInput)) {
                    $request->merge($allInput);
                }
            }

            // Handle text fields
            if ($request->filled('main_heading')) {
                $dataToUpdate['main_heading'] = $request->input('main_heading');
            } elseif ($request->has('main_heading') || array_key_exists('main_heading', $request->all())) {
                $dataToUpdate['main_heading'] = $request->input('main_heading');
            }

            if ($request->filled('description')) {
                $dataToUpdate['description'] = $request->input('description');
            } elseif ($request->has('description') || array_key_exists('description', $request->all())) {
                $dataToUpdate['description'] = $request->input('description');
            }

            // Handle background image upload and deletion
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($section->background_image) {
                    Storage::disk('public')->delete($section->background_image);
                }

                // Store new image
                $imagePath = $request->file('background_image')->store('portfolio-section', 'public');
                $dataToUpdate['background_image'] = $imagePath;
            } else {
                // Check if field should be deleted
                $fieldValue = $request->input('background_image');
                $fieldExists = $request->has('background_image') || array_key_exists('background_image', $request->all());
                
                if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                    // Delete old image if exists
                    if ($section->background_image) {
                        Storage::disk('public')->delete($section->background_image);
                    }
                    $dataToUpdate['background_image'] = null;
                }
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['main_heading'])) {
                    $rules['main_heading'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['description'])) {
                    $rules['description'] = ['nullable', 'string'];
                }
                if (isset($dataToUpdate['background_image']) && $request->hasFile('background_image')) {
                    $rules['background_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                // Broadcast content update
                $this->broadcastContentUpdate('portfolio-section', 'updated', [
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
                'message' => 'Portfolio section updated successfully',
                'data' => [
                    'portfolio_section' => [
                        'id' => $section->id,
                        'main_heading' => $section->main_heading,
                        'description' => $section->description,
                        'background_image' => $section->background_image_url,
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
                'message' => 'Portfolio section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during portfolio section update.',
            ], 500);
        }
    }

    /**
     * Delete portfolio section content.
     * 
     * Deletes the section and associated images.
     * Only super admin and editor can access this.
     * 
     * @param Request $request
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
                    'message' => 'Unauthorized. Only admins can delete portfolio section content.',
                ], 403);
            }

            $section = PortfolioSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Portfolio section not found.',
                ], 404);
            }

            // Delete associated image from storage
            if ($section->background_image) {
                Storage::disk('public')->delete($section->background_image);
            }

            // Delete the section record
            $sectionId = $section->id;
            $section->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('portfolio-section', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Portfolio section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during portfolio section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from portfolio section.
     * 
     * Deletes a single field (e.g., background_image) from the section without deleting the entire section.
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
                    'message' => 'Unauthorized. Only admins can delete portfolio section fields.',
                ], 403);
            }

            $section = PortfolioSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Portfolio section not found.',
                ], 404);
            }

            // List of all allowed fields
            $allowedFields = [
                'background_image',
            ];

            // Validate field name
            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            // Delete image file if it's an image field
            if (in_array($field, $allowedFields) && $section->$field) {
                Storage::disk('public')->delete($section->$field);
            }

            // Set field to null in database
            $section->$field = null;
            $section->save();
            $section->refresh();

            // Broadcast content update
            $this->broadcastContentUpdate('portfolio-section', 'updated', [
                'id' => $section->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'portfolio_section' => [
                        'id' => $section->id,
                        'main_heading' => $section->main_heading,
                        'description' => $section->description,
                        'background_image' => $section->background_image_url,
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
