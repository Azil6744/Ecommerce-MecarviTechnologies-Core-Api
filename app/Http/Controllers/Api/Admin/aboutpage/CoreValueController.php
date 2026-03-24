<?php

namespace App\Http\Controllers\Api\Admin\aboutpage;

use App\Http\Controllers\Controller;
use App\Models\CoreValue;
use App\Models\CoreValuesSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CoreValueController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all core values.
     * 
     * Returns all core values ordered by order field.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = CoreValuesSection::first();
        $coreValues = CoreValue::orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'core_values_section' => $section ? [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'section_description' => $section->section_description,
                    'updated_at' => $section->updated_at,
                ] : null,
                'core_values' => $coreValues->map(function ($coreValue) {
                    return [
                        'id' => $coreValue->id,
                        'icon' => $coreValue->icon_url,
                        'title' => $coreValue->title,
                        'description' => $coreValue->description,
                        'order' => $coreValue->order,
                        'created_at' => $coreValue->created_at,
                        'updated_at' => $coreValue->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific core value by ID.
     * 
     * Returns a single core value configuration.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $coreValue = CoreValue::find($id);

        if (!$coreValue) {
            return response()->json([
                'success' => false,
                'message' => 'Core value not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'core_value' => [
                    'id' => $coreValue->id,
                    'icon' => $coreValue->icon_url,
                    'title' => $coreValue->title,
                    'description' => $coreValue->description,
                    'order' => $coreValue->order,
                    'created_at' => $coreValue->created_at,
                    'updated_at' => $coreValue->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new core value.
     * 
     * Creates a new core value.
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
                    'message' => 'Unauthorized. Only admins can create core values.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:51200'],
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'order' => ['nullable', 'integer', 'min:0'],
            ]);

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $maxOrder = CoreValue::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Handle icon upload
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('core-values', 'public');
                $validated['icon'] = $iconPath;
            }

            // Create core value
            $coreValue = CoreValue::create($validated);

            // Broadcast content update
            $this->broadcastContentUpdate('core-value', 'created', [
                'id' => $coreValue->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Core value created successfully',
                'data' => [
                    'core_value' => [
                        'id' => $coreValue->id,
                        'icon' => $coreValue->icon_url,
                        'title' => $coreValue->title,
                        'description' => $coreValue->description,
                        'order' => $coreValue->order,
                        'created_at' => $coreValue->created_at,
                        'updated_at' => $coreValue->updated_at,
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
                'message' => 'Core value creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during core value creation.',
            ], 500);
        }
    }

    /**
     * Update core value content.
     * 
     * Updates the existing core value configuration.
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
                    'message' => 'Unauthorized. Only admins can update core values.',
                ], 403);
            }

            $coreValue = CoreValue::find($id);

            if (!$coreValue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Core value not found.',
                ], 404);
            }

            // Get all input data
            $dataToUpdate = [];

            // Check and update title
            if ($request->filled('title')) {
                $dataToUpdate['title'] = $request->input('title');
            } elseif ($request->has('title') || array_key_exists('title', $request->all())) {
                $dataToUpdate['title'] = $request->input('title');
            }

            // Check and update description
            if ($request->filled('description')) {
                $dataToUpdate['description'] = $request->input('description');
            } elseif ($request->has('description') || array_key_exists('description', $request->all())) {
                $dataToUpdate['description'] = $request->input('description');
            }

            // Check and update order
            if ($request->has('order')) {
                $dataToUpdate['order'] = (int) $request->input('order');
            }

            // Handle icon upload or deletion
            if ($request->hasFile('icon')) {
                // Delete old icon if exists
                if ($coreValue->icon) {
                    Storage::disk('public')->delete($coreValue->icon);
                }

                // Store new icon
                $iconPath = $request->file('icon')->store('core-values', 'public');
                $dataToUpdate['icon'] = $iconPath;
            } else {
                // Check if icon should be deleted (null or "delete" string)
                $iconValue = $request->input('icon');
                $iconExists = $request->has('icon') || array_key_exists('icon', $request->all());
                
                if ($iconExists && ($iconValue === null || $iconValue === 'delete' || $iconValue === '')) {
                    // Delete old icon if exists
                    if ($coreValue->icon) {
                        Storage::disk('public')->delete($coreValue->icon);
                    }
                    $dataToUpdate['icon'] = null;
                }
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['icon']) && $request->hasFile('icon')) {
                    $rules['icon'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:51200'];
                }
                if (isset($dataToUpdate['title'])) {
                    $rules['title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['description'])) {
                    $rules['description'] = ['nullable', 'string'];
                }
                if (isset($dataToUpdate['order'])) {
                    $rules['order'] = ['nullable', 'integer', 'min:0'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $coreValue->fill($dataToUpdate);
                $coreValue->save();
                $coreValue->refresh();

                // Broadcast content update
                $this->broadcastContentUpdate('core-value', 'updated', [
                    'id' => $coreValue->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Core value updated successfully',
                'data' => [
                    'core_value' => [
                        'id' => $coreValue->id,
                        'icon' => $coreValue->icon_url,
                        'title' => $coreValue->title,
                        'description' => $coreValue->description,
                        'order' => $coreValue->order,
                        'updated_at' => $coreValue->updated_at,
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
                'message' => 'Core value update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during core value update.',
            ], 500);
        }
    }

    /**
     * Delete core value.
     * 
     * Deletes the core value.
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
                    'message' => 'Unauthorized. Only admins can delete core values.',
                ], 403);
            }

            $coreValue = CoreValue::find($id);

            if (!$coreValue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Core value not found.',
                ], 404);
            }

            // Delete icon if exists
            if ($coreValue->icon) {
                Storage::disk('public')->delete($coreValue->icon);
            }

            // Delete the core value record
            $coreValueId = $coreValue->id;
            $coreValue->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('core-value', 'deleted', [
                'id' => $coreValueId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Core value deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Core value deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during core value deletion.',
            ], 500);
        }
    }
}

