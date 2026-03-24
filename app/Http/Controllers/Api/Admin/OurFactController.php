<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OurFact;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OurFactController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get all our facts.
     * 
     * Returns all facts ordered by order field.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $facts = OurFact::orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'our_facts' => $facts->map(function ($fact) {
                    return [
                        'id' => $fact->id,
                        'percentage' => $fact->percentage,
                        'label' => $fact->label,
                        'order' => $fact->order,
                        'created_at' => $fact->created_at,
                        'updated_at' => $fact->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific fact by ID.
     * 
     * Returns a single fact configuration.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $fact = OurFact::find($id);

        if (!$fact) {
            return response()->json([
                'success' => false,
                'message' => 'Our fact not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'our_fact' => [
                    'id' => $fact->id,
                    'percentage' => $fact->percentage,
                    'label' => $fact->label,
                    'order' => $fact->order,
                    'created_at' => $fact->created_at,
                    'updated_at' => $fact->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new fact.
     * 
     * Creates a new our fact.
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
                    'message' => 'Unauthorized. Only admins can create our facts.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'percentage' => ['nullable', 'string', 'max:255'],
                'label' => ['nullable', 'string', 'max:255'],
                'order' => ['nullable', 'integer', 'min:0'],
            ]);

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $maxOrder = OurFact::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Create fact
            $fact = OurFact::create($validated);

            // Broadcast content update
            $this->broadcastContentUpdate('our-fact', 'created', [
                'id' => $fact->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Our fact created successfully',
                'data' => [
                    'our_fact' => [
                        'id' => $fact->id,
                        'percentage' => $fact->percentage,
                        'label' => $fact->label,
                        'order' => $fact->order,
                        'created_at' => $fact->created_at,
                        'updated_at' => $fact->updated_at,
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
                'message' => 'Our fact creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during our fact creation.',
            ], 500);
        }
    }

    /**
     * Update fact content.
     * 
     * Updates the existing fact configuration.
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
                    'message' => 'Unauthorized. Only admins can update our facts.',
                ], 403);
            }

            $fact = OurFact::find($id);

            if (!$fact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Our fact not found.',
                ], 404);
            }

            // Get all input data
            $dataToUpdate = [];

            // Check and update percentage
            if ($request->filled('percentage')) {
                $dataToUpdate['percentage'] = $request->input('percentage');
            } elseif ($request->has('percentage') || array_key_exists('percentage', $request->all())) {
                $dataToUpdate['percentage'] = $request->input('percentage');
            }

            // Check and update label
            if ($request->filled('label')) {
                $dataToUpdate['label'] = $request->input('label');
            } elseif ($request->has('label') || array_key_exists('label', $request->all())) {
                $dataToUpdate['label'] = $request->input('label');
            }

            // Check and update order
            if ($request->has('order')) {
                $dataToUpdate['order'] = (int) $request->input('order');
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['percentage'])) {
                    $rules['percentage'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['label'])) {
                    $rules['label'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['order'])) {
                    $rules['order'] = ['nullable', 'integer', 'min:0'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $fact->fill($dataToUpdate);
                $fact->save();
                $fact->refresh();

                // Broadcast content update
                $this->broadcastContentUpdate('our-fact', 'updated', [
                    'id' => $fact->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Our fact updated successfully',
                'data' => [
                    'our_fact' => [
                        'id' => $fact->id,
                        'percentage' => $fact->percentage,
                        'label' => $fact->label,
                        'order' => $fact->order,
                        'updated_at' => $fact->updated_at,
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
                'message' => 'Our fact update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during our fact update.',
            ], 500);
        }
    }

    /**
     * Delete fact.
     * 
     * Deletes the fact.
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
                    'message' => 'Unauthorized. Only admins can delete our facts.',
                ], 403);
            }

            $fact = OurFact::find($id);

            if (!$fact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Our fact not found.',
                ], 404);
            }

            // Delete the fact record
            $factId = $fact->id;
            $fact->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('our-fact', 'deleted', [
                'id' => $factId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Our fact deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Our fact deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during our fact deletion.',
            ], 500);
        }
    }
}
