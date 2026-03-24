<?php

namespace App\Http\Controllers\Api\Admin\CareerPage;

use App\Http\Controllers\Controller;
use App\Models\JobSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class JobSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all job sections.
     * 
     * Returns all active job sections ordered by sort order.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $jobSections = JobSection::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'job_sections' => $jobSections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'section_description' => $section->section_description,
                        'title' => $section->title,
                        'description' => $section->description,
                        'employment_type' => $section->employment_type,
                        'experience_required' => $section->experience_required,
                        'company_name' => $section->company_name,
                        'image' => $section->image_url,
                        'is_active' => $section->is_active,
                        'sort_order' => $section->sort_order,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific job section.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = JobSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Job section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'job_section' => [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'section_description' => $section->section_description,
                    'title' => $section->title,
                    'description' => $section->description,
                    'employment_type' => $section->employment_type,
                    'experience_required' => $section->experience_required,
                    'company_name' => $section->company_name,
                    'image' => $section->image_url,
                    'is_active' => $section->is_active,
                    'sort_order' => $section->sort_order,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new job section.
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
                    'message' => 'Unauthorized. Only admins can manage job sections.',
                ], 403);
            }

            $validated = $request->validate([
                'section_title' => ['required', 'string', 'max:255'],
                'section_description' => ['nullable', 'string'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'employment_type' => ['nullable', 'string', 'max:100'],
                'experience_required' => ['nullable', 'string', 'max:100'],
                'company_name' => ['nullable', 'string', 'max:255'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('job-sections', 'public');
                $validated['image'] = $imagePath;
            } elseif ($request->has('image') && is_string($request->input('image'))) {
                $imageString = $request->input('image');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $imageString, $matches)) {
                    $imageType = $matches[1];
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageString));
                    
                    if ($imageData !== false) {
                        $filename = 'job_section_' . time() . '.' . $imageType;
                        $imagePath = 'job-sections/' . $filename;
                        
                        Storage::disk('public')->put($imagePath, $imageData);
                        $validated['image'] = $imagePath;
                    }
                }
            }

            // Set default values
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['sort_order'] = $validated['sort_order'] ?? 0;

            $jobSection = JobSection::create($validated);

            $this->broadcastContentUpdate('job-sections', 'created', [
                'id' => $jobSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job section created successfully',
                'data' => [
                    'job_section' => [
                        'id' => $jobSection->id,
                        'section_title' => $jobSection->section_title,
                        'section_description' => $jobSection->section_description,
                        'title' => $jobSection->title,
                        'description' => $jobSection->description,
                        'employment_type' => $jobSection->employment_type,
                        'experience_required' => $jobSection->experience_required,
                        'company_name' => $jobSection->company_name,
                        'image' => $jobSection->image_url,
                        'is_active' => $jobSection->is_active,
                        'sort_order' => $jobSection->sort_order,
                        'created_at' => $jobSection->created_at,
                        'updated_at' => $jobSection->updated_at,
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
                'message' => 'Failed to create job section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a job section.
     * 
     * @param \Illuminate\Http\Request $request
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
                    'message' => 'Unauthorized. Only admins can manage job sections.',
                ], 403);
            }

            $jobSection = JobSection::find($id);

            if (!$jobSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job section not found.',
                ], 404);
            }

            $validated = $request->validate([
                'section_title' => ['sometimes', 'required', 'string', 'max:255'],
                'section_description' => ['nullable', 'string'],
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'employment_type' => ['nullable', 'string', 'max:100'],
                'experience_required' => ['nullable', 'string', 'max:100'],
                'company_name' => ['nullable', 'string', 'max:255'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($jobSection->image) {
                    Storage::disk('public')->delete($jobSection->image);
                }
                
                $imagePath = $request->file('image')->store('job-sections', 'public');
                $validated['image'] = $imagePath;
            } elseif ($request->has('image') && is_string($request->input('image'))) {
                $imageString = $request->input('image');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $imageString, $matches)) {
                    $imageType = $matches[1];
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageString));
                    
                    if ($imageData !== false) {
                        // Delete old image if exists
                        if ($jobSection->image) {
                            Storage::disk('public')->delete($jobSection->image);
                        }
                        
                        $filename = 'job_section_' . time() . '.' . $imageType;
                        $imagePath = 'job-sections/' . $filename;
                        
                        Storage::disk('public')->put($imagePath, $imageData);
                        $validated['image'] = $imagePath;
                    }
                }
            }

            $jobSection->update($validated);

            $this->broadcastContentUpdate('job-sections', 'updated', [
                'id' => $jobSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job section updated successfully',
                'data' => [
                    'job_section' => [
                        'id' => $jobSection->id,
                        'section_title' => $jobSection->section_title,
                        'section_description' => $jobSection->section_description,
                        'title' => $jobSection->title,
                        'description' => $jobSection->description,
                        'employment_type' => $jobSection->employment_type,
                        'experience_required' => $jobSection->experience_required,
                        'company_name' => $jobSection->company_name,
                        'image' => $jobSection->image_url,
                        'is_active' => $jobSection->is_active,
                        'sort_order' => $jobSection->sort_order,
                        'created_at' => $jobSection->created_at,
                        'updated_at' => $jobSection->updated_at,
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
                'message' => 'Failed to update job section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific field from a job section.
     * 
     * @param int $id
     * @param string $field
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteField($id, $field)
    {
        try {
            $currentUser = request()->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage job sections.',
                ], 403);
            }

            $jobSection = JobSection::find($id);

            if (!$jobSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job section not found.',
                ], 404);
            }

            $allowedFields = ['image'];
            
            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field not allowed for deletion.',
                ], 400);
            }

            if ($field === 'image' && $jobSection->image) {
                Storage::disk('public')->delete($jobSection->image);
                $jobSection->image = null;
                $jobSection->save();
            }

            $this->broadcastContentUpdate('job-sections', 'updated', [
                'id' => $jobSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' deleted successfully",
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a job section.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $currentUser = request()->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage job sections.',
                ], 403);
            }

            $jobSection = JobSection::find($id);

            if (!$jobSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job section not found.',
                ], 404);
            }

            // Delete associated image if exists
            if ($jobSection->image) {
                Storage::disk('public')->delete($jobSection->image);
            }

            $jobSection->delete();

            $this->broadcastContentUpdate('job-sections', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job section deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete job section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}