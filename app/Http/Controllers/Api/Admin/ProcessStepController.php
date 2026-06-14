<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessStep;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProcessStepController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get all process steps.
     * 
     * Returns all steps ordered by order field.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $steps = ProcessStep::orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'process_steps' => $steps->map(function ($step) {
                    return [
                        'id' => $step->id,
                        'number' => $step->number,
                        'title' => $step->title,
                        'description' => $step->description,
                        'order' => $step->order,
                        'created_at' => $step->created_at,
                        'updated_at' => $step->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific step by ID.
     * 
     * Returns a single step configuration.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $step = ProcessStep::find($id);

        if (!$step) {
            return response()->json([
                'success' => false,
                'message' => 'Process step not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'process_step' => [
                    'id' => $step->id,
                    'number' => $step->number,
                    'title' => $step->title,
                    'description' => $step->description,
                    'order' => $step->order,
                    'created_at' => $step->created_at,
                    'updated_at' => $step->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new step.
     * 
     * Creates a new process step.
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
                    'message' => 'Unauthorized. Only admins can create process steps.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'number' => ['nullable', 'integer', 'min:0'],
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'order' => ['nullable', 'integer', 'min:0'],
            ]);

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $maxOrder = ProcessStep::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Create step
            $step = ProcessStep::create($validated);

            $this->broadcastContentUpdate('process-step', 'updated', [
                'id' => $step->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Process step created successfully',
                'data' => [
                    'process_step' => [
                        'id' => $step->id,
                        'number' => $step->number,
                        'title' => $step->title,
                        'description' => $step->description,
                        'order' => $step->order,
                        'created_at' => $step->created_at,
                        'updated_at' => $step->updated_at,
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
                'message' => 'Process step creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during process step creation.',
            ], 500);
        }
    }

    /**
     * Update step content.
     * 
     * Updates the existing step configuration.
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
                    'message' => 'Unauthorized. Only admins can update process steps.',
                ], 403);
            }

            $step = ProcessStep::find($id);

            if (!$step) {
                return response()->json([
                    'success' => false,
                    'message' => 'Process step not found.',
                ], 404);
            }

            // Get all input data
            $dataToUpdate = [];

            // Check and update number
            if ($request->has('number')) {
                $dataToUpdate['number'] = (int) $request->input('number');
            }

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
                
                if (isset($dataToUpdate['number'])) {
                    $rules['number'] = ['nullable', 'integer', 'min:0'];
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
                $step->fill($dataToUpdate);
                $step->save();
                $step->refresh();

                $this->broadcastContentUpdate('process-step', 'updated', [
                    'id' => $step->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Process step updated successfully',
                'data' => [
                    'process_step' => [
                        'id' => $step->id,
                        'number' => $step->number,
                        'title' => $step->title,
                        'description' => $step->description,
                        'order' => $step->order,
                        'updated_at' => $step->updated_at,
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
                'message' => 'Process step update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during process step update.',
            ], 500);
        }
    }

    /**
     * Delete step.
     * 
     * Deletes the step.
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
                    'message' => 'Unauthorized. Only admins can delete process steps.',
                ], 403);
            }

            $step = ProcessStep::find($id);

            if (!$step) {
                return response()->json([
                    'success' => false,
                    'message' => 'Process step not found.',
                ], 404);
            }

            // Delete the step record
            $step->delete();

            $this->broadcastContentUpdate('process-step', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Process step deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Process step deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during process step deletion.',
            ], 500);
        }
    }
}
