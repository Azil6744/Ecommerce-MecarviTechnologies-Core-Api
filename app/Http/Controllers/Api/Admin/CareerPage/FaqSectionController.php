<?php

namespace App\Http\Controllers\Api\Admin\CareerPage;

use App\Http\Controllers\Controller;
use App\Models\FaqSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FaqSectionController extends Controller
{
    public function index()
    {
        $sections = FaqSection::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'faq_sections' => $sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'question' => $section->question,
                        'answer' => $section->answer,
                        'is_active' => $section->is_active,
                        'sort_order' => $section->sort_order,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ];
                }),
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
                    'message' => 'Unauthorized. Only admins can manage FAQ sections.',
                ], 403);
            }

            $validated = $request->validate([
                'section_title' => ['required', 'string', 'max:255'],
                'question' => ['required', 'string', 'max:255'],
                'answer' => ['nullable', 'string'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            // Set default values for optional fields
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['sort_order'] = $validated['sort_order'] ?? 0;

            $section = FaqSection::create($validated);

            $this->broadcastContentUpdate('faq-sections', 'created', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FAQ section created successfully',
                'data' => [
                    'faq_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'question' => $section->question,
                        'answer' => $section->answer,
                        'is_active' => $section->is_active,
                        'sort_order' => $section->sort_order,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ],
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create FAQ section: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $section = FaqSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'faq_section' => [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'question' => $section->question,
                    'answer' => $section->answer,
                    'is_active' => $section->is_active,
                    'sort_order' => $section->sort_order,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage FAQ sections.',
                ], 403);
            }

            $section = FaqSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ section not found.',
                ], 404);
            }

            $validated = $request->validate([
                'section_title' => ['sometimes', 'string', 'max:255'],
                'question' => ['sometimes', 'string', 'max:255'],
                'answer' => ['sometimes', 'string'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            $section->update($validated);

            $this->broadcastContentUpdate('faq-sections', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FAQ section updated successfully',
                'data' => [
                    'faq_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'question' => $section->question,
                        'answer' => $section->answer,
                        'is_active' => $section->is_active,
                        'sort_order' => $section->sort_order,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update FAQ section: ' . $e->getMessage()
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
                    'message' => 'Unauthorized. Only admins can manage FAQ sections.',
                ], 403);
            }

            $section = FaqSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ section not found.',
                ], 404);
            }

            $section->delete();

            $this->broadcastContentUpdate('faq-sections', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FAQ section deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete FAQ section: ' . $e->getMessage()
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
                    'message' => 'Unauthorized. Only admins can manage FAQ sections.',
                ], 403);
            }

            $section = FaqSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ section not found.',
                ], 404);
            }

            $allowedFields = [
                'section_title', 'question', 'answer', 
                'is_active', 'sort_order'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            $section->update([$field => null]);

            $this->broadcastContentUpdate('faq-sections', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'faq_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'question' => $section->question,
                        'answer' => $section->answer,
                        'is_active' => $section->is_active,
                        'sort_order' => $section->sort_order,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field: ' . $e->getMessage()
            ], 500);
        }
    }

    private function broadcastContentUpdate($contentType, $action, $data)
    {
        try {
            $broadcastData = [
                'content_type' => $contentType,
                'action' => $action,
                'data' => $data,
                'timestamp' => now()->toISOString(),
                'socket' => null,
            ];

            broadcast(new \App\Events\ContentUpdated('faq-sections', $action, $broadcastData));
        } catch (\Exception $e) {
            \Log::error('Broadcast failed: ' . $e->getMessage());
        }
    }
}
