<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePage;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HomePageController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get home page content.
     * 
     * Returns the current home page configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $homePage = HomePage::first();

        if (!$homePage) {
            return response()->json([
                'success' => true,
                'data' => [
                    'home_page' => null,
                    'message' => 'Home page not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'home_page' => [
                    'id' => $homePage->id,
                    'title' => $homePage->title,
                    'top_label' => $homePage->top_label,
                    'button_text' => $homePage->button_text,
                    'button_url' => $homePage->button_url,
                    'secondary_button_text' => $homePage->secondary_button_text,
                    'secondary_button_url' => $homePage->secondary_button_url,
                    'description' => $homePage->description,
                    'trust_badge_1' => $homePage->trust_badge_1,
                    'trust_badge_2' => $homePage->trust_badge_2,
                    'trust_badge_3' => $homePage->trust_badge_3,
                    'background_image' => $homePage->background_image_url,
                    'secondary_image' => $homePage->secondary_image_url,
                    'background_color' => $homePage->background_color,
                    'accent_color' => $homePage->accent_color,
                    'created_at' => $homePage->created_at,
                    'updated_at' => $homePage->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update home page content.
     * 
     * Creates a new home page if none exists, or updates the existing one.
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
                    'message' => 'Unauthorized. Only admins can manage home page content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'top_label' => ['nullable', 'string', 'max:255'],
                'button_text' => ['required', 'string', 'max:255'],
                'button_url' => ['required', 'string', 'max:255'],
                'secondary_button_text' => ['nullable', 'string', 'max:255'],
                'secondary_button_url' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'trust_badge_1' => ['nullable', 'string', 'max:255'],
                'trust_badge_2' => ['nullable', 'string', 'max:255'],
                'trust_badge_3' => ['nullable', 'string', 'max:255'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'secondary_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_color' => ['nullable', 'string', 'max:50'],
                'accent_color' => ['nullable', 'string', 'max:50'],
            ]);

            $existingHomePage = HomePage::first();

            // Handle background image upload (only if provided)
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($existingHomePage && $existingHomePage->background_image) {
                    Storage::disk('public')->delete($existingHomePage->background_image);
                }

                // Store new image
                $backgroundImagePath = $request->file('background_image')->store('home-page', 'public');
                $validated['background_image'] = $backgroundImagePath;
            } else {
                // Keep existing image if not updating
                $validated['background_image'] = $existingHomePage->background_image ?? null;
            }

            // Handle secondary image upload (only if provided)
            if ($request->hasFile('secondary_image')) {
                // Delete old image if exists
                if ($existingHomePage && $existingHomePage->secondary_image) {
                    Storage::disk('public')->delete($existingHomePage->secondary_image);
                }

                // Store new image
                $secondaryImagePath = $request->file('secondary_image')->store('home-page', 'public');
                $validated['secondary_image'] = $secondaryImagePath;
            } else {
                // Keep existing image if not updating
                $validated['secondary_image'] = $existingHomePage->secondary_image ?? null;
            }

            // Update or create home page
            $homePage = HomePage::updateOrCreate(
                ['id' => HomePage::first()?->id ?? 0],
                [
                    'title' => $validated['title'],
                    'top_label' => $validated['top_label'] ?? null,
                    'button_text' => $validated['button_text'],
                    'button_url' => $validated['button_url'],
                    'secondary_button_text' => $validated['secondary_button_text'] ?? null,
                    'secondary_button_url' => $validated['secondary_button_url'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'trust_badge_1' => $validated['trust_badge_1'] ?? null,
                    'trust_badge_2' => $validated['trust_badge_2'] ?? null,
                    'trust_badge_3' => $validated['trust_badge_3'] ?? null,
                    'background_image' => $validated['background_image'] ?? null,
                    'secondary_image' => $validated['secondary_image'] ?? null,
                    'background_color' => $validated['background_color'] ?? null,
                    'accent_color' => $validated['accent_color'] ?? null,
                ]
            );

            $this->broadcastContentUpdate('home-page', 'updated', [
                'id' => $homePage->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Home page updated successfully',
                'data' => [
                    'home_page' => [
                        'id' => $homePage->id,
                        'title' => $homePage->title,
                        'top_label' => $homePage->top_label,
                        'button_text' => $homePage->button_text,
                        'button_url' => $homePage->button_url,
                        'secondary_button_text' => $homePage->secondary_button_text,
                        'secondary_button_url' => $homePage->secondary_button_url,
                        'description' => $homePage->description,
                        'trust_badge_1' => $homePage->trust_badge_1,
                        'trust_badge_2' => $homePage->trust_badge_2,
                        'trust_badge_3' => $homePage->trust_badge_3,
                        'background_image' => $homePage->background_image_url,
                        'secondary_image' => $homePage->secondary_image_url,
                        'background_color' => $homePage->background_color,
                        'updated_at' => $homePage->updated_at,
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Home page update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during home page update.',
            ], 500);
        }
    }

    /**
     * Update home page content.
     * 
     * Updates the existing home page configuration.
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
                    'message' => 'Unauthorized. Only admins can manage home page content.',
                ], 403);
            }

            $homePage = HomePage::find($id);

            if (!$homePage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Home page not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // For PUT/PATCH with form-data, Laravel may not parse it automatically
            // Try to get data from request->all() first, then fallback to $_POST
            $allInput = $request->all();
            if (empty($allInput) && $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data')) {
                // Form-data wasn't parsed, use $_POST as fallback
                $allInput = !empty($_POST) ? $_POST : [];
            }
            
            // Merge request data with parsed form-data
            if (!empty($allInput)) {
                // Merge into request object temporarily for validation
                $request->merge($allInput);
            }
            
            // Check and update title
            $title = $request->input('title');
            if ($title !== null && trim($title) !== '') {
                $dataToUpdate['title'] = trim($title);
            }

            // Check and update button_text
            $buttonText = $request->input('button_text');
            if ($buttonText !== null && trim($buttonText) !== '') {
                $dataToUpdate['button_text'] = trim($buttonText);
            }

            // Check and update button_url
            $buttonUrl = $request->input('button_url');
            if ($buttonUrl !== null && trim($buttonUrl) !== '') {
                $dataToUpdate['button_url'] = trim($buttonUrl);
            }

            // Optional strings
            $optionalStrings = [
                'top_label', 'secondary_button_text', 'trust_badge_1', 'trust_badge_2', 'trust_badge_3', 'background_color', 'accent_color'
            ];
            foreach ($optionalStrings as $field) {
                if ($request->has($field)) {
                    $val = $request->input($field);
                    $dataToUpdate[$field] = $val !== null ? trim($val) : null;
                }
            }

            // Optional URLs
            if ($request->has('secondary_button_url')) {
                $val = $request->input('secondary_button_url');
                $dataToUpdate['secondary_button_url'] = $val !== null ? trim($val) : null;
            }

            // Check and update description (can be empty/null)
            if ($request->has('description')) {
                $dataToUpdate['description'] = $request->input('description');
            }

            // Handle background image upload (only if provided)
            if ($request->hasFile('background_image')) {
                // Validate the file
                $request->validate([
                    'background_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);

                // Delete old image if exists
                if ($homePage->background_image) {
                    Storage::disk('public')->delete($homePage->background_image);
                }

                // Store new image
                $backgroundImagePath = $request->file('background_image')->store('home-page', 'public');
                $dataToUpdate['background_image'] = $backgroundImagePath;
            }
            // Note: If background_image is not provided, existing image is preserved

            // Handle secondary image upload (only if provided)
            if ($request->hasFile('secondary_image')) {
                // Validate the file
                $request->validate([
                    'secondary_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);

                // Delete old image if exists
                if ($homePage->secondary_image) {
                    Storage::disk('public')->delete($homePage->secondary_image);
                }

                // Store new image
                $secondaryImagePath = $request->file('secondary_image')->store('home-page', 'public');
                $dataToUpdate['secondary_image'] = $secondaryImagePath;
            }
            // Note: If secondary_image is not provided, existing image is preserved

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['title'])) {
                    $rules['title'] = ['required', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['button_text'])) {
                    $rules['button_text'] = ['required', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['button_url'])) {
                    $rules['button_url'] = ['required', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['description'])) {
                    $rules['description'] = ['nullable', 'string'];
                }
                // Note: Image fields are already validated when uploaded, no need to revalidate

                // Validate
                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update the home page
                $homePage->fill($dataToUpdate);
                $homePage->save();
                $homePage->refresh();

                $this->broadcastContentUpdate('home-page', 'updated', [
                    'id' => $homePage->id,
                ]);
            } else {
                // Debug: Return what we received if no data to update
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                    'debug' => [
                        'all_input' => $request->all(),
                        'button_text' => $request->input('button_text'),
                        'button_text_filled' => $request->filled('button_text'),
                        'content_type' => $request->header('Content-Type'),
                    ],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Home page updated successfully',
                'data' => [
                    'home_page' => [
                        'id' => $homePage->id,
                        'title' => $homePage->title,
                        'top_label' => $homePage->top_label,
                        'button_text' => $homePage->button_text,
                        'button_url' => $homePage->button_url,
                        'secondary_button_text' => $homePage->secondary_button_text,
                        'secondary_button_url' => $homePage->secondary_button_url,
                        'description' => $homePage->description,
                        'trust_badge_1' => $homePage->trust_badge_1,
                        'trust_badge_2' => $homePage->trust_badge_2,
                        'trust_badge_3' => $homePage->trust_badge_3,
                        'background_image' => $homePage->background_image_url,
                        'secondary_image' => $homePage->secondary_image_url,
                        'background_color' => $homePage->background_color,
                        'accent_color' => $homePage->accent_color,
                        'updated_at' => $homePage->updated_at,
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Home page update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during home page update.',
            ], 500);
        }
    }

    /**
     * Show a specific home page by ID.
     * 
     * Returns the home page configuration for a specific ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $homePage = HomePage::find($id);

        if (!$homePage) {
            return response()->json([
                'success' => false,
                'message' => 'Home page not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'home_page' => [
                    'id' => $homePage->id,
                    'title' => $homePage->title,
                    'top_label' => $homePage->top_label,
                    'button_text' => $homePage->button_text,
                    'button_url' => $homePage->button_url,
                    'secondary_button_text' => $homePage->secondary_button_text,
                    'secondary_button_url' => $homePage->secondary_button_url,
                    'description' => $homePage->description,
                    'trust_badge_1' => $homePage->trust_badge_1,
                    'trust_badge_2' => $homePage->trust_badge_2,
                    'trust_badge_3' => $homePage->trust_badge_3,
                    'background_image' => $homePage->background_image_url,
                    'secondary_image' => $homePage->secondary_image_url,
                    'created_at' => $homePage->created_at,
                    'updated_at' => $homePage->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete home page content.
     * 
     * Deletes a specific home page record and its associated images.
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
                    'message' => 'Unauthorized. Only admins can delete home page content.',
                ], 403);
            }

            $homePage = HomePage::find($id);

            if (!$homePage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Home page not found.',
                ], 404);
            }

            // Delete associated images from storage
            if ($homePage->background_image) {
                Storage::disk('public')->delete($homePage->background_image);
            }

            if ($homePage->secondary_image) {
                Storage::disk('public')->delete($homePage->secondary_image);
            }

            // Delete the home page record
            $homePage->delete();

            $this->broadcastContentUpdate('home-page', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Home page deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Home page deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during home page deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from home page content.
     * 
     * Sets a specific field to null and removes associated images if applicable.
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
                    'message' => 'Unauthorized. Only admins can manage home page content.',
                ], 403);
            }

            $homePage = HomePage::find($id);

            if (!$homePage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Home page not found.',
                ], 404);
            }

            // Validate field name
            $allowedFields = [
                'title',
                'top_label',
                'button_text',
                'button_url',
                'secondary_button_text',
                'secondary_button_url',
                'description',
                'trust_badge_1',
                'trust_badge_2',
                'trust_badge_3',
                'background_image',
                'secondary_image'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name.',
                    'allowed_fields' => $allowedFields,
                ], 400);
            }

            // Handle image deletion for image fields
            if ($field === 'background_image' && $homePage->background_image) {
                Storage::disk('public')->delete($homePage->background_image);
            }

            if ($field === 'secondary_image' && $homePage->secondary_image) {
                Storage::disk('public')->delete($homePage->secondary_image);
            }

            // Set the field to null
            $homePage->$field = null;
            $homePage->save();

            $this->broadcastContentUpdate('home-page', 'updated', [
                'id' => $homePage->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' deleted successfully from home page.",
                'data' => [
                    'home_page' => [
                        'id' => $homePage->id,
                        'title' => $homePage->title,
                        'top_label' => $homePage->top_label,
                        'button_text' => $homePage->button_text,
                        'button_url' => $homePage->button_url,
                        'secondary_button_text' => $homePage->secondary_button_text,
                        'secondary_button_url' => $homePage->secondary_button_url,
                        'description' => $homePage->description,
                        'trust_badge_1' => $homePage->trust_badge_1,
                        'trust_badge_2' => $homePage->trust_badge_2,
                        'trust_badge_3' => $homePage->trust_badge_3,
                        'background_image' => $homePage->background_image_url,
                        'secondary_image' => $homePage->secondary_image_url,
                        'updated_at' => $homePage->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Field deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during field deletion.',
            ], 500);
        }
    }
}

