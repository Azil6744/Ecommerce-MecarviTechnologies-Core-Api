<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ServiceSectionController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get service section content.
     * 
     * Returns the current service section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $serviceSection = ServiceSection::first();

        if (!$serviceSection) {
            return response()->json([
                'success' => true,
                'data' => [
                    'service_section' => null,
                    'message' => 'Service section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_section' => [
                    'id' => $serviceSection->id,
                    'subtitle' => $serviceSection->subtitle,
                    'main_title' => $serviceSection->main_title,
                    'button_text' => $serviceSection->button_text,
                    'process_subtitle' => $serviceSection->process_subtitle,
                    'process_title_1' => $serviceSection->process_title_1,
                    'process_title_2' => $serviceSection->process_title_2,
                    'process_description' => $serviceSection->process_description,
                    'process_checklist' => $serviceSection->process_checklist,
                    'background_image' => $serviceSection->background_image_url,
                    'background_color' => $serviceSection->background_color,
                    'card_background_color' => $serviceSection->card_background_color,
                    'process_background_color' => $serviceSection->process_background_color,
                    'process_card_background_color' => $serviceSection->process_card_background_color,
                    'process_background_image' => $serviceSection->process_background_image_url,
                    'created_at' => $serviceSection->created_at,
                    'updated_at' => $serviceSection->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update service section content.
     * 
     * Creates a new service section if none exists, or updates the existing one.
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
                    'message' => 'Unauthorized. Only admins can manage service section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'subtitle' => ['nullable', 'string', 'max:255'],
                'main_title' => ['nullable', 'string', 'max:255'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'process_subtitle' => ['nullable', 'string', 'max:255'],
                'process_title_1' => ['nullable', 'string', 'max:255'],
                'process_title_2' => ['nullable', 'string', 'max:255'],
                'process_description' => ['nullable', 'string'],
                'process_checklist' => ['nullable', 'array'],
                'background_color' => ['nullable', 'string', 'max:32'],
                'card_background_color' => ['nullable', 'string', 'max:50'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'process_background_color' => ['nullable', 'string', 'max:50'],
                'process_card_background_color' => ['nullable', 'string', 'max:50'],
                'process_background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            ]);

            $existingServiceSection = ServiceSection::first();

            // Handle background color (optional)
            if ($request->has('background_color')) {
                $validated['background_color'] = $request->input('background_color') ?: null;
            } else {
                $validated['background_color'] = $existingServiceSection->background_color ?? null;
            }

            // Handle card background color (optional)
            if ($request->has('card_background_color')) {
                $validated['card_background_color'] = $request->input('card_background_color') ?: null;
            } else {
                $validated['card_background_color'] = $existingServiceSection->card_background_color ?? null;
            }

            // Handle process background color (optional)
            if ($request->has('process_background_color')) {
                $validated['process_background_color'] = $request->input('process_background_color') ?: null;
            } else {
                $validated['process_background_color'] = $existingServiceSection->process_background_color ?? null;
            }

            // Handle process card background color (optional)
            if ($request->has('process_card_background_color')) {
                $validated['process_card_background_color'] = $request->input('process_card_background_color') ?: null;
            } else {
                $validated['process_card_background_color'] = $existingServiceSection->process_card_background_color ?? null;
            }

            // Handle background image upload (only if provided)
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($existingServiceSection && $existingServiceSection->background_image) {
                    Storage::disk('public')->delete($existingServiceSection->background_image);
                }

                // Store new image
                $imagePath = $request->file('background_image')->store('service-section', 'public');
                $validated['background_image'] = $imagePath;
            } else {
                // Keep existing image if not updating
                $validated['background_image'] = $existingServiceSection->background_image ?? null;
            }

            // Handle process background image upload (only if provided)
            if ($request->hasFile('process_background_image')) {
                // Delete old image if exists
                if ($existingServiceSection && $existingServiceSection->process_background_image) {
                    Storage::disk('public')->delete($existingServiceSection->process_background_image);
                }

                // Store new image
                $imagePath = $request->file('process_background_image')->store('service-section', 'public');
                $validated['process_background_image'] = $imagePath;
            } else {
                // Keep existing image if not updating
                $validated['process_background_image'] = $existingServiceSection->process_background_image ?? null;
            }

            // Update or create service section
            $serviceSection = ServiceSection::updateOrCreate(
                ['id' => ServiceSection::first()?->id ?? 0],
                $validated
            );

            $this->broadcastContentUpdate('service-section', 'updated', [
                'id' => $serviceSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service section updated successfully',
                'data' => [
                    'service_section' => [
                        'id' => $serviceSection->id,
                        'subtitle' => $serviceSection->subtitle,
                        'main_title' => $serviceSection->main_title,
                        'button_text' => $serviceSection->button_text,
                        'process_subtitle' => $serviceSection->process_subtitle,
                        'process_title_1' => $serviceSection->process_title_1,
                        'process_title_2' => $serviceSection->process_title_2,
                        'process_description' => $serviceSection->process_description,
                        'process_checklist' => $serviceSection->process_checklist,
                        'background_image' => $serviceSection->background_image_url,
                        'background_color' => $serviceSection->background_color,
                        'card_background_color' => $serviceSection->card_background_color,
                        'process_background_color' => $serviceSection->process_background_color,
                        'process_card_background_color' => $serviceSection->process_card_background_color,
                        'process_background_image' => $serviceSection->process_background_image_url,
                        'updated_at' => $serviceSection->updated_at,
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
                'message' => 'Service section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during service section update.',
            ], 500);
        }
    }

    /**
     * Update service section content.
     * 
     * Updates the existing service section configuration.
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
                    'message' => 'Unauthorized. Only admins can manage service section content.',
                ], 403);
            }

            $serviceSection = ServiceSection::find($id);

            if (!$serviceSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service section not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // For PUT/PATCH with form-data, Laravel may not parse it automatically
            // Check if this is a multipart/form-data request (file upload)
            $isMultipart = $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data');
            
            if ($isMultipart) {
                // For form-data, ensure request data is properly parsed
                $allInput = $request->all();
                if (!empty($allInput)) {
                    $request->merge($allInput);
                }
            }

            // Text fields that can be updated
            $textFields = [
                'subtitle', 'main_title', 'button_text', 'background_color', 'card_background_color',
                'process_subtitle', 'process_title_1', 'process_title_2', 'process_description',
                'process_background_color', 'process_card_background_color'
            ];
            
            foreach ($textFields as $field) {
                if ($request->filled($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                } elseif ($request->has($field) || array_key_exists($field, $request->all())) {
                    // Allow null to delete field (check both has() and all() for form-data compatibility)
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            if ($request->has('process_checklist')) {
                $dataToUpdate['process_checklist'] = $request->input('process_checklist');
            }

            // Handle background image upload (only if provided) and deletion
            $fieldValue = $request->input('background_image');
            $fieldExists = $request->has('background_image') || array_key_exists('background_image', $request->all());
            
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($serviceSection->background_image) {
                    Storage::disk('public')->delete($serviceSection->background_image);
                }

                // Store new image
                $imagePath = $request->file('background_image')->store('service-section', 'public');
                $dataToUpdate['background_image'] = $imagePath;
            } elseif ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                // Delete old image if exists
                if ($serviceSection->background_image) {
                    Storage::disk('public')->delete($serviceSection->background_image);
                }
                $dataToUpdate['background_image'] = null;
            }

            // Handle process background image upload (only if provided) and deletion
            $processFieldValue = $request->input('process_background_image');
            $processFieldExists = $request->has('process_background_image') || array_key_exists('process_background_image', $request->all());
            
            if ($request->hasFile('process_background_image')) {
                if ($serviceSection->process_background_image) {
                    Storage::disk('public')->delete($serviceSection->process_background_image);
                }
                $imagePath = $request->file('process_background_image')->store('service-section', 'public');
                $dataToUpdate['process_background_image'] = $imagePath;
            } elseif ($processFieldExists && ($processFieldValue === null || $processFieldValue === 'delete' || $processFieldValue === '')) {
                if ($serviceSection->process_background_image) {
                    Storage::disk('public')->delete($serviceSection->process_background_image);
                }
                $dataToUpdate['process_background_image'] = null;
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['subtitle'])) {
                    $rules['subtitle'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['main_title'])) {
                    $rules['main_title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['button_text'])) {
                    $rules['button_text'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['process_subtitle'])) {
                    $rules['process_subtitle'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['process_title_1'])) {
                    $rules['process_title_1'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['process_title_2'])) {
                    $rules['process_title_2'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['process_description'])) {
                    $rules['process_description'] = ['nullable', 'string'];
                }
                if (isset($dataToUpdate['process_checklist'])) {
                    $rules['process_checklist'] = ['nullable', 'array'];
                }
                if (isset($dataToUpdate['background_color'])) {
                    $rules['background_color'] = ['nullable', 'string', 'max:32'];
                }
                if (isset($dataToUpdate['card_background_color'])) {
                    $rules['card_background_color'] = ['nullable', 'string', 'max:50'];
                }
                if (isset($dataToUpdate['background_image']) && $request->hasFile('background_image')) {
                    $rules['background_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }
                if (isset($dataToUpdate['process_background_color'])) {
                    $rules['process_background_color'] = ['nullable', 'string', 'max:50'];
                }
                if (isset($dataToUpdate['process_card_background_color'])) {
                    $rules['process_card_background_color'] = ['nullable', 'string', 'max:50'];
                }
                if (isset($dataToUpdate['process_background_image']) && $request->hasFile('process_background_image')) {
                    $rules['process_background_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }

                // Validate
                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $serviceSection->fill($dataToUpdate);
                $serviceSection->save();
                $serviceSection->refresh();

                $this->broadcastContentUpdate('service-section', 'updated', [
                    'id' => $serviceSection->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Service section updated successfully',
                'data' => [
                    'service_section' => [
                        'id' => $serviceSection->id,
                        'subtitle' => $serviceSection->subtitle,
                        'main_title' => $serviceSection->main_title,
                        'button_text' => $serviceSection->button_text,
                        'process_subtitle' => $serviceSection->process_subtitle,
                        'process_title_1' => $serviceSection->process_title_1,
                        'process_title_2' => $serviceSection->process_title_2,
                        'process_description' => $serviceSection->process_description,
                        'process_checklist' => $serviceSection->process_checklist,
                        'background_image' => $serviceSection->background_image_url,
                        'background_color' => $serviceSection->background_color,
                        'card_background_color' => $serviceSection->card_background_color,
                        'process_background_color' => $serviceSection->process_background_color,
                        'process_card_background_color' => $serviceSection->process_card_background_color,
                        'process_background_image' => $serviceSection->process_background_image_url,
                        'updated_at' => $serviceSection->updated_at,
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
                'message' => 'Service section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during service section update.',
            ], 500);
        }
    }

    /**
     * Delete service section content.
     * 
     * Deletes the service section and associated images.
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
                    'message' => 'Unauthorized. Only admins can delete service section content.',
                ], 403);
            }

            $serviceSection = ServiceSection::find($id);

            if (!$serviceSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service section not found.',
                ], 404);
            }

            // Delete associated image from storage
            if ($serviceSection->background_image) {
                Storage::disk('public')->delete($serviceSection->background_image);
            }
            if ($serviceSection->process_background_image) {
                Storage::disk('public')->delete($serviceSection->process_background_image);
            }

            // Delete the service section record
            $serviceSection->delete();

            $this->broadcastContentUpdate('service-section', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during service section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from service section.
     * 
     * Deletes a single field (e.g., background_image) from the service section without deleting the entire section.
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
                    'message' => 'Unauthorized. Only admins can delete service section fields.',
                ], 403);
            }

            $serviceSection = ServiceSection::find($id);

            if (!$serviceSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service section not found.',
                ], 404);
            }

            // List of all allowed fields
            $allowedFields = [
                'subtitle',
                'main_title',
                'button_text',
                'process_subtitle',
                'process_title_1',
                'process_title_2',
                'process_description',
                'process_checklist',
                'background_image',
                'background_color',
                'card_background_color',
                'process_background_color',
                'process_card_background_color',
                'process_background_image',
            ];

            // Validate field name
            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            // Delete image file if it's an image field
            if ($field === 'background_image' && $serviceSection->background_image) {
                Storage::disk('public')->delete($serviceSection->background_image);
            }
            if ($field === 'process_background_image' && $serviceSection->process_background_image) {
                Storage::disk('public')->delete($serviceSection->process_background_image);
            }

            // Set field to null in database
            $serviceSection->$field = null;
            $serviceSection->save();
            $serviceSection->refresh();

            $this->broadcastContentUpdate('service-section', 'updated', [
                'id' => $serviceSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'service_section' => [
                        'id' => $serviceSection->id,
                        'subtitle' => $serviceSection->subtitle,
                        'main_title' => $serviceSection->main_title,
                        'button_text' => $serviceSection->button_text,
                        'process_subtitle' => $serviceSection->process_subtitle,
                        'process_title_1' => $serviceSection->process_title_1,
                        'process_title_2' => $serviceSection->process_title_2,
                        'process_description' => $serviceSection->process_description,
                        'process_checklist' => $serviceSection->process_checklist,
                        'background_image' => $serviceSection->background_image_url,
                        'background_color' => $serviceSection->background_color,
                        'card_background_color' => $serviceSection->card_background_color,
                        'process_background_color' => $serviceSection->process_background_color,
                        'process_card_background_color' => $serviceSection->process_card_background_color,
                        'process_background_image' => $serviceSection->process_background_image_url,
                        'updated_at' => $serviceSection->updated_at,
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
