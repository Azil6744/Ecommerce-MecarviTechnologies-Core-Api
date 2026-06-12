<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCardPageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GiftCardPageController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ['gift_card_page' => GiftCardPageContent::first()],
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            if (!$request->user()?->hasAdminAccess()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Only admins can manage gift card page content.'], 403);
            }

            $content = GiftCardPageContent::first();
            $validated = $this->handleUploads($request, $this->validatedPayload($request), $content);

            if ($content) {
                $content->fill($validated);
                $content->save();
            } else {
                $content = GiftCardPageContent::create($validated);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gift card page content saved successfully.',
                'data' => ['gift_card_page' => $content->fresh()],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gift card page content save failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!$request->user()?->hasAdminAccess()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Only admins can manage gift card page content.'], 403);
            }

            $content = GiftCardPageContent::find($id);
            if (!$content) {
                return response()->json(['success' => false, 'message' => 'Gift card page content not found.'], 404);
            }

            $validated = $this->handleUploads($request, $this->validatedPayload($request), $content);
            $content->fill($validated);
            $content->save();

            return response()->json([
                'success' => true,
                'message' => 'Gift card page content updated successfully.',
                'data' => ['gift_card_page' => $content->fresh()],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gift card page content update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    private function validatedPayload(Request $request): array
    {
        $jsonFields = ['backgrounds', 'header', 'hero', 'perks', 'card_types_section', 'card_types', 'design_showcase', 'how_it_works', 'redeem_band', 'faq_section', 'faqs', 'support_section', 'bottom_cta'];

        foreach ($jsonFields as $field) {
            $value = $request->input($field);
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $request->merge([$field => $decoded]);
                }
            }
        }

        return $request->validate([
            'backgrounds' => ['nullable', 'array'],
            'header' => ['nullable', 'array'],
            'hero' => ['nullable', 'array'],
            'perks' => ['nullable', 'array'],
            'card_types_section' => ['nullable', 'array'],
            'card_types' => ['nullable', 'array'],
            'design_showcase' => ['nullable', 'array'],
            'how_it_works' => ['nullable', 'array'],
            'redeem_band' => ['nullable', 'array'],
            'faq_section' => ['nullable', 'array'],
            'faqs' => ['nullable', 'array'],
            'support_section' => ['nullable', 'array'],
            'bottom_cta' => ['nullable', 'array'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            'design_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            'bottom_cta_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
        ]);
    }

    private function handleUploads(Request $request, array $validated, ?GiftCardPageContent $existing): array
    {
        if ($request->hasFile('hero_image')) {
            $this->deleteStoredImage($existing?->hero['image'] ?? null);
            $validated['hero'] = $validated['hero'] ?? [];
            $validated['hero']['image'] = $request->file('hero_image')->store('gift-card-page', 'public');
        }

        if ($request->hasFile('design_image')) {
            $this->deleteStoredImage($existing?->design_showcase['image'] ?? null);
            $validated['design_showcase'] = $validated['design_showcase'] ?? [];
            $validated['design_showcase']['image'] = $request->file('design_image')->store('gift-card-page', 'public');
        }

        if ($request->hasFile('bottom_cta_image')) {
            $this->deleteStoredImage($existing?->bottom_cta['image'] ?? null);
            $validated['bottom_cta'] = $validated['bottom_cta'] ?? [];
            $validated['bottom_cta']['image'] = $request->file('bottom_cta_image')->store('gift-card-page', 'public');
        }

        unset($validated['hero_image'], $validated['design_image'], $validated['bottom_cta_image']);

        return $validated;
    }

    private function deleteStoredImage(?string $path): void
    {
        if (!$path || str_starts_with($path, '/assets/') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalized = str_starts_with($path, '/storage/') ? substr($path, strlen('/storage/')) : ltrim($path, '/');
        Storage::disk('public')->delete($normalized);
    }
}
