<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use App\Models\SocialMediaSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SocialLinkController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get social media section and links (Public endpoint)
     */
    public function index(Request $request)
    {
        try {
            // Get social media section (heading and description)
            $section = SocialMediaSection::active()->first();

            // Get social links
            $query = SocialLink::query();

            // Filter by active status if provided
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            } else {
                // By default, show only active links for public access
                $query->active();
            }

            $links = $query->ordered()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'social_media_section' => $section ? [
                        'id' => $section->id,
                        'heading' => $section->heading,
                        'description' => $section->description,
                        'is_active' => $section->is_active,
                    ] : null,
                    'social_links' => $links->map(function ($link) {
                        return $this->formatLink($link);
                    }),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch social media data',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Get specific social link (Public endpoint)
     */
    public function show($id)
    {
        try {
            $link = SocialLink::find($id);

            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Social link not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'social_link' => $this->formatLink($link),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch social link',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Get social media section (Public endpoint)
     */
    public function getSection()
    {
        try {
            $section = SocialMediaSection::active()->first();

            if (!$section) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'social_media_section' => null,
                        'message' => 'Social media section not configured yet.',
                    ],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'social_media_section' => [
                        'id' => $section->id,
                        'heading' => $section->heading,
                        'description' => $section->description,
                        'is_active' => $section->is_active,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch social media section',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Create or update social media section (Admin only)
     */
    public function storeSection(Request $request)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage social media sections.',
                ], 403);
            }

            // Normalize boolean values before validation
            $this->normalizeBooleanInput($request, 'is_active');

            $validated = $request->validate([
                'heading' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'is_active' => ['sometimes', 'boolean', 'nullable'],
            ]);

            // Get or create section (only one section allowed)
            $section = SocialMediaSection::first();
            
            if ($section) {
                $section->update($validated);
                $message = 'Social media section updated successfully';
            } else {
                $section = SocialMediaSection::create($validated);
                $message = 'Social media section created successfully';
            }

            $this->broadcastContentUpdate('social-media-sections', $section->wasRecentlyCreated ? 'created' : 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'social_media_section' => [
                        'id' => $section->id,
                        'heading' => $section->heading,
                        'description' => $section->description,
                        'is_active' => $section->is_active,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ],
                ],
            ], $section->wasRecentlyCreated ? 201 : 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Social media section operation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Create a new social link (Admin only)
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage social links.',
                ], 403);
            }

            // Normalize boolean values before validation
            $this->normalizeBooleanInput($request, 'is_active');

            $validated = $request->validate([
                'platform_name' => ['required', 'string', 'max:255'],
                'platform_url' => ['required', 'url', 'max:500'],
                // Allow SVG uploads in addition to raster images
                'icon' => ['nullable', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:51200'],
                'is_active' => ['sometimes', 'boolean', 'nullable'],
                'sort_order' => ['sometimes', 'integer', 'min:0'],
            ]);

            // Handle icon upload
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('social-links', 'public');
                $validated['icon'] = $iconPath;
            } elseif ($request->has('icon') && is_string($request->input('icon'))) {
                $iconString = $request->input('icon');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $iconString, $matches)) {
                    $imageType = $matches[1];
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $iconString));
                    
                    if ($imageData !== false) {
                        $filename = 'social_link_' . time() . '_' . uniqid() . '.' . $imageType;
                        $iconPath = 'social-links/' . $filename;
                        
                        Storage::disk('public')->put($iconPath, $imageData);
                        $validated['icon'] = $iconPath;
                    }
                }
            }

            $link = SocialLink::create($validated);

            $this->broadcastContentUpdate('social-links', 'created', [
                'id' => $link->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Social link created successfully',
                'data' => [
                    'social_link' => $this->formatLink($link),
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
                'message' => 'Social link creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during social link creation.',
            ], 500);
        }
    }

    /**
     * Update a social link (Admin only)
     */
    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage social links.',
                ], 403);
            }

            $link = SocialLink::find($id);

            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Social link not found.',
                ], 404);
            }

            // Normalize boolean values before validation
            $this->normalizeBooleanInput($request, 'is_active');

            $validated = $request->validate([
                'platform_name' => ['sometimes', 'string', 'max:255'],
                'platform_url' => ['sometimes', 'url', 'max:500'],
                // Allow SVG uploads in addition to raster images
                'icon' => ['sometimes', 'nullable', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:51200'],
                'is_active' => ['sometimes', 'boolean', 'nullable'],
                'sort_order' => ['sometimes', 'integer', 'min:0'],
            ]);

            // Handle icon upload
            if ($request->hasFile('icon')) {
                if ($link->icon) {
                    Storage::disk('public')->delete($link->icon);
                }
                $iconPath = $request->file('icon')->store('social-links', 'public');
                $validated['icon'] = $iconPath;
            } elseif ($request->has('icon') && is_string($request->input('icon'))) {
                $iconString = $request->input('icon');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $iconString, $matches)) {
                    $imageType = $matches[1];
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $iconString));
                    
                    if ($imageData !== false) {
                        if ($link->icon) {
                            Storage::disk('public')->delete($link->icon);
                        }
                        
                        $filename = 'social_link_' . time() . '_' . uniqid() . '.' . $imageType;
                        $iconPath = 'social-links/' . $filename;
                        
                        Storage::disk('public')->put($iconPath, $imageData);
                        $validated['icon'] = $iconPath;
                    }
                }
            } elseif ($request->has('icon') && ($request->input('icon') === null || $request->input('icon') === 'delete' || $request->input('icon') === '')) {
                // Delete icon if explicitly set to null/delete/empty
                if ($link->icon) {
                    Storage::disk('public')->delete($link->icon);
                }
                $validated['icon'] = null;
            }

            $link->update($validated);

            $this->broadcastContentUpdate('social-links', 'updated', [
                'id' => $link->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Social link updated successfully',
                'data' => [
                    'social_link' => $this->formatLink($link),
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
                'message' => 'Social link update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during social link update.',
            ], 500);
        }
    }

    /**
     * Delete a social link (Admin only)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete social links.',
                ], 403);
            }

            $link = SocialLink::find($id);

            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Social link not found.',
                ], 404);
            }

            if ($link->icon) {
                Storage::disk('public')->delete($link->icon);
            }

            $linkId = $link->id;
            $link->delete();

            $this->broadcastContentUpdate('social-links', 'deleted', [
                'id' => $linkId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Social link deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Social link deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during social link deletion.',
            ], 500);
        }
    }

    /**
     * Normalize boolean input values
     */
    private function normalizeBooleanInput(Request $request, $field)
    {
        if ($request->has($field)) {
            $value = $request->input($field);
            
            // If it's already a boolean, no need to convert
            if (is_bool($value)) {
                return;
            }
            
            // Convert string/numeric representations to boolean
            if (is_string($value)) {
                $value = strtolower(trim($value));
                if (in_array($value, ['true', '1', 'yes', 'on'])) {
                    $request->merge([$field => true]);
                } elseif (in_array($value, ['false', '0', 'no', 'off', ''])) {
                    $request->merge([$field => false]);
                }
            } elseif (is_numeric($value)) {
                $request->merge([$field => (bool) $value]);
            }
        }
    }

    /**
     * Format social link data for response
     */
    private function formatLink(SocialLink $link)
    {
        return [
            'id' => $link->id,
            'platform_name' => $link->platform_name,
            'platform_url' => $link->platform_url,
            'icon' => $link->icon_url,
            'is_active' => $link->is_active,
            'sort_order' => $link->sort_order,
            'created_at' => $link->created_at,
            'updated_at' => $link->updated_at,
        ];
    }
}
