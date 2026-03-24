<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all reviews.
     * 
     * Returns all reviews ordered by order field.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $reviews = Review::orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'review_quote' => $review->review_quote,
                        'avatar' => $review->avatar_url,
                        'name' => $review->name,
                        'designation' => $review->designation,
                        'rating' => $review->rating,
                        'order' => $review->order,
                        'created_at' => $review->created_at,
                        'updated_at' => $review->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific review by ID.
     * 
     * Returns a single review configuration.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'review' => [
                    'id' => $review->id,
                    'review_quote' => $review->review_quote,
                    'avatar' => $review->avatar_url,
                    'name' => $review->name,
                    'designation' => $review->designation,
                    'rating' => $review->rating,
                    'order' => $review->order,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new review.
     * 
     * Creates a new review card.
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
                    'message' => 'Unauthorized. Only admins can create reviews.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'review_quote' => ['nullable', 'string'],
                'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'name' => ['nullable', 'string', 'max:255'],
                'designation' => ['nullable', 'string', 'max:255'],
                'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
                'order' => ['nullable', 'integer', 'min:0'],
            ]);

            // Handle avatar image upload
            if ($request->hasFile('avatar')) {
                $imagePath = $request->file('avatar')->store('reviews', 'public');
                $validated['avatar'] = $imagePath;
            }

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $maxOrder = Review::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Create review
            $review = Review::create($validated);

            // Broadcast content update
            $this->broadcastContentUpdate('review', 'created', [
                'id' => $review->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review created successfully',
                'data' => [
                    'review' => [
                        'id' => $review->id,
                        'review_quote' => $review->review_quote,
                        'avatar' => $review->avatar_url,
                        'name' => $review->name,
                        'designation' => $review->designation,
                        'rating' => $review->rating,
                        'order' => $review->order,
                        'created_at' => $review->created_at,
                        'updated_at' => $review->updated_at,
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
                'message' => 'Review creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during review creation.',
            ], 500);
        }
    }

    /**
     * Update review content.
     * 
     * Updates the existing review configuration.
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
                    'message' => 'Unauthorized. Only admins can update reviews.',
                ], 403);
            }

            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found.',
                ], 404);
            }

            // Get all input data
            $dataToUpdate = [];

            // Handle text fields
            $textFields = ['review_quote', 'name', 'designation', 'rating'];
            foreach ($textFields as $field) {
                if ($request->filled($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                } elseif ($request->has($field) || array_key_exists($field, $request->all())) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // Handle order
            if ($request->has('order')) {
                $dataToUpdate['order'] = (int) $request->input('order');
            }

            // Handle avatar image upload and deletion
            if ($request->hasFile('avatar')) {
                // Delete old image if exists
                if ($review->avatar) {
                    Storage::disk('public')->delete($review->avatar);
                }

                // Store new image
                $imagePath = $request->file('avatar')->store('reviews', 'public');
                $dataToUpdate['avatar'] = $imagePath;
            } else {
                // Check if avatar should be deleted
                $fieldValue = $request->input('avatar');
                $fieldExists = $request->has('avatar') || array_key_exists('avatar', $request->all());
                
                if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                    // Delete old image if exists
                    if ($review->avatar) {
                        Storage::disk('public')->delete($review->avatar);
                    }
                    $dataToUpdate['avatar'] = null;
                }
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['review_quote'])) {
                    $rules['review_quote'] = ['nullable', 'string'];
                }
                if (isset($dataToUpdate['name'])) {
                    $rules['name'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['designation'])) {
                    $rules['designation'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['rating'])) {
                    $rules['rating'] = ['nullable', 'numeric', 'min:0', 'max:5'];
                }
                if (isset($dataToUpdate['order'])) {
                    $rules['order'] = ['nullable', 'integer', 'min:0'];
                }
                if (isset($dataToUpdate['avatar']) && $request->hasFile('avatar')) {
                    $rules['avatar'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $review->fill($dataToUpdate);
                $review->save();
                $review->refresh();

                // Broadcast content update
                $this->broadcastContentUpdate('review', 'updated', [
                    'id' => $review->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => [
                    'review' => [
                        'id' => $review->id,
                        'review_quote' => $review->review_quote,
                        'avatar' => $review->avatar_url,
                        'name' => $review->name,
                        'designation' => $review->designation,
                        'rating' => $review->rating,
                        'order' => $review->order,
                        'updated_at' => $review->updated_at,
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
                'message' => 'Review update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during review update.',
            ], 500);
        }
    }

    /**
     * Delete review.
     * 
     * Deletes the review and associated avatar.
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
                    'message' => 'Unauthorized. Only admins can delete reviews.',
                ], 403);
            }

            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found.',
                ], 404);
            }

            // Delete associated avatar image from storage
            if ($review->avatar) {
                Storage::disk('public')->delete($review->avatar);
            }

            // Delete the review record
            $reviewId = $review->id;
            $review->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('review', 'deleted', [
                'id' => $reviewId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Review deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during review deletion.',
            ], 500);
        }
    }
}
