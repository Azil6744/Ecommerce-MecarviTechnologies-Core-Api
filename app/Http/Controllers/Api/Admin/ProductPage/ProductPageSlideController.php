<?php

namespace App\Http\Controllers\Api\Admin\ProductPage;

use App\Http\Controllers\Controller;
use App\Models\ProductPageSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductPageSlideController extends Controller
{
    /**
     * Get all product page slides.
     */
    public function index()
    {
        $slides = ProductPageSlide::all();

        if ($slides->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'product_page_slides' => [],
                    'message' => 'Product page slides not configured yet.',
                ],
            ], 200);
        }

        $slidesData = $slides->map(function ($slide) {
            return [
                'id' => $slide->id,
                'page_heading' => $slide->page_heading,
                'bg_image' => $slide->bg_image_url,
                'small_text' => $slide->small_text,
                'main_heading' => $slide->main_heading,
                'outlined_heading' => $slide->outlined_heading,
                'description' => $slide->description,
                'background_text' => $slide->background_text,
                'button_text' => $slide->button_text,
                'button_url' => $slide->button_url,
                'created_at' => $slide->created_at,
                'updated_at' => $slide->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'product_page_slides' => $slidesData,
            ],
        ], 200);
    }

    /**
     * Create a new product page slide.
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage product page content.',
                ], 403);
            }

            // Normalize empty optional URL fields to null
            $optionalUrlFields = ['button_url'];
            foreach ($optionalUrlFields as $f) {
                if ($request->has($f) && is_string($request->input($f)) && trim($request->input($f)) === '') {
                    $request->merge([$f => null]);
                }
            }

            $validated = $request->validate([
                'page_heading' => ['nullable', 'string', 'max:255'],
                'bg_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'small_text' => ['nullable', 'string', 'max:255'],
                'main_heading' => ['nullable', 'string', 'max:255'],
                'outlined_heading' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'background_text' => ['nullable', 'string', 'max:255'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'button_url' => ['nullable', 'string', 'max:255'],
            ]);

            if ($request->hasFile('bg_image')) {
                $bgImagePath = $request->file('bg_image')->store('product-page', 'public');
                $validated['bg_image'] = $bgImagePath;
            }

            $slide = ProductPageSlide::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product page slide created successfully',
                'data' => [
                    'product_page_slide' => [
                        'id' => $slide->id,
                        'page_heading' => $slide->page_heading,
                        'bg_image' => $slide->bg_image_url,
                        'small_text' => $slide->small_text,
                        'main_heading' => $slide->main_heading,
                        'outlined_heading' => $slide->outlined_heading,
                        'description' => $slide->description,
                        'background_text' => $slide->background_text,
                        'button_text' => $slide->button_text,
                        'button_url' => $slide->button_url,
                        'updated_at' => $slide->updated_at,
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
                'message' => 'Product page slide creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Update a product page slide.
     */
    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage product page content.',
                ], 403);
            }

            $slide = ProductPageSlide::find($id);

            if (!$slide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product page slide not found.',
                ], 404);
            }

            $dataToUpdate = [];

            $allInput = $request->all();
            if (empty($allInput) && $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data')) {
                $allInput = !empty($_POST) ? $_POST : [];
            }

            if (!empty($allInput)) {
                $request->merge($allInput);
            }

            $fields = [
                'page_heading' => 'string|max:255',
                'small_text' => 'string|max:255',
                'main_heading' => 'string|max:255',
                'outlined_heading' => 'string|max:255',
                'description' => 'string',
                'background_text' => 'string|max:255',
                'button_text' => 'string|max:255',
                'button_url' => 'string|max:255',
            ];

            foreach ($fields as $field => $rules) {
                $value = $request->input($field);
                if ($request->has($field)) {
                    $dataToUpdate[$field] = (is_string($value) && trim($value) === '') ? null : $value;
                }
            }

            if ($request->hasFile('bg_image')) {
                $request->validate([
                    'bg_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);

                if ($slide->bg_image) {
                    Storage::disk('public')->delete($slide->bg_image);
                }

                $bgImagePath = $request->file('bg_image')->store('product-page', 'public');
                $dataToUpdate['bg_image'] = $bgImagePath;
            }

            if (!empty($dataToUpdate)) {
                $request->merge($dataToUpdate);

                $rules = [];
                foreach ($dataToUpdate as $key => $value) {
                    if (isset($fields[$key])) {
                        $rules[$key] = ['nullable', ...explode('|', $fields[$key])];
                    }
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                $slide->fill($dataToUpdate);
                $slide->save();
                $slide->refresh();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product page slide updated successfully',
                'data' => [
                    'product_page_slide' => [
                        'id' => $slide->id,
                        'page_heading' => $slide->page_heading,
                        'bg_image' => $slide->bg_image_url,
                        'small_text' => $slide->small_text,
                        'main_heading' => $slide->main_heading,
                        'outlined_heading' => $slide->outlined_heading,
                        'description' => $slide->description,
                        'background_text' => $slide->background_text,
                        'button_text' => $slide->button_text,
                        'button_url' => $slide->button_url,
                        'updated_at' => $slide->updated_at,
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
                'message' => 'Product page slide update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Show a specific product page slide.
     */
    public function show($id)
    {
        $slide = ProductPageSlide::find($id);

        if (!$slide) {
            return response()->json([
                'success' => false,
                'message' => 'Product page slide not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product_page_slide' => [
                    'id' => $slide->id,
                    'page_heading' => $slide->page_heading,
                    'bg_image' => $slide->bg_image_url,
                    'small_text' => $slide->small_text,
                    'main_heading' => $slide->main_heading,
                    'outlined_heading' => $slide->outlined_heading,
                    'description' => $slide->description,
                    'background_text' => $slide->background_text,
                    'button_text' => $slide->button_text,
                    'button_url' => $slide->button_url,
                    'created_at' => $slide->created_at,
                    'updated_at' => $slide->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete a product page slide.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete product page content.',
                ], 403);
            }

            $slide = ProductPageSlide::find($id);

            if (!$slide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product page slide not found.',
                ], 404);
            }

            if ($slide->bg_image) {
                Storage::disk('public')->delete($slide->bg_image);
            }

            $slide->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product page slide deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product page slide deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from a product page slide.
     */
    public function deleteField(Request $request, $id, $field)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage product page content.',
                ], 403);
            }

            $slide = ProductPageSlide::find($id);

            if (!$slide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product page slide not found.',
                ], 404);
            }

            $allowedFields = [
                'page_heading',
                'bg_image',
                'small_text',
                'main_heading',
                'outlined_heading',
                'description',
                'background_text',
                'button_text',
                'button_url'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name.',
                    'allowed_fields' => $allowedFields,
                ], 400);
            }

            if ($field === 'bg_image' && $slide->bg_image) {
                Storage::disk('public')->delete($slide->bg_image);
            }

            $slide->$field = null;
            $slide->save();

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' deleted successfully from product page slide.",
                'data' => [
                    'product_page_slide' => [
                        'id' => $slide->id,
                        'page_heading' => $slide->page_heading,
                        'bg_image' => $slide->bg_image_url,
                        'small_text' => $slide->small_text,
                        'main_heading' => $slide->main_heading,
                        'outlined_heading' => $slide->outlined_heading,
                        'description' => $slide->description,
                        'background_text' => $slide->background_text,
                        'button_text' => $slide->button_text,
                        'button_url' => $slide->button_url,
                        'updated_at' => $slide->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Field deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }
}
