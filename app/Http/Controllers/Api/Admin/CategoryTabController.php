<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryTab;
use App\Models\WhatWeCreateTab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CategoryTabController extends Controller
{
    /**
     * Get all category tabs.
     * 
     * Returns all category tabs ordered by order field.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categoryTabs = CategoryTab::orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'category_tabs' => $categoryTabs->map(function ($tab) {
                    return [
                        'id' => $tab->id,
                        'category_name' => $tab->category_name,
                        'order' => $tab->order,
                        'created_at' => $tab->created_at,
                        'updated_at' => $tab->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific category tab by ID.
     * 
     * Returns a single category tab configuration.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $categoryTab = CategoryTab::with(['tabs' => function($query) {
            $query->orderBy('order', 'asc');
        }])->find($id);

        if (!$categoryTab) {
            return response()->json([
                'success' => false,
                'message' => 'Category tab not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'category_tab' => [
                    'id' => $categoryTab->id,
                    'category_name' => $categoryTab->category_name,
                    'order' => $categoryTab->order,
                    'tabs' => $categoryTab->tabs->map(function($tab) {
                        return [
                            'id' => $tab->id,
                            'tag_label' => $tab->tag_label,
                            'main_heading' => $tab->main_heading,
                            'description' => $tab->description,
                            'features' => $tab->features ?? [],
                            'button_text' => $tab->button_text,
                            'image_1' => $tab->image_1_url,
                            'image_2' => $tab->image_2_url,
                            'image_3' => $tab->image_3_url,
                            'order' => $tab->order,
                        ];
                    }),
                    'created_at' => $categoryTab->created_at,
                    'updated_at' => $categoryTab->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new category tab.
     * 
     * Creates a new category tab.
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
                    'message' => 'Unauthorized. Only admins can create category tabs.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'category_name' => ['required', 'string', 'max:255'],
                'order' => ['nullable', 'integer', 'min:0'],
            ]);

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $maxOrder = CategoryTab::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Create category tab
            $categoryTab = CategoryTab::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category tab created successfully',
                'data' => [
                    'category_tab' => [
                        'id' => $categoryTab->id,
                        'category_name' => $categoryTab->category_name,
                        'order' => $categoryTab->order,
                        'created_at' => $categoryTab->created_at,
                        'updated_at' => $categoryTab->updated_at,
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
                'message' => 'Category tab creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during category tab creation.',
            ], 500);
        }
    }

    /**
     * Update category tab content.
     * 
     * Updates the existing category tab configuration.
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
                    'message' => 'Unauthorized. Only admins can update category tabs.',
                ], 403);
            }

            $categoryTab = CategoryTab::find($id);

            if (!$categoryTab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category tab not found.',
                ], 404);
            }

            // Get all input data
            $dataToUpdate = [];

            // Check and update category_name
            if ($request->filled('category_name')) {
                $dataToUpdate['category_name'] = $request->input('category_name');
            } elseif ($request->has('category_name')) {
                $dataToUpdate['category_name'] = $request->input('category_name');
            }

            // Check and update order
            if ($request->has('order')) {
                $dataToUpdate['order'] = (int) $request->input('order');
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['category_name'])) {
                    $rules['category_name'] = ['required', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['order'])) {
                    $rules['order'] = ['nullable', 'integer', 'min:0'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $categoryTab->fill($dataToUpdate);
                $categoryTab->save();
                $categoryTab->refresh();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category tab updated successfully',
                'data' => [
                    'category_tab' => [
                        'id' => $categoryTab->id,
                        'category_name' => $categoryTab->category_name,
                        'order' => $categoryTab->order,
                        'updated_at' => $categoryTab->updated_at,
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
                'message' => 'Category tab update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during category tab update.',
            ], 500);
        }
    }

    /**
     * Delete category tab.
     * 
     * Deletes the category tab.
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
                    'message' => 'Unauthorized. Only admins can delete category tabs.',
                ], 403);
            }

            $categoryTab = CategoryTab::with('tabs')->find($id);

            if (!$categoryTab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category tab not found.',
                ], 404);
            }

            // Delete all associated tab images before deleting the category
            // (The tabs will be cascade deleted, but we need to clean up their images first)
            foreach ($categoryTab->tabs as $tab) {
                $imageFields = ['image_1', 'image_2', 'image_3'];
                foreach ($imageFields as $field) {
                    if ($tab->$field) {
                        Storage::disk('public')->delete($tab->$field);
                    }
                }
            }

            // Delete the category tab record
            // This will automatically delete all associated tabs due to cascade delete
            $categoryTab->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category tab deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category tab deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during category tab deletion.',
            ], 500);
        }
    }
}
