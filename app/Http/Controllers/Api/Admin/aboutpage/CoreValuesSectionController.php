<?php

namespace App\Http\Controllers\Api\Admin\aboutpage;

use App\Http\Controllers\Controller;
use App\Models\CoreValuesSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CoreValuesSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get core values section content (section title and description).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = CoreValuesSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'core_values_section' => null,
                    'message' => 'Core values section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'core_values_section' => [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'section_description' => $section->section_description,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Get core values section by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = CoreValuesSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Core values section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'core_values_section' => [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'section_description' => $section->section_description,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update core values section content.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage core values section content.',
                ], 403);
            }

            $validated = $request->validate([
                'section_title' => ['nullable', 'string', 'max:255'],
                'section_description' => ['nullable', 'string'],
            ]);

            $section = CoreValuesSection::updateOrCreate(
                ['id' => CoreValuesSection::first()?->id ?? 0],
                $validated
            );

            $this->broadcastContentUpdate('core-values-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Core values section updated successfully',
                'data' => [
                    'core_values_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'section_description' => $section->section_description,
                        'updated_at' => $section->updated_at,
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
                'message' => 'Core values section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during core values section update.',
            ], 500);
        }
    }

    /**
     * Update core values section content.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage core values section content.',
                ], 403);
            }

            $section = CoreValuesSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Core values section not found.',
                ], 404);
            }

            $dataToUpdate = [];

            if ($request->has('section_title')) {
                $dataToUpdate['section_title'] = $request->input('section_title');
            }
            if ($request->has('section_description')) {
                $dataToUpdate['section_description'] = $request->input('section_description');
            }

            if (!empty($dataToUpdate)) {
                $request->validate([
                    'section_title' => ['nullable', 'string', 'max:255'],
                    'section_description' => ['nullable', 'string'],
                ]);

                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                $this->broadcastContentUpdate('core-values-section', 'updated', [
                    'id' => $section->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Core values section updated successfully',
                'data' => [
                    'core_values_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'section_description' => $section->section_description,
                        'updated_at' => $section->updated_at,
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
                'message' => 'Core values section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during core values section update.',
            ], 500);
        }
    }
}
