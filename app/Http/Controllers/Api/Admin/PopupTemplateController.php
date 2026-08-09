<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopupTemplate;
use App\Services\PopupTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PopupTemplateController extends Controller
{
    protected PopupTemplateService $templateService;

    public function __construct(PopupTemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    public function overview()
    {
        try {
            $this->templateService->ensureDefaultTemplates();
            $templates = PopupTemplate::orderBy('category')->orderBy('name')->get();
            $events = PopupTemplateService::POPUP_EVENTS;
            return response()->json([
                'success' => true,
                'data' => [
                    'templates' => $templates,
                    'events' => $events,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch overview.'], 500);
        }
    }

    public function index()
    {
        try {
            $templates = PopupTemplate::orderBy('updated_at', 'desc')->get();
            return response()->json(['success' => true, 'data' => ['templates' => $templates]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch popup templates.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'event_key' => 'nullable|string|max:255|unique:popup_templates,event_key',
                'category' => 'sometimes|string|in:general,errors,orders,sales,wallet,rewards,security,support,promotions,onboarding,system',
                'heading' => 'nullable|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'body_text' => 'nullable|string',
                'body_html' => 'nullable|string',
                'footer_text' => 'nullable|string',
                'button_text' => 'nullable|string|max:255',
                'button_url' => 'nullable|string|max:1000',
                'button_style' => 'sometimes|string|in:primary,secondary,outline',
                'image_url' => 'nullable|string|max:1000',
                'logo_url' => 'nullable|string|max:1000',
                'logo_position' => 'sometimes|string|in:left,center,right,hidden',
                'popup_size' => 'sometimes|string|in:small,medium,large,full',
                'popup_position' => 'sometimes|string|in:center,top,bottom',
                'overlay_opacity' => 'sometimes|integer|min:0|max:100',
                'show_close_button' => 'sometimes|boolean',
                'auto_close_seconds' => 'nullable|integer|min:0|max:300',
                'background_color' => 'nullable|string|max:20',
                'text_color' => 'nullable|string|max:20',
                'status' => 'sometimes|string|in:draft,published',
                'variables' => 'nullable|array',
                'trigger_type' => 'sometimes|string|in:event,page',
                'trigger_pages' => 'nullable|array',
                'trigger_pages.*' => 'string|in:home,shop,cart,product,checkout,account,all',
                'display_frequency' => 'sometimes|string|in:every_time,once_per_session,once_per_day,once_ever',
            ]);
            
            $template = PopupTemplate::create($validated);
            return response()->json(['success' => true, 'message' => 'Popup template created.', 'data' => ['template' => $template]], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create popup template.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function show($id)
    {
        try {
            $template = PopupTemplate::findOrFail($id);
            return response()->json(['success' => true, 'data' => ['template' => $template]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Popup template not found.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $template = PopupTemplate::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'event_key' => 'nullable|string|max:255|unique:popup_templates,event_key,' . $id,
                'category' => 'sometimes|string|in:general,errors,orders,sales,wallet,rewards,security,support,promotions,onboarding,system',
                'heading' => 'nullable|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'body_text' => 'nullable|string',
                'body_html' => 'nullable|string',
                'footer_text' => 'nullable|string',
                'button_text' => 'nullable|string|max:255',
                'button_url' => 'nullable|string|max:1000',
                'button_style' => 'sometimes|string|in:primary,secondary,outline',
                'image_url' => 'nullable|string|max:1000',
                'logo_url' => 'nullable|string|max:1000',
                'logo_position' => 'sometimes|string|in:left,center,right,hidden',
                'popup_size' => 'sometimes|string|in:small,medium,large,full',
                'popup_position' => 'sometimes|string|in:center,top,bottom',
                'overlay_opacity' => 'sometimes|integer|min:0|max:100',
                'show_close_button' => 'sometimes|boolean',
                'auto_close_seconds' => 'nullable|integer|min:0|max:300',
                'background_color' => 'nullable|string|max:20',
                'text_color' => 'nullable|string|max:20',
                'status' => 'sometimes|string|in:draft,published',
                'variables' => 'nullable|array',
                'trigger_type' => 'sometimes|string|in:event,page',
                'trigger_pages' => 'nullable|array',
                'trigger_pages.*' => 'string|in:home,shop,cart,product,checkout,account,all',
                'display_frequency' => 'sometimes|string|in:every_time,once_per_session,once_per_day,once_ever',
            ]);
            
            $template->update($validated);
            return response()->json(['success' => true, 'message' => 'Popup template updated.', 'data' => ['template' => $template]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update popup template.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function restore($id)
    {
        try {
            $template = PopupTemplate::findOrFail($id);
            $template = $this->templateService->restoreTemplate($template);
            return response()->json(['success' => true, 'message' => 'Template restored to defaults.', 'data' => ['template' => $template]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to restore template.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,gif,svg,webp', 'max:5120'],
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('popup-templates', 'public');
                $url = Storage::disk('public')->url($path);
                
                return response()->json([
                    'success' => true,
                    'data' => ['url' => $url],
                ]);
            }
            return response()->json(['success' => false, 'message' => 'No image provided.'], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to upload image.', 'error' => config('app.debug') ? $e->getMessage() : null], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $template = PopupTemplate::findOrFail($id);
            if ($template->event_key && array_key_exists($template->event_key, PopupTemplateService::POPUP_EVENTS)) {
                return response()->json([
                    'success' => false,
                    'message' => 'System event templates cannot be deleted. You can disable or reset them instead.'
                ], 422);
            }
            $template->delete();
            return response()->json(['success' => true, 'message' => 'Popup template deleted.']);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete popup template.'], 500);
        }
    }
}
