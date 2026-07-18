<?php

namespace App\Http\Controllers\Api\Admin\ProductPage;

use App\Http\Controllers\Controller;
use App\Models\ProductPageHeroSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ProductPageHeroSectionController extends Controller
{
    use BroadcastsContentUpdates;

    public function index()
    {
        $section = ProductPageHeroSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'product_page_hero_section' => null,
                    'message' => 'Product page hero section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product_page_hero_section' => $section,
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            if (!$request->user()->hasAdminAccess()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $validated = $request->validate([
                'hero_title' => ['nullable', 'string', 'max:255'],
                'description_title' => ['nullable', 'string', 'max:255'],
                'hero_description' => ['nullable', 'string'],
                'section_bg_color' => ['nullable', 'string', 'max:20'],
                'image_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            ]);

            $existingSection = ProductPageHeroSection::first();

            $updateData = [
                'hero_title' => $validated['hero_title'] ?? null,
                'description_title' => $validated['description_title'] ?? null,
                'hero_description' => $validated['hero_description'] ?? null,
                'section_bg_color' => $validated['section_bg_color'] ?? '#ff6a00',
            ];

            if ($request->hasFile('image_url')) {
                if ($existingSection && $existingSection->image_url) {
                    Storage::disk('public')->delete($existingSection->image_url);
                }
                $updateData['image_url'] = $request->file('image_url')->store('product-page-hero-section', 'public');
            }

            if ($request->hasFile('background_image')) {
                if ($existingSection && $existingSection->background_image) {
                    Storage::disk('public')->delete($existingSection->background_image);
                }
                $updateData['background_image'] = $request->file('background_image')->store('product-page-hero-section', 'public');
            }

            if ($existingSection) {
                $existingSection->update($updateData);
                $section = $existingSection;
            } else {
                $section = ProductPageHeroSection::create($updateData);
            }

            $this->broadcastContentUpdate('product-page-hero-section', 'updated', ['id' => $section->id]);

            return response()->json([
                'success' => true,
                'message' => 'Product page hero section updated successfully',
                'data' => ['product_page_hero_section' => $section],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Update failed.', 'error' => $e->getMessage()], 500);
        }
    }
}
