<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PortfolioItemController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all portfolio items.
     * 
     * Returns all portfolio items ordered by order field.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $items = PortfolioItem::orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'portfolio_items' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'link' => $item->link,
                        'image' => $item->image_url,
                        'order' => $item->order,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific portfolio item by ID.
     * 
     * Returns a single portfolio item configuration.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $item = PortfolioItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio item not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'portfolio_item' => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'link' => $item->link,
                    'image' => $item->image_url,
                    'order' => $item->order,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new portfolio item.
     * 
     * Creates a new portfolio item.
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
                    'message' => 'Unauthorized. Only admins can create portfolio items.',
                ], 403);
            }

            // Prepare validation rules
            $rules = [
                'title' => ['nullable', 'string', 'max:255'],
                'order' => ['nullable', 'integer', 'min:0'],
            ];

            // Validate link if provided (allow empty string or valid URL)
            if ($request->has('link') && $request->input('link') !== null && $request->input('link') !== '') {
                $rules['link'] = ['string', 'max:500'];
            } else {
                $rules['link'] = ['nullable', 'string', 'max:500'];
            }

            // Validate image only if file is actually uploaded
            if ($request->hasFile('image')) {
                $rules['image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
            }

            $validated = $request->validate($rules);

            // Handle image upload
            if ($request->hasFile('image')) {
                try {
                    // Verify file was uploaded successfully
                    if (!$request->file('image')->isValid()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Image upload failed. File may be too large or corrupted.',
                            'errors' => [
                                'image' => ['The image file could not be uploaded. Please check file size (max 15MB) and try again.']
                            ],
                        ], 422);
                    }

                    $imagePath = $request->file('image')->store('portfolio-items', 'public');
                    $validated['image'] = $imagePath;
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Image upload failed.',
                        'errors' => [
                            'image' => [config('app.debug') ? $e->getMessage() : 'The image failed to upload. Please check file size (max 15MB) and try again.']
                        ],
                    ], 422);
                }
            }

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $maxOrder = PortfolioItem::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Create portfolio item
            $item = PortfolioItem::create($validated);

            // Broadcast content update
            $this->broadcastContentUpdate('portfolio-item', 'created', [
                'id' => $item->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Portfolio item created successfully',
                'data' => [
                    'portfolio_item' => [
                        'id' => $item->id,
                        'title' => $item->title,
                        'link' => $item->link,
                        'image' => $item->image_url,
                        'order' => $item->order,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
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
                'message' => 'Portfolio item creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during portfolio item creation.',
            ], 500);
        }
    }

    /**
     * Update portfolio item content.
     * 
     * Updates the existing portfolio item configuration.
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
                    'message' => 'Unauthorized. Only admins can update portfolio items.',
                ], 403);
            }

            $item = PortfolioItem::find($id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Portfolio item not found.',
                ], 404);
            }

            // Get all input data
            $dataToUpdate = [];

            // Handle text fields
            if ($request->filled('title')) {
                $dataToUpdate['title'] = $request->input('title');
            } elseif ($request->has('title') || array_key_exists('title', $request->all())) {
                $dataToUpdate['title'] = $request->input('title');
            }

            if ($request->filled('link')) {
                $dataToUpdate['link'] = $request->input('link');
            } elseif ($request->has('link') || array_key_exists('link', $request->all())) {
                $dataToUpdate['link'] = $request->input('link');
            }

            // Handle order
            if ($request->has('order')) {
                $dataToUpdate['order'] = (int) $request->input('order');
            }

            // Handle image upload and deletion
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }

                // Store new image
                $imagePath = $request->file('image')->store('portfolio-items', 'public');
                $dataToUpdate['image'] = $imagePath;
            } else {
                // Check if image should be deleted
                $fieldValue = $request->input('image');
                $fieldExists = $request->has('image') || array_key_exists('image', $request->all());
                
                if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                    // Delete old image if exists
                    if ($item->image) {
                        Storage::disk('public')->delete($item->image);
                    }
                    $dataToUpdate['image'] = null;
                }
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['title'])) {
                    $rules['title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['link'])) {
                    $rules['link'] = ['nullable', 'string', 'max:500'];
                }
                if (isset($dataToUpdate['order'])) {
                    $rules['order'] = ['nullable', 'integer', 'min:0'];
                }
                if (isset($dataToUpdate['image']) && $request->hasFile('image')) {
                    $rules['image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $item->fill($dataToUpdate);
                $item->save();
                $item->refresh();

                // Broadcast content update
                $this->broadcastContentUpdate('portfolio-item', 'updated', [
                    'id' => $item->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Portfolio item updated successfully',
                'data' => [
                    'portfolio_item' => [
                        'id' => $item->id,
                        'title' => $item->title,
                        'link' => $item->link,
                        'image' => $item->image_url,
                        'order' => $item->order,
                        'updated_at' => $item->updated_at,
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
                'message' => 'Portfolio item update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during portfolio item update.',
            ], 500);
        }
    }

    /**
     * Delete portfolio item.
     * 
     * Deletes the portfolio item and associated image.
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
                    'message' => 'Unauthorized. Only admins can delete portfolio items.',
                ], 403);
            }

            $item = PortfolioItem::find($id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Portfolio item not found.',
                ], 404);
            }

            // Delete associated image from storage
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            // Delete the portfolio item record
            $itemId = $item->id;
            $item->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('portfolio-item', 'deleted', [
                'id' => $itemId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Portfolio item deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio item deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during portfolio item deletion.',
            ], 500);
        }
    }
}
