<?php

namespace App\Http\Controllers\Api\Admin\servicepage;

use App\Http\Controllers\Controller;
use App\Models\ServicePage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ServicePageController extends Controller
{
    /**
     * Get all service page slides.
     * 
     * Returns all service page slide configurations.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $servicePages = ServicePage::all();

        if ($servicePages->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'service_pages' => [],
                    'message' => 'Service page slides not configured yet.',
                ],
            ], 200);
        }

        $servicePagesData = $servicePages->map(function ($servicePage) {
            return [
                'id' => $servicePage->id,
                'page_heading' => $servicePage->page_heading,
                'bg_image' => $servicePage->bg_image_url,
                'small_text' => $servicePage->small_text,
                'main_heading' => $servicePage->main_heading,
                'outlined_heading' => $servicePage->outlined_heading,
                'description' => $servicePage->description,
                'background_text' => $servicePage->background_text,
                'button_text' => $servicePage->button_text,
                'button_url' => $servicePage->button_url,
                'created_at' => $servicePage->created_at,
                'updated_at' => $servicePage->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'service_pages' => $servicePagesData,
            ],
        ], 200);
    }

    /**
     * Create service page slide.
     * 
     * Creates a new service page slide.
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
                    'message' => 'Unauthorized. Only admins can manage service page content.',
                ], 403);
            }

            // Normalize empty optional fields to null so validation passes when only some fields are filled
            $optionalUrlFields = ['button_url'];
            foreach ($optionalUrlFields as $f) {
                if ($request->has($f) && is_string($request->input($f)) && trim($request->input($f)) === '') {
                    $request->merge([$f => null]);
                }
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'page_heading' => ['nullable', 'string', 'max:255'],
                'bg_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'small_text' => ['nullable', 'string', 'max:255'],
                'main_heading' => ['nullable', 'string', 'max:255'],
                'outlined_heading' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'background_text' => ['nullable', 'string', 'max:255'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'button_url' => ['nullable', 'string', 'max:255'],
            ]);

            // Handle background image upload (only if provided)
            if ($request->hasFile('bg_image')) {
                // Store new image
                $bgImagePath = $request->file('bg_image')->store('service-page', 'public');
                $validated['bg_image'] = $bgImagePath;
            }

            // Create new service page slide
            $servicePage = ServicePage::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Service page slide created successfully',
                'data' => [
                    'service_page' => [
                        'id' => $servicePage->id,
                        'page_heading' => $servicePage->page_heading,
                        'bg_image' => $servicePage->bg_image_url,
                        'small_text' => $servicePage->small_text,
                        'main_heading' => $servicePage->main_heading,
                        'outlined_heading' => $servicePage->outlined_heading,
                        'description' => $servicePage->description,
                        'background_text' => $servicePage->background_text,
                        'button_text' => $servicePage->button_text,
                        'button_url' => $servicePage->button_url,
                        'updated_at' => $servicePage->updated_at,
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
                'message' => 'Service page update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during service page update.',
            ], 500);
        }
    }

    /**
     * Update service page content.
     * 
     * Updates the existing service page configuration.
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
                    'message' => 'Unauthorized. Only admins can manage service page content.',
                ], 403);
            }

            $servicePage = ServicePage::find($id);

            if (!$servicePage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service page not found.',
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
            
            // Check and update each field
            $fields = [
                'page_heading' => 'string|max:255',
                'small_text' => 'string|max:255',
                'main_heading' => 'string|max:255',
                'outlined_heading' => 'string|max:255',
                'description' => 'string',
                'background_text' => 'string|max:255',
                'button_text' => 'string|max:255',
                'button_url' => 'string|max:255',
            ];

            foreach ($fields as $field => $rules) {
                $value = $request->input($field);
                if ($request->has($field)) {
                    // Normalize empty string to null so nullable|url passes when saving a single field
                    $dataToUpdate[$field] = (is_string($value) && trim($value) === '') ? null : $value;
                }
            }

            // Handle background image upload (only if provided)
            if ($request->hasFile('bg_image')) {
                // Validate the file
                $request->validate([
                    'bg_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);

                // Delete old image if exists
                if ($servicePage->bg_image) {
                    Storage::disk('public')->delete($servicePage->bg_image);
                }

                // Store new image
                $bgImagePath = $request->file('bg_image')->store('service-page', 'public');
                $dataToUpdate['bg_image'] = $bgImagePath;
            }
            // Note: If bg_image is not provided, existing image is preserved

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                // Merge normalized values into request so validation sees null for empty strings
                $request->merge($dataToUpdate);

                $rules = [];
                foreach ($dataToUpdate as $key => $value) {
                    if (isset($fields[$key])) {
                        $rules[$key] = ['nullable', ...explode('|', $fields[$key])];
                    }
                }

                // Validate
                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update the service page
                $servicePage->fill($dataToUpdate);
                $servicePage->save();
                $servicePage->refresh();
            } else {
                // Debug: Return what we received if no data to update
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                    'debug' => [
                        'all_input' => $request->all(),
                        'content_type' => $request->header('Content-Type'),
                    ],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Service page updated successfully',
                'data' => [
                    'service_page' => [
                        'id' => $servicePage->id,
                        'page_heading' => $servicePage->page_heading,
                        'bg_image' => $servicePage->bg_image_url,
                        'small_text' => $servicePage->small_text,
                        'main_heading' => $servicePage->main_heading,
                        'outlined_heading' => $servicePage->outlined_heading,
                        'description' => $servicePage->description,
                        'background_text' => $servicePage->background_text,
                        'button_text' => $servicePage->button_text,
                        'button_url' => $servicePage->button_url,
                        'updated_at' => $servicePage->updated_at,
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
                'message' => 'Service page update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during service page update.',
            ], 500);
        }
    }

    /**
     * Show a specific service page by ID.
     * 
     * Returns the service page configuration for a specific ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $servicePage = ServicePage::find($id);

        if (!$servicePage) {
            return response()->json([
                'success' => false,
                'message' => 'Service page not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_page' => [
                    'id' => $servicePage->id,
                    'page_heading' => $servicePage->page_heading,
                    'bg_image' => $servicePage->bg_image_url,
                    'small_text' => $servicePage->small_text,
                    'main_heading' => $servicePage->main_heading,
                    'outlined_heading' => $servicePage->outlined_heading,
                    'description' => $servicePage->description,
                    'background_text' => $servicePage->background_text,
                    'button_text' => $servicePage->button_text,
                    'button_url' => $servicePage->button_url,
                    'created_at' => $servicePage->created_at,
                    'updated_at' => $servicePage->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete service page content.
     * 
     * Deletes a specific service page record and its associated images.
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
                    'message' => 'Unauthorized. Only admins can delete service page content.',
                ], 403);
            }

            $servicePage = ServicePage::find($id);

            if (!$servicePage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service page not found.',
                ], 404);
            }

            // Delete associated images from storage
            if ($servicePage->bg_image) {
                Storage::disk('public')->delete($servicePage->bg_image);
            }

            // Delete the service page record
            $servicePage->delete();

            return response()->json([
                'success' => true,
                'message' => 'Service page deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Service page deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during service page deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from service page.
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
                    'message' => 'Unauthorized. Only admins can manage service page content.',
                ], 403);
            }

            $servicePage = ServicePage::find($id);

            if (!$servicePage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service page not found.',
                ], 404);
            }

            // Validate field name
            $allowedFields = [
                'page_heading',
                'bg_image', 
                'small_text',
                'main_heading',
                'outlined_heading',
                'description',
                'background_text',
                'button_text',
                'button_url'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name.',
                    'allowed_fields' => $allowedFields,
                ], 400);
            }

            // Handle image deletion for bg_image field
            if ($field === 'bg_image' && $servicePage->bg_image) {
                Storage::disk('public')->delete($servicePage->bg_image);
            }

            // Set the field to null
            $servicePage->$field = null;
            $servicePage->save();

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' deleted successfully from service page.",
                'data' => [
                    'service_page' => [
                        'id' => $servicePage->id,
                        'page_heading' => $servicePage->page_heading,
                        'bg_image' => $servicePage->bg_image_url,
                        'small_text' => $servicePage->small_text,
                        'main_heading' => $servicePage->main_heading,
                        'outlined_heading' => $servicePage->outlined_heading,
                        'description' => $servicePage->description,
                        'background_text' => $servicePage->background_text,
                        'button_text' => $servicePage->button_text,
                        'button_url' => $servicePage->button_url,
                        'updated_at' => $servicePage->updated_at,
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
