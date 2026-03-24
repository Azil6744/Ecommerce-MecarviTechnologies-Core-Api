<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionLabel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SectionLabelController extends Controller
{
    /**
     * Get all section labels (public – used by both admin and frontend).
     *
     * GET /api/v1/section-labels
     * Optional query: ?page_slug=home
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = SectionLabel::ordered();

        if ($request->has('page_slug')) {
            $query->forPage($request->input('page_slug'));
        }

        $labels = $query->get();

        // Group by page_slug for easier frontend consumption
        $grouped = $labels->groupBy('page_slug')->map(function ($pageLabels) {
            $result = [];
            foreach ($pageLabels as $label) {
                $result[$label->section_key] = $label->custom_label;
            }
            return $result;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'section_labels' => $grouped,
            ],
        ], 200);
    }

    /**
     * Get section labels for a specific page.
     *
     * GET /api/v1/section-labels/{pageSlug}
     *
     * @param string $pageSlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $pageSlug)
    {
        $labels = SectionLabel::forPage($pageSlug)->ordered()->get();

        $result = [];
        foreach ($labels as $label) {
            $result[$label->section_key] = $label->custom_label;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'page_slug' => $pageSlug,
                'labels' => $result,
            ],
        ], 200);
    }

    /**
     * Bulk upsert section labels for a page.
     *
     * POST /api/v1/section-labels
     * Body: { page_slug: "home", labels: { "Hero": "Banner", "About": "About Us", ... } }
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage section labels.',
                ], 403);
            }

            $validated = $request->validate([
                'page_slug' => ['required', 'string', 'max:100'],
                'labels' => ['required', 'array'],
                'labels.*' => ['required', 'string', 'max:255'],
            ]);

            $pageSlug = $validated['page_slug'];
            $labels = $validated['labels'];

            $upserted = [];
            $order = 0;

            foreach ($labels as $sectionKey => $customLabel) {
                $label = SectionLabel::updateOrCreate(
                    [
                        'page_slug' => $pageSlug,
                        'section_key' => $sectionKey,
                    ],
                    [
                        'custom_label' => $customLabel,
                        'sort_order' => $order,
                    ]
                );
                $upserted[$sectionKey] = $customLabel;
                $order++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Section labels saved successfully',
                'data' => [
                    'page_slug' => $pageSlug,
                    'labels' => $upserted,
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
                'message' => 'Failed to save section labels',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
