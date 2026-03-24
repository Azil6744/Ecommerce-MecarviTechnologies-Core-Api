<?php

namespace App\Http\Controllers\Api\Admin\CareerPage;

use App\Http\Controllers\Controller;
use App\Models\SupportSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SupportSectionController extends Controller
{
    public function index()
    {
        $sections = SupportSection::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'support_sections' => $sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'title' => $section->title,
                        'description' => $section->description,
                        'call_icon' => $section->call_icon,
                        'call_title' => $section->call_title,
                        'call_description' => $section->call_description,
                        'call_phone' => $section->call_phone,
                        'email_icon' => $section->email_icon,
                        'email_title' => $section->email_title,
                        'email_description' => $section->email_description,
                        'email_address' => $section->email_address,
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
                    'message' => 'Unauthorized. Only admins can manage support sections.',
                ], 403);
            }

            // Prepare validation rules dynamically
            $rules = [
                'section_title' => ['required', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'call_title' => ['nullable', 'string', 'max:255'],
                'call_description' => ['nullable', 'string'],
                'call_phone' => ['nullable', 'string', 'max:255'],
                'email_title' => ['nullable', 'string', 'max:255'],
                'email_description' => ['nullable', 'string'],
                'email_address' => ['nullable', 'email', 'max:255'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ];

            // Add icon validation rules based on input type
            if ($request->hasFile('call_icon')) {
                $rules['call_icon'] = ['nullable', 'file', 'image', 'max:51200', 'mimes:jpeg,png,jpg,gif,webp'];
            } else {
                $rules['call_icon'] = ['nullable', 'string', 'max:255'];
            }

            if ($request->hasFile('email_icon')) {
                $rules['email_icon'] = ['nullable', 'file', 'image', 'max:51200', 'mimes:jpeg,png,jpg,gif,webp'];
            } else {
                $rules['email_icon'] = ['nullable', 'string', 'max:255'];
            }

            $validated = $request->validate($rules);

            // Handle file uploads for icon fields
            if ($request->hasFile('call_icon')) {
                $callIconPath = $request->file('call_icon')->store('support-icons', 'public');
                $validated['call_icon'] = $callIconPath;
            } elseif ($request->has('call_icon') && is_array($request->input('call_icon'))) {
                $validated['call_icon'] = $request->input('call_icon')[0] ?? null;
            }

            if ($request->hasFile('email_icon')) {
                $emailIconPath = $request->file('email_icon')->store('support-icons', 'public');
                $validated['email_icon'] = $emailIconPath;
            } elseif ($request->has('email_icon') && is_array($request->input('email_icon'))) {
                $validated['email_icon'] = $request->input('email_icon')[0] ?? null;
            }

            // Set default values for optional fields
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['sort_order'] = $validated['sort_order'] ?? 0;

            $section = SupportSection::create($validated);

            $this->broadcastContentUpdate('support-sections', 'created', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Support section created successfully',
                'data' => [
                    'support_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'title' => $section->title,
                        'description' => $section->description,
                        'call_icon' => $section->call_icon,
                        'call_title' => $section->call_title,
                        'call_description' => $section->call_description,
                        'call_phone' => $section->call_phone,
                        'email_icon' => $section->email_icon,
                        'email_title' => $section->email_title,
                        'email_description' => $section->email_description,
                        'email_address' => $section->email_address,
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
                'message' => 'Failed to create support section: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $section = SupportSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Support section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'support_section' => [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'title' => $section->title,
                    'description' => $section->description,
                    'call_icon' => $section->call_icon,
                    'call_title' => $section->call_title,
                    'call_description' => $section->call_description,
                    'call_phone' => $section->call_phone,
                    'email_icon' => $section->email_icon,
                    'email_title' => $section->email_title,
                    'email_description' => $section->email_description,
                    'email_address' => $section->email_address,
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
                    'message' => 'Unauthorized. Only admins can manage support sections.',
                ], 403);
            }

            $section = SupportSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Support section not found.',
                ], 404);
            }

            // Prepare validation rules dynamically
            $rules = [
                'section_title' => ['sometimes', 'string', 'max:255'],
                'title' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'call_title' => ['sometimes', 'string', 'max:255'],
                'call_description' => ['sometimes', 'string'],
                'call_phone' => ['sometimes', 'string', 'max:255'],
                'email_title' => ['sometimes', 'string', 'max:255'],
                'email_description' => ['sometimes', 'string'],
                'email_address' => ['sometimes', 'email', 'max:255'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ];

            // Add icon validation rules based on input type
            if ($request->hasFile('call_icon')) {
                $rules['call_icon'] = ['sometimes', 'file', 'image', 'max:51200', 'mimes:jpeg,png,jpg,gif,webp'];
            } else {
                $rules['call_icon'] = ['sometimes', 'string', 'max:255'];
            }

            if ($request->hasFile('email_icon')) {
                $rules['email_icon'] = ['sometimes', 'file', 'image', 'max:51200', 'mimes:jpeg,png,jpg,gif,webp'];
            } else {
                $rules['email_icon'] = ['sometimes', 'string', 'max:255'];
            }

            $validated = $request->validate($rules);

            // Handle file uploads for icon fields
            if ($request->hasFile('call_icon')) {
                // Delete old icon if exists
                if ($section->call_icon) {
                    Storage::disk('public')->delete($section->call_icon);
                }
                $callIconPath = $request->file('call_icon')->store('support-icons', 'public');
                $validated['call_icon'] = $callIconPath;
            } elseif ($request->has('call_icon') && is_array($request->input('call_icon'))) {
                $validated['call_icon'] = $request->input('call_icon')[0] ?? null;
            }

            if ($request->hasFile('email_icon')) {
                // Delete old icon if exists
                if ($section->email_icon) {
                    Storage::disk('public')->delete($section->email_icon);
                }
                $emailIconPath = $request->file('email_icon')->store('support-icons', 'public');
                $validated['email_icon'] = $emailIconPath;
            } elseif ($request->has('email_icon') && is_array($request->input('email_icon'))) {
                $validated['email_icon'] = $request->input('email_icon')[0] ?? null;
            }

            $section->update($validated);

            $this->broadcastContentUpdate('support-sections', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Support section updated successfully',
                'data' => [
                    'support_section' => [
                        'id' => $section->id,
                    'section_title' => $section->section_title,
                    'title' => $section->title,
                    'description' => $section->description,
                    'call_icon' => $section->call_icon,
                        'call_title' => $section->call_title,
                        'call_description' => $section->call_description,
                        'call_phone' => $section->call_phone,
                        'email_icon' => $section->email_icon,
                        'email_title' => $section->email_title,
                        'email_description' => $section->email_description,
                        'email_address' => $section->email_address,
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
                'message' => 'Failed to update support section: ' . $e->getMessage()
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
                    'message' => 'Unauthorized. Only admins can manage support sections.',
                ], 403);
            }

            $section = SupportSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Support section not found.',
                ], 404);
            }

            // Delete associated icon files if they exist
            if ($section->call_icon) {
                Storage::disk('public')->delete($section->call_icon);
            }
            if ($section->email_icon) {
                Storage::disk('public')->delete($section->email_icon);
            }

            $section->delete();

            $this->broadcastContentUpdate('support-sections', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Support section deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete support section: ' . $e->getMessage()
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
                    'message' => 'Unauthorized. Only admins can manage support sections.',
                ], 403);
            }

            $section = SupportSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Support section not found.',
                ], 404);
            }

            $allowedFields = [
                'section_title', 'title', 'description', 'call_icon', 'call_title', 
                'call_description', 'call_phone', 'email_icon', 'email_title', 
                'email_description', 'email_address', 'is_active', 'sort_order'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            $section->update([$field => null]);

            $this->broadcastContentUpdate('support-sections', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'support_section' => [
                        'id' => $section->id,
                    'section_title' => $section->section_title,
                    'title' => $section->title,
                    'description' => $section->description,
                    'call_icon' => $section->call_icon,
                        'call_title' => $section->call_title,
                        'call_description' => $section->call_description,
                        'call_phone' => $section->call_phone,
                        'email_icon' => $section->email_icon,
                        'email_title' => $section->email_title,
                        'email_description' => $section->email_description,
                        'email_address' => $section->email_address,
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

            broadcast(new \App\Events\ContentUpdated('support-sections', $action, $broadcastData));
        } catch (\Exception $e) {
            \Log::error('Broadcast failed: ' . $e->getMessage());
        }
    }
}
