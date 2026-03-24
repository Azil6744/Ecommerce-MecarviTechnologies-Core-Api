<?php

namespace App\Http\Controllers\Api\Admin\servicepage;

use App\Http\Controllers\Controller;
use App\Models\ServiceHowItWorksSection;
use App\Models\ServiceHowItWorksItem;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceHowItWorksSectionController extends Controller
{
    use BroadcastsContentUpdates;

    public function index()
    {
        $section = ServiceHowItWorksSection::with(['items' => function ($query) {
            $query->orderBy('order');
        }])->first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'service_how_it_works_section' => null,
                    'message' => 'Service "How It Works" section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_how_it_works_section' => $section,
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
                'title' => ['nullable', 'string', 'max:255'],
                'short_description' => ['nullable', 'string'],
                'full_description' => ['nullable', 'string'],
                'background_image_url' => ['nullable', 'string'],
                'items' => ['nullable', 'array'],
                'items.*.title' => ['nullable', 'string', 'max:255'],
                'items.*.short_description' => ['nullable', 'string'],
                'items.*.full_description' => ['nullable', 'string'],
                'items.*.image_url' => ['nullable', 'string'],
                'items.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'items.*.order' => ['nullable', 'integer'],
            ]);

            $section = ServiceHowItWorksSection::updateOrCreate(
                ['id' => ServiceHowItWorksSection::first()?->id ?? 0],
                collect($validated)->except(['items'])->toArray()
            );

            if (array_key_exists('items', $validated)) {
                $section->items()->delete();
                $itemsInput = $request->input('items', []);
                $items = [];

                foreach ($itemsInput as $index => $item) {
                    $imagePath = null;
                    if ($request->hasFile("items.$index.image")) {
                        $imagePath = $request->file("items.$index.image")->store('service-how-it-works', 'public');
                    }

                    $items[] = [
                        'section_id' => $section->id,
                        'title' => $item['title'] ?? null,
                        'short_description' => $item['short_description'] ?? null,
                        'full_description' => $item['full_description'] ?? null,
                        'image_url' => $imagePath ?? $this->normalizeImagePath($item['image_url'] ?? null),
                        'order' => $item['order'] ?? $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($items)) {
                    ServiceHowItWorksItem::insert($items);
                }
            }

            $this->broadcastContentUpdate('service-how-it-works-section', 'updated', ['id' => $section->id]);

            return response()->json([
                'success' => true,
                'message' => 'Service "How It Works" section updated successfully',
                'data' => ['service_how_it_works_section' => $section->load('items')],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Update failed.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Normalize an image URL/path back to a relative storage path for DB storage.
     * The model accessor will convert it back to a full URL on read.
     */
    private function normalizeImagePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // If it's already a relative path (e.g. "service-how-it-works/xxx.jpg"), keep as-is
        if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
            // Strip leading /storage/ if present
            if (str_starts_with($path, '/storage/')) {
                return ltrim(substr($path, 9), '/');
            }
            return $path;
        }

        // If it's a full URL pointing to our storage, extract the relative path
        $storagePath = '/storage/';
        $pos = strpos($path, $storagePath);
        if ($pos !== false) {
            return substr($path, $pos + strlen($storagePath));
        }

        // External URL — keep as-is
        return $path;
    }
}
