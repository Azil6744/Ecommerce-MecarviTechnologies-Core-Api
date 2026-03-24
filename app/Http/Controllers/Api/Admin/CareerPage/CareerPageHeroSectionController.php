<?php

namespace App\Http\Controllers\Api\Admin\CareerPage;

use App\Http\Controllers\Controller;
use App\Models\CareerPageHeroSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CareerPageHeroSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get career page hero section content.
     * 
     * Returns the current career page hero section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = CareerPageHeroSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'career_page_hero_section' => null,
                    'message' => 'Career page hero section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'career_page_hero_section' => [
                    'id' => $section->id,
                    'image' => $section->image_url,
                    'title' => $section->title,
                    'heading' => $section->heading,
                    'description' => $section->description,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update career page hero section content.
     * 
     * Creates a new career page hero section if none exists, or updates the existing one.
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
                    'message' => 'Unauthorized. Only admins can manage career page hero section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'title' => ['nullable', 'string', 'max:255'],
                'heading' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);

            $existingSection = CareerPageHeroSection::first();

            // Handle image uploads (only if provided)
            $imageFields = [
                'image',
            ];

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($existingSection && $existingSection->$field) {
                        Storage::disk('public')->delete($existingSection->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('career-page-hero-section', 'public');
                    $validated[$field] = $imagePath;
                } elseif ($request->has($field) && is_string($request->input($field))) {
                    // Handle case where image URL is sent as string (base64 or URL)
                    $imageString = $request->input($field);
                    
                    // If it's a base64 image
                    if (preg_match('/^data:image\/(\w+);base64,/', $imageString, $matches)) {
                        $imageType = $matches[1];
                        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageString));
                        
                        if ($imageData !== false) {
                            $filename = $field . '_' . time() . '.' . $imageType;
                            $imagePath = 'career-page-hero-section/' . $filename;
                            
                            Storage::disk('public')->put($imagePath, $imageData);
                            
                            // Delete old image if exists
                            if ($existingSection && $existingSection->$field) {
                                Storage::disk('public')->delete($existingSection->$field);
                            }
                            
                            $validated[$field] = $imagePath;
                        }
                    }
                } else {
                    // Keep existing image if not updating
                    $validated[$field] = $existingSection->$field ?? null;
                }
            }

            // Update or create career page hero section
            $section = CareerPageHeroSection::updateOrCreate(
                ['id' => CareerPageHeroSection::first()?->id ?? 0],
                $validated
            );

            // Broadcast content update
            $this->broadcastContentUpdate('career-page-hero-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Career page hero section updated successfully',
                'data' => [
                    'career_page_hero_section' => [
                        'id' => $section->id,
                        'image' => $section->image_url,
                        'title' => $section->title,
                        'heading' => $section->heading,
                        'description' => $section->description,
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
                'message' => 'Career page hero section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during career page hero section update.',
            ], 500);
        }
    }

    /**
     * Update career page hero section content.
     * 
     * Updates the existing career page hero section configuration.
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
                    'message' => 'Unauthorized. Only admins can manage career page hero section content.',
                ], 403);
            }

            $section = CareerPageHeroSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career page hero section not found.',
                ], 404);
            }

            // Get all input data
            $dataToUpdate = [];

            // Text fields
            $textFields = [
                'title',
                'heading',
                'description',
            ];

            foreach ($textFields as $field) {
                if ($request->filled($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                } elseif ($request->has($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // Handle image uploads and deletion
            $imageFields = [
                'image',
            ];

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($section->$field) {
                        Storage::disk('public')->delete($section->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('career-page-hero-section', 'public');
                    $dataToUpdate[$field] = $imagePath;
                } elseif ($request->has($field) && is_string($request->input($field))) {
                    // Handle case where image URL is sent as string (base64 or URL)
                    $imageString = $request->input($field);
                    
                    // If it's a base64 image
                    if (preg_match('/^data:image\/(\w+);base64,/', $imageString, $matches)) {
                        $imageType = $matches[1];
                        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageString));
                        
                        if ($imageData !== false) {
                            $filename = $field . '_' . time() . '.' . $imageType;
                            $imagePath = 'career-page-hero-section/' . $filename;
                            
                            Storage::disk('public')->put($imagePath, $imageData);
                            
                            // Delete old image if exists
                            if ($section->$field) {
                                Storage::disk('public')->delete($section->$field);
                            }
                            
                            $dataToUpdate[$field] = $imagePath;
                        }
                    }
                } else {
                    // Check if field should be deleted
                    $fieldValue = $request->input($field);
                    $fieldExists = $request->has($field) || array_key_exists($field, $request->all());
                    
                    if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                        // Delete old image if exists
                        if ($section->$field) {
                            Storage::disk('public')->delete($section->$field);
                        }
                        $dataToUpdate[$field] = null;
                    }
                }
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                // Text field validation rules
                foreach ($textFields as $field) {
                    if (isset($dataToUpdate[$field])) {
                        if ($field === 'description') {
                            $rules[$field] = ['nullable', 'string'];
                        } else {
                            $rules[$field] = ['nullable', 'string', 'max:255'];
                        }
                    }
                }
                
                // Image field validation rules (only if files are uploaded)
                foreach ($imageFields as $field) {
                    if ($request->hasFile($field)) {
                        $rules[$field] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                    }
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                // Broadcast content update
                $this->broadcastContentUpdate('career-page-hero-section', 'updated', [
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
                'message' => 'Career page hero section updated successfully',
                'data' => [
                    'career_page_hero_section' => [
                        'id' => $section->id,
                        'image' => $section->image_url,
                        'title' => $section->title,
                        'heading' => $section->heading,
                        'description' => $section->description,
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
                'message' => 'Career page hero section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during career page hero section update.',
            ], 500);
        }
    }

    /**
     * Show a specific career page hero section by ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = CareerPageHeroSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Career page hero section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'career_page_hero_section' => [
                    'id' => $section->id,
                    'image' => $section->image_url,
                    'title' => $section->title,
                    'heading' => $section->heading,
                    'description' => $section->description,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete career page hero section content.
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
                    'message' => 'Unauthorized. Only admins can delete career page hero section content.',
                ], 403);
            }

            $section = CareerPageHeroSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career page hero section not found.',
                ], 404);
            }

            // Delete associated images
            if ($section->image) {
                Storage::disk('public')->delete($section->image);
            }

            $sectionId = $section->id;
            $section->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('career-page-hero-section', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Career page hero section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Career page hero section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during career page hero section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from career page hero section.
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
                    'message' => 'Unauthorized. Only admins can delete career page hero section fields.',
                ], 403);
            }

            $section = CareerPageHeroSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career page hero section not found.',
                ], 404);
            }

            $allowedFields = [
                'image',
                'title',
                'heading',
                'description',
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            $imageFields = ['image'];

            if (in_array($field, $imageFields) && $section->$field) {
                Storage::disk('public')->delete($section->$field);
            }

            $section->$field = null;
            $section->save();
            $section->refresh();

            // Broadcast content update
            $this->broadcastContentUpdate('career-page-hero-section', 'updated', [
                'id' => $section->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                'career_page_hero_section' => [
                    'id' => $section->id,
                    'image' => $section->image_url,
                    'title' => $section->title,
                    'heading' => $section->heading,
                    'description' => $section->description,
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
