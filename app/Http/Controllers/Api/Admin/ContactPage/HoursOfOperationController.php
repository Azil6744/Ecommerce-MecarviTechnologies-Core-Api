<?php

namespace App\Http\Controllers\Api\Admin\ContactPage;

use App\Http\Controllers\Controller;
use App\Models\HoursOfOperation;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HoursOfOperationController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all hours of operation (Public endpoint)
     */
    public function index(Request $request)
    {
        try {
            $query = HoursOfOperation::query();

            // Filter by active status if provided
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            } else {
                // By default, show only active for public access
                $query->active();
            }

            $hours = $query->ordered()->get();

            // Get section-level data from first record (if exists)
            $sectionTitle = $hours->first()?->section_title;
            $backgroundImage = $hours->first()?->background_image;

            return response()->json([
                'success' => true,
                'data' => [
                    'section_title' => $sectionTitle,
                    'background_image' => $backgroundImage,
                    'hours_of_operation' => $hours->map(function ($hour) {
                        return $this->formatHours($hour);
                    }),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hours of operation',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Get specific hours of operation (Public endpoint)
     */
    public function show($id)
    {
        try {
            $hours = HoursOfOperation::find($id);

            if (!$hours) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hours of operation not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'hours_of_operation' => $this->formatHours($hours),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hours of operation',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Create a new hours of operation (Admin only)
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage hours of operation.',
                ], 403);
            }

            // Normalize boolean values before validation
            $this->normalizeBooleanInput($request, 'is_active');

            $validated = $this->validateHoursData($request);

            // Handle background image upload
            if ($request->hasFile('background_image')) {
                $path = $request->file('background_image')->store('hours-of-operation', 'public');
                $validated['background_image'] = '/storage/' . $path;
            }

            $hours = HoursOfOperation::create($validated);

            $this->broadcastContentUpdate('hours-of-operation', 'created', [
                'id' => $hours->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hours of operation created successfully',
                'data' => [
                    'hours_of_operation' => $this->formatHours($hours),
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
                'message' => 'Hours of operation creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during hours of operation creation.',
            ], 500);
        }
    }

    /**
     * Update hours of operation (Admin only)
     */
    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage hours of operation.',
                ], 403);
            }

            $hours = HoursOfOperation::find($id);

            if (!$hours) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hours of operation not found.',
                ], 404);
            }

            // Normalize boolean values before validation
            $this->normalizeBooleanInput($request, 'is_active');

            $validated = $this->validateHoursData($request, true);

            // Handle background image upload
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($hours->background_image) {
                    $oldPath = str_replace('/storage/', '', $hours->background_image);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('background_image')->store('hours-of-operation', 'public');
                $validated['background_image'] = '/storage/' . $path;
            }

            $hours->update($validated);

            $this->broadcastContentUpdate('hours-of-operation', 'updated', [
                'id' => $hours->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hours of operation updated successfully',
                'data' => [
                    'hours_of_operation' => $this->formatHours($hours),
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
                'message' => 'Hours of operation update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during hours of operation update.',
            ], 500);
        }
    }

    /**
     * Delete hours of operation (Admin only)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete hours of operation.',
                ], 403);
            }

            $hours = HoursOfOperation::find($id);

            if (!$hours) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hours of operation not found.',
                ], 404);
            }

            $hoursId = $hours->id;
            $hours->delete();

            $this->broadcastContentUpdate('hours-of-operation', 'deleted', [
                'id' => $hoursId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hours of operation deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hours of operation deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during hours of operation deletion.',
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
     * Validate hours of operation data
     */
    private function validateHoursData(Request $request, $isUpdate = false)
    {
        $rules = [
            'section_title' => ['nullable', 'string', 'max:255'],
            'category_title' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'monday_friday_hours' => ['nullable', 'string', 'max:255'],
            'saturday_hours' => ['nullable', 'string', 'max:255'],
            'sunday_hours' => ['nullable', 'string', 'max:255'],
            'public_holidays_hours' => ['nullable', 'string', 'max:255'],
            'description_1' => ['nullable', 'string'],
            'description_2' => ['nullable', 'string'],
            'background_image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['sometimes', 'boolean', 'nullable'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];

        return $request->validate($rules);
    }

    /**
     * Format hours of operation data for response
     */
    private function formatHours(HoursOfOperation $hours)
    {
        return [
            'id' => $hours->id,
            'section_title' => $hours->section_title,
            'category_title' => $hours->category_title,
            'monday_friday_hours' => $hours->monday_friday_hours,
            'saturday_hours' => $hours->saturday_hours,
            'sunday_hours' => $hours->sunday_hours,
            'public_holidays_hours' => $hours->public_holidays_hours,
            'description_1' => $hours->description_1,
            'description_2' => $hours->description_2,
            'background_image' => $hours->background_image,
            'is_active' => $hours->is_active,
            'sort_order' => $hours->sort_order,
            'created_at' => $hours->created_at,
            'updated_at' => $hours->updated_at,
        ];
    }
}
