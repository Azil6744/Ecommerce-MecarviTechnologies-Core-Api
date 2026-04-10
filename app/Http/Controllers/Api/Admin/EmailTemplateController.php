<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailTemplateController extends Controller
{
    public function index()
    {
        try {
            $templates = EmailTemplate::orderBy('updated_at', 'desc')->get();
            return response()->json(['success' => true, 'data' => ['templates' => $templates]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch templates.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'subject' => 'nullable|string|max:255',
                'category' => 'required|string|in:system,onboarding,orders,sales,promotional',
                'preview_text' => 'nullable|string',
                'body_html' => 'nullable|string',
                'status' => 'sometimes|string|in:draft,published',
                'variables' => 'nullable|array',
            ]);
            $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
            $template = EmailTemplate::create($validated);
            return response()->json(['success' => true, 'message' => 'Template created.', 'data' => ['template' => $template]], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create template.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function show($id)
    {
        try {
            $template = EmailTemplate::findOrFail($id);
            return response()->json(['success' => true, 'data' => ['template' => $template]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $template = EmailTemplate::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'subject' => 'nullable|string|max:255',
                'category' => 'sometimes|string|in:system,onboarding,orders,sales,promotional',
                'preview_text' => 'nullable|string',
                'body_html' => 'nullable|string',
                'status' => 'sometimes|string|in:draft,published',
                'variables' => 'nullable|array',
            ]);
            $template->update($validated);
            return response()->json(['success' => true, 'message' => 'Template updated.', 'data' => ['template' => $template]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update template.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function destroy($id)
    {
        try {
            EmailTemplate::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Template deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete template.'], 500);
        }
    }
}
