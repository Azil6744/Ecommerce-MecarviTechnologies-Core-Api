<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OurPromise;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OurPromiseController extends Controller
{
    /**
     * Get our promise content.
     * 
     * Returns the current our promise configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $promise = OurPromise::first();

        if (!$promise) {
            return response()->json([
                'success' => true,
                'data' => [
                    'our_promise' => null,
                    'message' => 'Our promise not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'our_promise' => [
                    'id' => $promise->id,
                    'title' => $promise->title,
                    'description' => $promise->description,
                    'created_at' => $promise->created_at,
                    'updated_at' => $promise->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update our promise content.
     * 
     * Creates a new promise if none exists, or updates the existing one.
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
                    'message' => 'Unauthorized. Only admins can manage our promise content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);

            // Update or create promise
            $promise = OurPromise::updateOrCreate(
                ['id' => OurPromise::first()?->id ?? 0],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Our promise updated successfully',
                'data' => [
                    'our_promise' => [
                        'id' => $promise->id,
                        'title' => $promise->title,
                        'description' => $promise->description,
                        'updated_at' => $promise->updated_at,
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
                'message' => 'Our promise update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during our promise update.',
            ], 500);
        }
    }

    /**
     * Update our promise content.
     * 
     * Updates the existing promise configuration.
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
                    'message' => 'Unauthorized. Only admins can manage our promise content.',
                ], 403);
            }

            $promise = OurPromise::find($id);

            if (!$promise) {
                return response()->json([
                    'success' => false,
                    'message' => 'Our promise not found.',
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

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['title'])) {
                    $rules['title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['description'])) {
                    $rules['description'] = ['nullable', 'string'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $promise->fill($dataToUpdate);
                $promise->save();
                $promise->refresh();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Our promise updated successfully',
                'data' => [
                    'our_promise' => [
                        'id' => $promise->id,
                        'title' => $promise->title,
                        'description' => $promise->description,
                        'updated_at' => $promise->updated_at,
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
                'message' => 'Our promise update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during our promise update.',
            ], 500);
        }
    }

    /**
     * Delete our promise content.
     * 
     * Deletes the promise.
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
                    'message' => 'Unauthorized. Only admins can delete our promise content.',
                ], 403);
            }

            $promise = OurPromise::find($id);

            if (!$promise) {
                return response()->json([
                    'success' => false,
                    'message' => 'Our promise not found.',
                ], 404);
            }

            // Delete the promise record
            $promise->delete();

            return response()->json([
                'success' => true,
                'message' => 'Our promise deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Our promise deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during our promise deletion.',
            ], 500);
        }
    }
}
