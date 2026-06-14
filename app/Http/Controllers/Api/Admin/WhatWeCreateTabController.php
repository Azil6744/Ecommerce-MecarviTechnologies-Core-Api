<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatWeCreateTab;
use App\Models\CategoryTab;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WhatWeCreateTabController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get all what we create tabs.
     * 
     * Returns all tabs ordered by order field. Can filter by category_tab_id.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = WhatWeCreateTab::with('categoryTab')->orderBy('order', 'asc');
        
        // Filter by category if provided
        if ($request->has('category_tab_id')) {
            $query->where('category_tab_id', $request->input('category_tab_id'));
        }
        
        $tabs = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'what_we_create_tabs' => $tabs->map(function ($tab) {
                    return [
                        'id' => $tab->id,
                        'category_tab_id' => $tab->category_tab_id,
                        'category_name' => $tab->categoryTab ? $tab->categoryTab->category_name : null,
                        'tag_label' => $tab->tag_label,
                        'main_heading' => $tab->main_heading,
                        'description' => $tab->description,
                        'features' => $tab->features ?? [],
                        'button_text' => $tab->button_text,
                        'image_1' => $tab->image_1_url,
                        'image_2' => $tab->image_2_url,
                        'image_3' => $tab->image_3_url,
                        'order' => $tab->order,
                        'created_at' => $tab->created_at,
                        'updated_at' => $tab->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific tab by ID.
     * 
     * Returns a single tab configuration.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $tab = WhatWeCreateTab::with('categoryTab')->find($id);

        if (!$tab) {
            return response()->json([
                'success' => false,
                'message' => 'What we create tab not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'what_we_create_tab' => [
                    'id' => $tab->id,
                    'category_tab_id' => $tab->category_tab_id,
                    'category_name' => $tab->categoryTab ? $tab->categoryTab->category_name : null,
                    'tag_label' => $tab->tag_label,
                    'main_heading' => $tab->main_heading,
                    'description' => $tab->description,
                    'features' => $tab->features ?? [],
                    'button_text' => $tab->button_text,
                    'image_1' => $tab->image_1_url,
                    'image_2' => $tab->image_2_url,
                    'image_3' => $tab->image_3_url,
                    'order' => $tab->order,
                    'created_at' => $tab->created_at,
                    'updated_at' => $tab->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new tab.
     * 
     * Creates a new what we create tab.
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
                    'message' => 'Unauthorized. Only admins can create what we create tabs.',
                ], 403);
            }

            // Pre-process features field if it comes as JSON string
            if ($request->has('features')) {
                $features = $request->input('features');
                if (is_string($features)) {
                    // Try to decode JSON string
                    $decoded = json_decode($features, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $request->merge(['features' => $decoded]);
                    } elseif (empty($features)) {
                        $request->merge(['features' => null]);
                    }
                }
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'category_tab_id' => ['required', 'exists:category_tabs,id'],
                'tag_label' => ['nullable', 'string', 'max:255'],
                'main_heading' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'features' => ['nullable'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'image_1' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'image_2' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'image_3' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'order' => ['nullable', 'integer', 'min:0'],
            ]);

            // If tag_label is not provided, automatically set it to the category name
            if (empty($validated['tag_label']) || $validated['tag_label'] === null) {
                $categoryTab = CategoryTab::find($validated['category_tab_id']);
                if ($categoryTab) {
                    $validated['tag_label'] = $categoryTab->category_name;
                }
            }

            // Handle image uploads
            $imageFields = ['image_1', 'image_2', 'image_3'];
            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    $imagePath = $request->file($field)->store('what-we-create-tabs', 'public');
                    $validated[$field] = $imagePath;
                } else {
                    $validated[$field] = null;
                }
            }

            // Handle features - ensure it's an array or null
            if (isset($validated['features'])) {
                if (is_string($validated['features'])) {
                    // If still a string, try to decode
                    $decoded = json_decode($validated['features'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $validated['features'] = array_filter($decoded); // Remove empty values
                    } else {
                        $validated['features'] = null;
                    }
                } elseif (is_array($validated['features'])) {
                    $validated['features'] = array_filter($validated['features']); // Remove empty values
                    // If array is empty after filtering, set to null
                    if (empty($validated['features'])) {
                        $validated['features'] = null;
                    }
                } else {
                    $validated['features'] = null;
                }
            } else {
                $validated['features'] = null;
            }

            // Set default order if not provided (within the category)
            if (!isset($validated['order'])) {
                $maxOrder = WhatWeCreateTab::where('category_tab_id', $validated['category_tab_id'])
                    ->max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Create tab
            $tab = WhatWeCreateTab::with('categoryTab')->create($validated);
            $tab->refresh();
            $tab->load('categoryTab');

            $this->broadcastContentUpdate('what-we-create-tab', 'updated', [
                'id' => $tab->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'What we create tab created successfully',
                'data' => [
                    'what_we_create_tab' => [
                        'id' => $tab->id,
                        'category_tab_id' => $tab->category_tab_id,
                        'category_name' => $tab->categoryTab ? $tab->categoryTab->category_name : null,
                        'tag_label' => $tab->tag_label,
                        'main_heading' => $tab->main_heading,
                        'description' => $tab->description,
                        'features' => $tab->features ?? [],
                        'button_text' => $tab->button_text,
                        'image_1' => $tab->image_1_url,
                        'image_2' => $tab->image_2_url,
                        'image_3' => $tab->image_3_url,
                        'order' => $tab->order,
                        'created_at' => $tab->created_at,
                        'updated_at' => $tab->updated_at,
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
                'message' => 'What we create tab creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during what we create tab creation.',
            ], 500);
        }
    }

    /**
     * Update tab content.
     * 
     * Updates the existing tab configuration.
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
                    'message' => 'Unauthorized. Only admins can update what we create tabs.',
                ], 403);
            }

            $tab = WhatWeCreateTab::with('categoryTab')->find($id);

            if (!$tab) {
                return response()->json([
                    'success' => false,
                    'message' => 'What we create tab not found.',
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

            // Text fields
            $textFields = ['tag_label', 'main_heading', 'description', 'button_text'];
            foreach ($textFields as $field) {
                if ($request->filled($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                } elseif ($request->has($field) || array_key_exists($field, $request->all())) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // Handle features - pre-process if it comes as JSON string
            if ($request->has('features') || array_key_exists('features', $request->all())) {
                $features = $request->input('features');
                
                // Handle null or empty string
                if ($features === null || $features === '') {
                    $dataToUpdate['features'] = null;
                } elseif (is_string($features)) {
                    // Try to decode JSON string
                    $decoded = json_decode($features, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $features = $decoded;
                    } else {
                        // If not valid JSON, treat as single string in array
                        $features = [$features];
                    }
                }
                
                // Process array features
                if (is_array($features)) {
                    $features = array_filter($features); // Remove empty values
                    $dataToUpdate['features'] = empty($features) ? null : $features;
                } else {
                    $dataToUpdate['features'] = null;
                }
            }

            // Handle category_tab_id update
            if ($request->has('category_tab_id')) {
                $request->validate([
                    'category_tab_id' => ['required', 'exists:category_tabs,id'],
                ]);
                $dataToUpdate['category_tab_id'] = $request->input('category_tab_id');
            }

            // Handle order
            if ($request->has('order')) {
                $dataToUpdate['order'] = (int) $request->input('order');
            }

            // Handle image uploads and deletion
            $imageFields = ['image_1', 'image_2', 'image_3'];
            
            // Collect all files that need validation
            $filesToValidate = [];
            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    $filesToValidate[$field] = $request->file($field);
                }
            }
            
            // Validate all files at once if any files are present
            if (!empty($filesToValidate)) {
                $validationRules = [];
                foreach (array_keys($filesToValidate) as $field) {
                    $validationRules[$field] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }
                $request->validate($validationRules);
            }

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($tab->$field) {
                        Storage::disk('public')->delete($tab->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('what-we-create-tabs', 'public');
                    $dataToUpdate[$field] = $imagePath;
                } else {
                    // Check if field should be deleted
                    $fieldValue = $request->input($field);
                    $fieldExists = $request->has($field) || array_key_exists($field, $request->all());
                    
                    if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                        // Delete old image if exists
                        if ($tab->$field) {
                            Storage::disk('public')->delete($tab->$field);
                        }
                        $dataToUpdate[$field] = null;
                    }
                }
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['tag_label'])) {
                    $rules['tag_label'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['main_heading'])) {
                    $rules['main_heading'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['description'])) {
                    $rules['description'] = ['nullable', 'string'];
                }
                if (isset($dataToUpdate['button_text'])) {
                    $rules['button_text'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['order'])) {
                    $rules['order'] = ['nullable', 'integer', 'min:0'];
                }
                // Features validation is handled in the update logic above
                // No need to validate it again here as we've already processed it

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $tab->fill($dataToUpdate);
                $tab->save();
                $tab->refresh();
                $tab->load('categoryTab');

                $this->broadcastContentUpdate('what-we-create-tab', 'updated', [
                    'id' => $tab->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'What we create tab updated successfully',
                'data' => [
                    'what_we_create_tab' => [
                        'id' => $tab->id,
                        'category_tab_id' => $tab->category_tab_id,
                        'category_name' => $tab->categoryTab ? $tab->categoryTab->category_name : null,
                        'tag_label' => $tab->tag_label,
                        'main_heading' => $tab->main_heading,
                        'description' => $tab->description,
                        'features' => $tab->features ?? [],
                        'button_text' => $tab->button_text,
                        'image_1' => $tab->image_1_url,
                        'image_2' => $tab->image_2_url,
                        'image_3' => $tab->image_3_url,
                        'order' => $tab->order,
                        'updated_at' => $tab->updated_at,
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
                'message' => 'What we create tab update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during what we create tab update.',
            ], 500);
        }
    }

    /**
     * Delete tab.
     * 
     * Deletes the tab and associated images.
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
                    'message' => 'Unauthorized. Only admins can delete what we create tabs.',
                ], 403);
            }

            $tab = WhatWeCreateTab::find($id);

            if (!$tab) {
                return response()->json([
                    'success' => false,
                    'message' => 'What we create tab not found.',
                ], 404);
            }

            // Delete associated images from storage
            $imageFields = ['image_1', 'image_2', 'image_3'];
            foreach ($imageFields as $field) {
                if ($tab->$field) {
                    Storage::disk('public')->delete($tab->$field);
                }
            }

            // Delete the tab record
            $tab->delete();

            $this->broadcastContentUpdate('what-we-create-tab', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'What we create tab deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'What we create tab deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during what we create tab deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from tab.
     * 
     * Deletes a single field (e.g., an image) from the tab without deleting the entire tab.
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
                    'message' => 'Unauthorized. Only admins can delete what we create tab fields.',
                ], 403);
            }

            $tab = WhatWeCreateTab::with('categoryTab')->find($id);

            if (!$tab) {
                return response()->json([
                    'success' => false,
                    'message' => 'What we create tab not found.',
                ], 404);
            }

            // List of all allowed fields
            $allowedFields = [
                'tag_label',
                'main_heading',
                'description',
                'features',
                'button_text',
                'image_1',
                'image_2',
                'image_3',
            ];

            // Validate field name
            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            // List of image fields that need file deletion
            $imageFields = ['image_1', 'image_2', 'image_3'];

            // Delete image file if it's an image field
            if (in_array($field, $imageFields) && $tab->$field) {
                Storage::disk('public')->delete($tab->$field);
            }

            // Set field to null in database
            $tab->$field = null;
            $tab->save();
            $tab->refresh();
            $tab->load('categoryTab');

            $this->broadcastContentUpdate('what-we-create-tab', 'updated', [
                'id' => $tab->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'what_we_create_tab' => [
                        'id' => $tab->id,
                        'category_tab_id' => $tab->category_tab_id,
                        'category_name' => $tab->categoryTab ? $tab->categoryTab->category_name : null,
                        'tag_label' => $tab->tag_label,
                        'main_heading' => $tab->main_heading,
                        'description' => $tab->description,
                        'features' => $tab->features ?? [],
                        'button_text' => $tab->button_text,
                        'image_1' => $tab->image_1_url,
                        'image_2' => $tab->image_2_url,
                        'image_3' => $tab->image_3_url,
                        'order' => $tab->order,
                        'updated_at' => $tab->updated_at,
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
