<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhyChooseUsTab;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WhyChooseUsTabController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get all why choose us tabs.
     * 
     * Returns all tabs ordered by order field.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tabs = WhyChooseUsTab::orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'why_choose_us_tabs' => $tabs->map(function ($tab) {
                    return [
                        'id' => $tab->id,
                        'title' => $tab->title,
                        'description' => $tab->description,
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
        $tab = WhyChooseUsTab::find($id);

        if (!$tab) {
            return response()->json([
                'success' => false,
                'message' => 'Why choose us tab not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'why_choose_us_tab' => [
                    'id' => $tab->id,
                    'title' => $tab->title,
                    'description' => $tab->description,
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
     * Creates a new why choose us tab.
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
                    'message' => 'Unauthorized. Only admins can create why choose us tabs.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'order' => ['nullable', 'integer', 'min:0'],
            ]);

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $maxOrder = WhyChooseUsTab::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Create tab
            $tab = WhyChooseUsTab::create($validated);

            $this->broadcastContentUpdate('why-choose-us-tab', 'updated', [
                'id' => $tab->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Why choose us tab created successfully',
                'data' => [
                    'why_choose_us_tab' => [
                        'id' => $tab->id,
                        'title' => $tab->title,
                        'description' => $tab->description,
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
                'message' => 'Why choose us tab creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during why choose us tab creation.',
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
                    'message' => 'Unauthorized. Only admins can update why choose us tabs.',
                ], 403);
            }

            $tab = WhyChooseUsTab::find($id);

            if (!$tab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Why choose us tab not found.',
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

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
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
                $tab->fill($dataToUpdate);
                $tab->save();
                $tab->refresh();

                $this->broadcastContentUpdate('why-choose-us-tab', 'updated', [
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
                'message' => 'Why choose us tab updated successfully',
                'data' => [
                    'why_choose_us_tab' => [
                        'id' => $tab->id,
                        'title' => $tab->title,
                        'description' => $tab->description,
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
                'message' => 'Why choose us tab update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during why choose us tab update.',
            ], 500);
        }
    }

    /**
     * Delete tab.
     * 
     * Deletes the tab.
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
                    'message' => 'Unauthorized. Only admins can delete why choose us tabs.',
                ], 403);
            }

            $tab = WhyChooseUsTab::find($id);

            if (!$tab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Why choose us tab not found.',
                ], 404);
            }

            // Delete the tab record
            $tab->delete();

            $this->broadcastContentUpdate('why-choose-us-tab', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Why choose us tab deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Why choose us tab deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during why choose us tab deletion.',
            ], 500);
        }
    }
}
