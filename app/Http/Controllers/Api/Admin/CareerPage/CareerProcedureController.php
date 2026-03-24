<?php

namespace App\Http\Controllers\Api\Admin\CareerPage;

use App\Http\Controllers\Controller;
use App\Models\CareerProcedure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CareerProcedureController extends Controller
{
    public function index()
    {
        $items = CareerProcedure::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'career_procedures' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'section_title' => $item->section_title,
                        'section_description' => $item->section_description,
                        'background_image_url' => $item->background_image_url,
                        'background_color' => $item->background_color,
                        'heading' => $item->heading,
                        'description' => $item->description,
                        'step_number' => $item->step_number,
                        'is_active' => $item->is_active,
                        'sort_order' => $item->sort_order,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    public function show($id)
    {
        $item = CareerProcedure::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Career procedure not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'career_procedure' => [
                    'id' => $item->id,
                    'section_title' => $item->section_title,
                    'section_description' => $item->section_description,
                    'background_image_url' => $item->background_image_url,
                    'background_color' => $item->background_color,
                    'heading' => $item->heading,
                    'description' => $item->description,
                    'step_number' => $item->step_number,
                    'is_active' => $item->is_active,
                    'sort_order' => $item->sort_order,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ],
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage career procedures.',
                ], 403);
            }

            $validated = $request->validate([
                'section_title' => ['nullable', 'string', 'max:255'],
                'section_description' => ['nullable', 'string'],
                'background_image_url' => ['nullable', 'string', 'max:2048'],
                'background_color' => ['nullable', 'string', 'max:50'],
                'heading' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'step_number' => ['nullable', 'integer', 'min:1'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['sort_order'] = $validated['sort_order'] ?? 0;
            $validated['step_number'] = $validated['step_number'] ?? 1;

            $item = CareerProcedure::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Career procedure created successfully',
                'data' => [
                    'career_procedure' => [
                        'id' => $item->id,
                        'section_title' => $item->section_title,
                        'section_description' => $item->section_description,
                        'background_image_url' => $item->background_image_url,
                        'background_color' => $item->background_color,
                        'heading' => $item->heading,
                        'description' => $item->description,
                        'step_number' => $item->step_number,
                        'is_active' => $item->is_active,
                        'sort_order' => $item->sort_order,
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
                'message' => 'Failed to create career procedure: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage career procedures.',
                ], 403);
            }

            $item = CareerProcedure::find($id);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career procedure not found.',
                ], 404);
            }

            $validated = $request->validate([
                'section_title' => ['sometimes', 'string', 'max:255'],
                'section_description' => ['sometimes', 'string'],
                'background_image_url' => ['sometimes', 'string', 'max:2048'],
                'background_color' => ['sometimes', 'string', 'max:50'],
                'heading' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'step_number' => ['sometimes', 'integer', 'min:1'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            $item->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Career procedure updated successfully',
                'data' => [
                    'career_procedure' => [
                    'id' => $item->id,
                    'section_title' => $item->section_title,
                    'section_description' => $item->section_description,
                    'background_image_url' => $item->background_image_url,
                    'background_color' => $item->background_color,
                    'heading' => $item->heading,
                    'description' => $item->description,
                        'step_number' => $item->step_number,
                        'is_active' => $item->is_active,
                        'sort_order' => $item->sort_order,
                        'created_at' => $item->created_at,
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
                'message' => 'Failed to update career procedure: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage career procedures.',
                ], 403);
            }

            $item = CareerProcedure::find($id);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career procedure not found.',
                ], 404);
            }

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Career procedure deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete career procedure: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteField(Request $request, $id, $field)
    {
        try {
            $currentUser = $request->user();
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage career procedures.',
                ], 403);
            }

            $item = CareerProcedure::find($id);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career procedure not found.',
                ], 404);
            }

            $allowedFields = [
                'section_title',
                'section_description',
                'heading',
                'description',
                'step_number',
                'is_active',
                'sort_order',
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            $item->update([$field => null]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'career_procedure' => [
                    'id' => $item->id,
                    'section_title' => $item->section_title,
                    'section_description' => $item->section_description,
                    'background_image_url' => $item->background_image_url,
                    'background_color' => $item->background_color,
                    'heading' => $item->heading,
                    'description' => $item->description,
                        'step_number' => $item->step_number,
                        'is_active' => $item->is_active,
                        'sort_order' => $item->sort_order,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field: ' . $e->getMessage(),
            ], 500);
        }
    }
}
