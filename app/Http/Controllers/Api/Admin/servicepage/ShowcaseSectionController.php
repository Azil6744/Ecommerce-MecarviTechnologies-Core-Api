<?php

namespace App\Http\Controllers\Api\Admin\servicepage;

use App\Http\Controllers\Controller;
use App\Models\ServicePage\ShowcaseSection;
use App\Models\ServicePage\ShowcaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShowcaseSectionController extends Controller
{
    /**
     * Get all showcase sections with their items.
     * 
     * Returns all showcase section configurations with associated showcase items.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $showcaseSections = ShowcaseSection::with('showcaseItems')->get();

        if ($showcaseSections->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'showcase_sections' => [],
                    'message' => 'Showcase sections not configured yet.',
                ],
            ], 200);
        }

        $showcaseSectionsData = $showcaseSections->map(function ($section) {
            return [
                'id' => $section->id,
                'section_title' => $section->section_title,
                'section_description' => $section->section_description,
                'section_image' => $section->section_image_url,
                'background_image' => $section->background_image_url,
                'background_image_mobile' => $section->background_image_mobile_url,
                'showcase_items' => $section->showcaseItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'description' => $item->description,
                        'image' => $item->image_url,
                        'video_url' => $item->video_url,
                        'order' => $item->order,
                    ];
                }),
                'created_at' => $section->created_at,
                'updated_at' => $section->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'showcase_sections' => $showcaseSectionsData,
            ],
        ], 200);
    }

    /**
     * Show a specific showcase section by ID with its items.
     * 
     * Returns the showcase section configuration for a specific ID with associated showcase items.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $showcaseSection = ShowcaseSection::with('showcaseItems')->find($id);

        if (!$showcaseSection) {
            return response()->json([
                'success' => false,
                'message' => 'Showcase section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'showcase_section' => [
                    'id' => $showcaseSection->id,
                    'section_title' => $showcaseSection->section_title,
                    'section_description' => $showcaseSection->section_description,
                    'section_image' => $showcaseSection->section_image_url,
                    'background_image' => $showcaseSection->background_image_url,
                    'background_image_mobile' => $showcaseSection->background_image_mobile_url,
                    'showcase_items' => $showcaseSection->showcaseItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'title' => $item->title,
                            'description' => $item->description,
                            'image' => $item->image_url,
                            'video_url' => $item->video_url,
                            'order' => $item->order,
                        ];
                    }),
                    'created_at' => $showcaseSection->created_at,
                    'updated_at' => $showcaseSection->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create showcase section with showcase items.
     * 
     * Creates a new showcase section with associated showcase items.
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
                    'message' => 'Unauthorized. Only admins can manage showcase section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'section_title' => ['nullable', 'string', 'max:255'],
                'section_description' => ['nullable', 'string'],
                'section_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_image_mobile' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'showcase_items' => ['nullable', 'array'],
                'showcase_items.*.id' => ['nullable', 'integer'],
                'showcase_items.*.title' => ['required_with:showcase_items', 'string', 'max:255'],
                'showcase_items.*.description' => ['nullable', 'string'],
                'showcase_items.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'showcase_items.*.video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:51200'],
                'showcase_items.*.video_url' => ['nullable', 'string'],
                'showcase_items.*.order' => ['nullable', 'integer', 'min:0'],
            ]);

            return DB::transaction(function () use ($validated, $request) {
                // Handle section image upload
                if ($request->hasFile('section_image')) {
                    $validated['section_image'] = $request->file('section_image')->store('showcase-section/images', 'public');
                }

                // Handle background image upload
                if ($request->hasFile('background_image')) {
                    $validated['background_image'] = $request->file('background_image')->store('showcase-section/backgrounds', 'public');
                }

                // Handle mobile background image upload
                if ($request->hasFile('background_image_mobile')) {
                    $validated['background_image_mobile'] = $request->file('background_image_mobile')->store('showcase-section/backgrounds', 'public');
                }

                // Create new showcase section
                $showcaseSection = ShowcaseSection::create([
                    'section_title' => $validated['section_title'] ?? null,
                    'section_description' => $validated['section_description'] ?? null,
                    'section_image' => $validated['section_image'] ?? null,
                    'background_image' => $validated['background_image'] ?? null,
                    'background_image_mobile' => $validated['background_image_mobile'] ?? null,
                ]);

                // Handle showcase items if provided
                $showcaseItemsData = [];
                if (isset($validated['showcase_items']) && is_array($validated['showcase_items'])) {
                    foreach ($validated['showcase_items'] as $index => $itemData) {
                        // Handle item image upload
                        $itemImagePath = null;
                        $itemVideoPath = null;
                        if ($request->hasFile("showcase_items.{$index}.image")) {
                            $itemImagePath = $request->file("showcase_items.{$index}.image")->store('showcase-section/images', 'public');
                        }
                        if ($request->hasFile("showcase_items.{$index}.video")) {
                            $itemVideoPath = $request->file("showcase_items.{$index}.video")->store('showcase-section/videos', 'public');
                        } elseif (!empty($itemData['video_url'])) {
                            $itemVideoPath = $itemData['video_url'];
                        }

                        $item = ShowcaseItem::create([
                            'showcase_section_id' => $showcaseSection->id,
                            'title' => $itemData['title'] ?? null,
                            'description' => $itemData['description'] ?? null,
                            'image' => $itemImagePath,
                            'video' => $itemVideoPath,
                            'order' => $itemData['order'] ?? $index,
                        ]);

                        $showcaseItemsData[] = [
                            'id' => $item->id,
                            'title' => $item->title,
                            'description' => $item->description,
                            'image' => $item->image_url,
                            'video_url' => $item->video_url,
                            'order' => $item->order,
                        ];
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Showcase section created successfully',
                    'data' => [
                        'showcase_section' => [
                            'id' => $showcaseSection->id,
                            'section_title' => $showcaseSection->section_title,
                            'section_description' => $showcaseSection->section_description,
                            'section_image' => $showcaseSection->section_image_url,
                            'background_image' => $showcaseSection->background_image_url,
                            'background_image_mobile' => $showcaseSection->background_image_mobile_url,
                            'showcase_items' => $showcaseItemsData,
                            'updated_at' => $showcaseSection->updated_at,
                        ],
                    ],
                ], 200);
            });
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
                'message' => 'Showcase section creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during showcase section creation.',
            ], 500);
        }
    }

    /**
     * Update showcase section content.
     * 
     * Updates the existing showcase section configuration and associated showcase items.
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
                    'message' => 'Unauthorized. Only admins can manage showcase section content.',
                ], 403);
            }

            $showcaseSection = ShowcaseSection::find($id);

            if (!$showcaseSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Showcase section not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // For PUT/PATCH with form-data, Laravel may not parse it automatically
            $allInput = $request->all();
            if (empty($allInput) && $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data')) {
                $allInput = !empty($_POST) ? $_POST : [];
            }
            
            // Merge request data with parsed form-data
            if (!empty($allInput)) {
                $request->merge($allInput);
            }
            
            // Check and update section fields
            $sectionFields = [
                'section_title' => 'string|max:255',
                'section_description' => 'string',
            ];

            foreach ($sectionFields as $field => $rules) {
                if ($request->has($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            return DB::transaction(function () use ($request, $showcaseSection, $dataToUpdate) {
                // Handle section image upload
                if ($request->hasFile('section_image')) {
                    if ($showcaseSection->section_image) {
                        Storage::disk('public')->delete($showcaseSection->section_image);
                    }
                    $dataToUpdate['section_image'] = $request->file('section_image')->store('showcase-section/images', 'public');
                }

                // Handle background image upload
                if ($request->hasFile('background_image')) {
                    // Delete old background image if exists
                    if ($showcaseSection->background_image) {
                        Storage::disk('public')->delete($showcaseSection->background_image);
                    }
                    $dataToUpdate['background_image'] = $request->file('background_image')->store('showcase-section/backgrounds', 'public');
                }

                // Handle mobile background image upload
                if ($request->hasFile('background_image_mobile')) {
                    // Delete old mobile background image if exists
                    if ($showcaseSection->background_image_mobile) {
                        Storage::disk('public')->delete($showcaseSection->background_image_mobile);
                    }
                    $dataToUpdate['background_image_mobile'] = $request->file('background_image_mobile')->store('showcase-section/backgrounds', 'public');
                }
                // Update showcase section
                if (!empty($dataToUpdate)) {
                    $rules = [];
                    
                    foreach ($dataToUpdate as $key => $value) {
                        if (isset($sectionFields[$key])) {
                            $rules[$key] = ['nullable', ...explode('|', $sectionFields[$key])];
                        }
                    }

                    // Add validation for uploaded images
                    if ($request->hasFile('section_image')) {
                        $rules['section_image'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                    }
                    if ($request->hasFile('background_image')) {
                        $rules['background_image'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                    }
                    if ($request->hasFile('background_image_mobile')) {
                        $rules['background_image_mobile'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                    }

                    // Validate
                    if (!empty($rules)) {
                        $request->validate($rules);
                    }

                    $showcaseSection->fill($dataToUpdate);
                    $showcaseSection->save();
                }

                // Handle showcase items update if provided
                $showcaseItemsData = [];
                if ($request->has('showcase_items') && is_array($request->input('showcase_items'))) {
                    $showcaseItems = $request->input('showcase_items');
                    
                    // Get existing items for comparison
                    $existingItems = $showcaseSection->showcaseItems()->get();
                    $existingItemsMap = $existingItems->keyBy('id');
                    
                    // Track which existing items are kept/updated
                    $keptItemIds = [];
                    
                    // Process each item in the request
                    foreach ($showcaseItems as $index => $itemData) {
                        $itemImagePath = null;
                        $itemVideoPath = null;
                        
                        // Check if this is an existing item (has id) or new item
                        if (isset($itemData['id']) && isset($existingItemsMap[$itemData['id']])) {
                            // Update existing item
                            $existingItem = $existingItemsMap[$itemData['id']];
                            $keptItemIds[] = $existingItem->id;
                            
                            // Handle image upload if provided
                            if ($request->hasFile("showcase_items.{$index}.image")) {
                                // Delete old image if exists
                                if ($existingItem->image) {
                                    Storage::disk('public')->delete($existingItem->image);
                                }
                                $itemImagePath = $request->file("showcase_items.{$index}.image")->store('showcase-section/images', 'public');
                            } elseif (!empty($itemData['delete_image'])) {
                                // Image was explicitly deleted by user
                                if ($existingItem->image) {
                                    Storage::disk('public')->delete($existingItem->image);
                                }
                                $itemImagePath = null;
                            } else {
                                // Keep existing image
                                $itemImagePath = $existingItem->image;
                            }

                            if ($request->hasFile("showcase_items.{$index}.video")) {
                                if ($existingItem->video && !str_starts_with($existingItem->video, 'http')) {
                                    Storage::disk('public')->delete($existingItem->video);
                                }
                                $itemVideoPath = $request->file("showcase_items.{$index}.video")->store('showcase-section/videos', 'public');
                            } elseif (!empty($itemData['delete_video'])) {
                                // Video was explicitly deleted by user
                                if ($existingItem->video && !str_starts_with($existingItem->video, 'http')) {
                                    Storage::disk('public')->delete($existingItem->video);
                                }
                                $itemVideoPath = null;
                            } elseif (!empty($itemData['video_url'])) {
                                $itemVideoPath = $itemData['video_url'];
                            } else {
                                $itemVideoPath = $existingItem->video;
                            }
                            
                            // Update the item
                            $existingItem->update([
                                'title' => $itemData['title'] ?? $existingItem->title,
                                'description' => $itemData['description'] ?? $existingItem->description,
                                'image' => $itemImagePath,
                                'video' => $itemVideoPath,
                                'order' => $itemData['order'] ?? $existingItem->order,
                            ]);
                            
                            $showcaseItemsData[] = [
                                'id' => $existingItem->id,
                                'title' => $existingItem->title,
                                'description' => $existingItem->description,
                                'image' => $existingItem->image_url,
                                'video_url' => $existingItem->video_url,
                                'order' => $existingItem->order,
                            ];
                        } else {
                            // Create new item
                            if ($request->hasFile("showcase_items.{$index}.image")) {
                                $itemImagePath = $request->file("showcase_items.{$index}.image")->store('showcase-section/images', 'public');
                            }
                            if ($request->hasFile("showcase_items.{$index}.video")) {
                                $itemVideoPath = $request->file("showcase_items.{$index}.video")->store('showcase-section/videos', 'public');
                            } elseif (!empty($itemData['video_url'])) {
                                $itemVideoPath = $itemData['video_url'];
                            }

                            $item = ShowcaseItem::create([
                                'showcase_section_id' => $showcaseSection->id,
                                'title' => $itemData['title'] ?? null,
                                'description' => $itemData['description'] ?? null,
                                'image' => $itemImagePath,
                                'video' => $itemVideoPath,
                                'order' => $itemData['order'] ?? $index,
                            ]);

                            $showcaseItemsData[] = [
                                'id' => $item->id,
                                'title' => $item->title,
                                'description' => $item->description,
                                'image' => $item->image_url,
                                'video_url' => $item->video_url,
                                'order' => $item->order,
                            ];
                        }
                    }
                    
                    // Delete existing items that are not in the request (removed items)
                    $itemsToDelete = $existingItems->whereNotIn('id', $keptItemIds);
                    foreach ($itemsToDelete as $itemToDelete) {
                        if ($itemToDelete->image) {
                            Storage::disk('public')->delete($itemToDelete->image);
                        }
                        if ($itemToDelete->video && !str_starts_with($itemToDelete->video, 'http')) {
                            Storage::disk('public')->delete($itemToDelete->video);
                        }
                        $itemToDelete->delete();
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Showcase section updated successfully',
                    'data' => [
                        'showcase_section' => [
                            'id' => $showcaseSection->id,
                            'section_title' => $showcaseSection->section_title,
                            'section_description' => $showcaseSection->section_description,
                            'section_image' => $showcaseSection->section_image_url,
                            'background_image' => $showcaseSection->background_image_url,
                            'background_image_mobile' => $showcaseSection->background_image_mobile_url,
                            'showcase_items' => $showcaseItemsData,
                            'updated_at' => $showcaseSection->updated_at,
                        ],
                    ],
                ], 200);
            });
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
                'message' => 'Showcase section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during showcase section update.',
            ], 500);
        }
    }

    /**
     * Delete showcase section content.
     * 
     * Deletes a specific showcase section record and its associated showcase items and images.
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
                    'message' => 'Unauthorized. Only admins can delete showcase section content.',
                ], 403);
            }

            $showcaseSection = ShowcaseSection::find($id);

            if (!$showcaseSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Showcase section not found.',
                ], 404);
            }

            return DB::transaction(function () use ($showcaseSection) {
                // Delete section and background images if they exist
                if ($showcaseSection->section_image) {
                    Storage::disk('public')->delete($showcaseSection->section_image);
                }
                if ($showcaseSection->background_image) {
                    Storage::disk('public')->delete($showcaseSection->background_image);
                }
                if ($showcaseSection->background_image_mobile) {
                    Storage::disk('public')->delete($showcaseSection->background_image_mobile);
                }

                // Delete associated showcase items and their images
                $showcaseItems = $showcaseSection->showcaseItems;
                foreach ($showcaseItems as $item) {
                    if ($item->image) {
                        Storage::disk('public')->delete($item->image);
                    }
                    if ($item->video && !str_starts_with($item->video, 'http')) {
                        Storage::disk('public')->delete($item->video);
                    }
                    $item->delete();
                }

                // Delete the showcase section record
                $showcaseSection->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Showcase section deleted successfully',
                ], 200);
            });
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Showcase section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during showcase section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from showcase section.
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
                    'message' => 'Unauthorized. Only admins can manage showcase section content.',
                ], 403);
            }

            $showcaseSection = ShowcaseSection::find($id);

            if (!$showcaseSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Showcase section not found.',
                ], 404);
            }

            // Validate field name
            $allowedFields = [
                'section_title',
                'section_description',
                'section_image',
                'background_image',
                'background_image_mobile',
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name.',
                    'allowed_fields' => $allowedFields,
                ], 400);
            }

            // Set the field to null and delete associated image if applicable
            if (in_array($field, ['section_image', 'background_image', 'background_image_mobile']) && $showcaseSection->$field) {
                Storage::disk('public')->delete($showcaseSection->$field);
            }
            $showcaseSection->$field = null;
            $showcaseSection->save();

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' deleted successfully from showcase section.",
                'data' => [
                    'showcase_section' => [
                        'id' => $showcaseSection->id,
                        'section_title' => $showcaseSection->section_title,
                        'section_description' => $showcaseSection->section_description,
                        'section_image' => $showcaseSection->section_image_url,
                        'background_image' => $showcaseSection->background_image_url,
                        'background_image_mobile' => $showcaseSection->background_image_mobile_url,
                        'showcase_items' => $showcaseSection->showcaseItems->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'title' => $item->title,
                                'description' => $item->description,
                                'image' => $item->image_url,
                                'video_url' => $item->video_url,
                                'order' => $item->order,
                            ];
                        }),
                        'updated_at' => $showcaseSection->updated_at,
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
