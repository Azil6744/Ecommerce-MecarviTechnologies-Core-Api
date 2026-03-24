<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteFormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuoteFormFieldController extends Controller
{
    /**
     * Public: Get all active form fields grouped by section.
     */
    public function publicIndex()
    {
        $pageSlug = request('page', 'quote');

        $fields = QuoteFormField::where('is_active', true)
            ->where('page_slug', $pageSlug)
            ->orderBy('sort_order')
            ->get();

        // Group by section, preserving insertion order
        $sections = [];
        foreach ($fields as $field) {
            $sections[$field->section][] = [
                'id' => $field->id,
                'label' => $field->label,
                'name' => $field->name,
                'type' => $field->type,
                'options' => $field->options,
                'config' => $field->config,
                'is_required' => $field->is_required,
                'placeholder' => $field->placeholder,
                'grid_cols' => $field->grid_cols,
            ];
        }

        // Convert to ordered array of section objects
        $result = [];
        foreach ($sections as $sectionName => $sectionFields) {
            $result[] = [
                'section' => $sectionName,
                'fields' => $sectionFields,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Admin: List all form fields.
     */
    public function index()
    {
        $pageSlug = request('page');

        $fields = QuoteFormField::when($pageSlug, function ($query) use ($pageSlug) {
                $query->where('page_slug', $pageSlug);
            })
            ->orderBy('page_slug')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $fields,
        ]);
    }

    /**
     * Admin: Create a new form field.
     */
    public function store(Request $request)
    {
        try {
            $pageSlug = $request->input('page_slug', 'quote') ?: 'quote';

            $validated = $request->validate([
                'page_slug' => 'nullable|string|max:255',
                'section' => 'required|string|max:255',
                'label' => 'required|string|max:255',
                'name' => [
                    'required', 'string', 'max:255',
                    Rule::unique('quote_form_fields', 'name')->where('page_slug', $pageSlug),
                ],
                'type' => 'required|string|in:text,email,number,tel,select,radio,checkbox,textarea,file,image,image_upload,date,quantity,paragraph,card_select,image_choice,signature,captcha,repeater',
                'options' => 'nullable|array',
                'options.*' => 'nullable',
                'config' => 'nullable|array',
                'is_required' => 'boolean',
                'is_active' => 'boolean',
                'placeholder' => 'nullable|string|max:255',
                'grid_cols' => 'integer|in:1,2',
            ]);

            if (empty($validated['page_slug'])) {
                $validated['page_slug'] = 'quote';
            }

            // Auto-assign sort_order at end of section
            if (!$request->has('sort_order')) {
                $maxOrder = QuoteFormField::where('page_slug', $validated['page_slug'])
                    ->where('section', $validated['section'])
                    ->max('sort_order');
                $validated['sort_order'] = ($maxOrder ?? 0) + 1;
            } else {
                $validated['sort_order'] = $request->input('sort_order');
            }

            $field = QuoteFormField::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Form field created successfully',
                'data' => $field,
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
                'message' => 'Failed to create form field',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Admin: Update an existing form field.
     */
    public function update(Request $request, $id)
    {
        try {
            $field = QuoteFormField::findOrFail($id);

            $updatePageSlug = $request->input('page_slug', $field->page_slug) ?: $field->page_slug;

            $validated = $request->validate([
                'page_slug' => 'sometimes|nullable|string|max:255',
                'section' => 'sometimes|required|string|max:255',
                'label' => 'sometimes|required|string|max:255',
                'name' => [
                    'sometimes', 'required', 'string', 'max:255',
                    Rule::unique('quote_form_fields', 'name')->where('page_slug', $updatePageSlug)->ignore($id),
                ],
                'type' => 'sometimes|required|string|in:text,email,number,tel,select,radio,checkbox,textarea,file,image,image_upload,date,quantity,paragraph,card_select,image_choice,signature,captcha,repeater',
                'options' => 'nullable|array',
                'options.*' => 'nullable',
                'config' => 'nullable|array',
                'is_required' => 'boolean',
                'sort_order' => 'integer',
                'is_active' => 'boolean',
                'placeholder' => 'nullable|string|max:255',
                'grid_cols' => 'integer|in:1,2',
            ]);

            if (array_key_exists('page_slug', $validated) && empty($validated['page_slug'])) {
                $validated['page_slug'] = 'quote';
            }

            $field->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Form field updated successfully',
                'data' => $field->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update form field',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Admin: Delete a form field.
     */
    public function destroy($id)
    {
        try {
            $field = QuoteFormField::findOrFail($id);
            $field->delete();

            return response()->json([
                'success' => true,
                'message' => 'Form field deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete form field',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Admin: Bulk reorder fields.
     * Expects: { "fields": [{ "id": 1, "sort_order": 0 }, ...] }
     */
    public function reorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'fields' => 'required|array',
                'fields.*.id' => 'required|integer|exists:quote_form_fields,id',
                'fields.*.sort_order' => 'required|integer',
            ]);

            foreach ($validated['fields'] as $item) {
                QuoteFormField::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fields reordered successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder fields',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Admin: Upload a single image for image choice fields.
     */
    public function uploadImage(Request $request)
    {
        try {
            $validated = $request->validate([
                'image' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            ]);

            $path = $request->file('image')->store('quote-form-images', 'public');

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image upload failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }
}
