<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCard;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ServiceCardController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get all service cards.
     * 
     * Returns all service cards ordered by order field.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $serviceCards = ServiceCard::orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'service_cards' => $serviceCards->map(function ($card) {
                    return [
                        'id' => $card->id,
                        'subtitle' => $card->subtitle,
                        'description' => $card->description,
                        'image' => $card->image_url,
                        'order' => $card->order,
                        'created_at' => $card->created_at,
                        'updated_at' => $card->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific service card by ID.
     * 
     * Returns a single service card configuration.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $serviceCard = ServiceCard::find($id);

        if (!$serviceCard) {
            return response()->json([
                'success' => false,
                'message' => 'Service card not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_card' => [
                    'id' => $serviceCard->id,
                    'subtitle' => $serviceCard->subtitle,
                    'description' => $serviceCard->description,
                    'image' => $serviceCard->image_url,
                    'order' => $serviceCard->order,
                    'created_at' => $serviceCard->created_at,
                    'updated_at' => $serviceCard->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new service card.
     * 
     * Creates a new service card.
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
                    'message' => 'Unauthorized. Only admins can create service cards.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'subtitle' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'order' => ['nullable', 'integer', 'min:0'],
            ]);

            // Handle image upload (only if provided)
            if ($request->hasFile('image')) {
                // Store new image
                $imagePath = $request->file('image')->store('service-cards', 'public');
                $validated['image'] = $imagePath;
            } else {
                $validated['image'] = null;
            }

            // Set default order if not provided
            if (!isset($validated['order'])) {
                $maxOrder = ServiceCard::max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            // Create service card
            $serviceCard = ServiceCard::create($validated);

            $this->broadcastContentUpdate('service-card', 'updated', [
                'id' => $serviceCard->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service card created successfully',
                'data' => [
                    'service_card' => [
                        'id' => $serviceCard->id,
                        'subtitle' => $serviceCard->subtitle,
                        'description' => $serviceCard->description,
                        'image' => $serviceCard->image_url,
                        'order' => $serviceCard->order,
                        'created_at' => $serviceCard->created_at,
                        'updated_at' => $serviceCard->updated_at,
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
                'message' => 'Service card creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during service card creation.',
            ], 500);
        }
    }

    /**
     * Update service card content.
     * 
     * Updates the existing service card configuration.
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
                    'message' => 'Unauthorized. Only admins can update service cards.',
                ], 403);
            }

            $serviceCard = ServiceCard::find($id);

            if (!$serviceCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service card not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // For PUT/PATCH with form-data, Laravel may not parse it automatically
            // Check if this is a multipart/form-data request (file upload)
            $isMultipart = $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data');
            
            if ($isMultipart) {
                // For form-data, ensure request data is properly parsed
                $allInput = $request->all();
                if (!empty($allInput)) {
                    $request->merge($allInput);
                }
            }

            // Check and update subtitle
            if ($request->has('subtitle')) {
                $dataToUpdate['subtitle'] = $request->input('subtitle');
            }

            // Check and update description
            if ($request->has('description')) {
                $dataToUpdate['description'] = $request->input('description');
            }

            // Check and update order
            if ($request->has('order')) {
                $dataToUpdate['order'] = (int) $request->input('order');
            }

            // Handle image upload (only if provided) and deletion
            $fieldValue = $request->input('image');
            $fieldExists = $request->has('image') || array_key_exists('image', $request->all());
            
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($serviceCard->image) {
                    Storage::disk('public')->delete($serviceCard->image);
                }

                // Store new image
                $imagePath = $request->file('image')->store('service-cards', 'public');
                $dataToUpdate['image'] = $imagePath;
            } elseif ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                // Delete old image if exists
                if ($serviceCard->image) {
                    Storage::disk('public')->delete($serviceCard->image);
                }
                $dataToUpdate['image'] = null;
            }
            // Note: If image is not provided, existing image is preserved

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['subtitle'])) {
                    $rules['subtitle'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['description'])) {
                    $rules['description'] = ['nullable', 'string'];
                }
                if (isset($dataToUpdate['order'])) {
                    $rules['order'] = ['nullable', 'integer', 'min:0'];
                }
                if (isset($dataToUpdate['image']) && $request->hasFile('image')) {
                    $rules['image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }

                // Validate
                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $serviceCard->fill($dataToUpdate);
                $serviceCard->save();
                $serviceCard->refresh();

                $this->broadcastContentUpdate('service-card', 'updated', [
                    'id' => $serviceCard->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Service card updated successfully',
                'data' => [
                    'service_card' => [
                        'id' => $serviceCard->id,
                        'subtitle' => $serviceCard->subtitle,
                        'description' => $serviceCard->description,
                        'image' => $serviceCard->image_url,
                        'order' => $serviceCard->order,
                        'updated_at' => $serviceCard->updated_at,
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
                'message' => 'Service card update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during service card update.',
            ], 500);
        }
    }

    /**
     * Delete service card.
     * 
     * Deletes the service card and associated image.
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
                    'message' => 'Unauthorized. Only admins can delete service cards.',
                ], 403);
            }

            $serviceCard = ServiceCard::find($id);

            if (!$serviceCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service card not found.',
                ], 404);
            }

            // Delete associated image from storage
            if ($serviceCard->image) {
                Storage::disk('public')->delete($serviceCard->image);
            }

            // Delete the service card record
            $serviceCard->delete();

            $this->broadcastContentUpdate('service-card', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service card deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service card deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during service card deletion.',
            ], 500);
        }
    }
}
