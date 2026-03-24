<?php

namespace App\Http\Controllers\Api\Admin\sitesettings;

use App\Http\Controllers\Controller;
use App\Models\HeaderLink;
use App\Models\SiteSetting;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SiteSettingController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get site settings (Public).
     */
    public function index()
    {
        try {
            $settings = SiteSetting::first();
            $headerLinks = HeaderLink::ordered()->get();

            if (! $settings) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'site_settings' => $this->defaultStructure($headerLinks),
                    ],
                ], 200);
            }

            $data = [
                'seo_site_title' => $settings->seo_site_title,
                'seo_description' => $settings->seo_description,
                'seo_keywords' => $settings->seo_keywords,
                'logo' => $settings->logo,
                'logo_url' => $settings->logo_url,
                'favicon' => $settings->favicon,
                'favicon_url' => $settings->favicon_url,
                'button' => [
                    'name' => $settings->button_name,
                    'url' => $settings->button_url,
                ],
                'theme_primary_color' => $settings->theme_primary_color ?? '#ff6c00',
                'theme_secondary_color' => $settings->theme_secondary_color ?? '#ff00a7',
                'header_links' => $headerLinks->map(fn ($link) => [
                    'id' => $link->id,
                    'label' => $link->label,
                    'url' => $link->url,
                    'show_in_header' => $link->show_in_header,
                    'sort_order' => $link->sort_order,
                ]),
            ];

            return response()->json([
                'success' => true,
                'data' => ['site_settings' => $data],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch site settings',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Save site settings (Admin only). Create or update main row and replace header_links when provided.
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();
            if (! $currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage site settings.',
                ], 403);
            }

            $this->normalizeBooleanInput($request, 'header_links.*.show_in_header');

            $rules = [
                'seo_site_title' => ['nullable', 'string', 'max:255'],
                'seo_description' => ['nullable', 'string', 'max:1000'],
                'seo_keywords' => ['nullable', 'string', 'max:1000'],
                'button_name' => ['nullable', 'string', 'max:255'],
                'button_url' => ['nullable', 'string', 'max:500'],
                'button' => ['sometimes', 'array'],
                'button.name' => ['nullable', 'string', 'max:255'],
                'button.url' => ['nullable', 'string', 'max:500'],
                'theme_primary_color' => ['nullable', 'string', 'max:50'],
                'theme_secondary_color' => ['nullable', 'string', 'max:50'],
                'header_links' => ['sometimes', 'array'],
                'header_links.*.label' => ['required_with:header_links', 'string', 'max:255'],
                'header_links.*.url' => ['required_with:header_links', 'string', 'max:500'],
                'header_links.*.show_in_header' => ['sometimes', 'boolean'],
                'header_links.*.sort_order' => ['sometimes', 'integer', 'min:0'],
            ];
            if ($request->hasFile('logo')) {
                $rules['logo'] = ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } elseif ($request->hasFile('logo_file')) {
                $rules['logo_file'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } else {
                $rules['logo'] = ['nullable', 'string', 'max:2100000'];
            }
            if ($request->hasFile('favicon')) {
                $rules['favicon'] = ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } elseif ($request->hasFile('favicon_file')) {
                $rules['favicon_file'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } else {
                $rules['favicon'] = ['nullable', 'string', 'max:2100000'];
            }
            $request->validate($rules);

            $settings = SiteSetting::first();
            if (! $settings) {
                $settings = new SiteSetting;
            }

            if ($request->has('seo_site_title')) {
                $settings->seo_site_title = $request->input('seo_site_title');
            }
            if ($request->has('seo_description')) {
                $settings->seo_description = $request->input('seo_description');
            }
            if ($request->has('seo_keywords')) {
                $settings->seo_keywords = $request->input('seo_keywords');
            }
            if ($request->has('theme_primary_color')) {
                $settings->theme_primary_color = $request->input('theme_primary_color');
            }
            if ($request->has('theme_secondary_color')) {
                $settings->theme_secondary_color = $request->input('theme_secondary_color');
            }
            if ($request->has('logo') && ! $request->hasFile('logo') && is_string($request->input('logo'))) {
                $settings->logo = $request->input('logo') ?: null;
            }
            if ($request->has('favicon') && ! $request->hasFile('favicon') && is_string($request->input('favicon'))) {
                $settings->favicon = $request->input('favicon') ?: null;
            }
            $this->handleLogoUpload($request, $settings);
            $this->handleFaviconUpload($request, $settings);
            $this->applyButtonFromRequest($request, $settings);
            $settings->save();

            if ($request->has('header_links')) {
                HeaderLink::query()->delete();
                foreach ($request->input('header_links', []) as $index => $item) {
                    HeaderLink::create([
                        'label' => $item['label'] ?? '',
                        'url' => $item['url'] ?? '',
                        'show_in_header' => isset($item['show_in_header']) ? (bool) $item['show_in_header'] : true,
                        'sort_order' => $item['sort_order'] ?? $index,
                    ]);
                }
            }

            $this->broadcastContentUpdate('site-settings', 'updated', []);

            return response()->json([
                'success' => true,
                'message' => 'Site settings saved successfully',
                'data' => ['site_settings' => $this->buildSettingsData()],
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
                'message' => 'Failed to save site settings',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Update site settings by ID (Admin only).
     */
    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            if (! $currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage site settings.',
                ], 403);
            }

            $settings = SiteSetting::find($id);
            if (! $settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Site settings not found.',
                ], 404);
            }

            $this->normalizeBooleanInput($request, 'header_links.*.show_in_header');

            $rules = [
                'seo_site_title' => ['sometimes', 'nullable', 'string', 'max:255'],
                'seo_description' => ['sometimes', 'nullable', 'string', 'max:1000'],
                'seo_keywords' => ['sometimes', 'nullable', 'string', 'max:1000'],
                'button_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'button_url' => ['sometimes', 'nullable', 'string', 'max:500'],
                'button' => ['sometimes', 'array'],
                'button.name' => ['nullable', 'string', 'max:255'],
                'button.url' => ['nullable', 'string', 'max:500'],
                'theme_primary_color' => ['sometimes', 'nullable', 'string', 'max:50'],
                'theme_secondary_color' => ['sometimes', 'nullable', 'string', 'max:50'],
                'header_links' => ['sometimes', 'array'],
                'header_links.*.label' => ['required_with:header_links', 'string', 'max:255'],
                'header_links.*.url' => ['required_with:header_links', 'string', 'max:500'],
                'header_links.*.show_in_header' => ['sometimes', 'boolean'],
                'header_links.*.sort_order' => ['sometimes', 'integer', 'min:0'],
            ];
            if ($request->hasFile('logo')) {
                $rules['logo'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } elseif ($request->hasFile('logo_file')) {
                $rules['logo_file'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } else {
                $rules['logo'] = ['sometimes', 'nullable', 'string', 'max:2100000'];
            }
            if ($request->hasFile('favicon')) {
                $rules['favicon'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } elseif ($request->hasFile('favicon_file')) {
                $rules['favicon_file'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } else {
                $rules['favicon'] = ['sometimes', 'nullable', 'string', 'max:2100000'];
            }
            $request->validate($rules);

            if ($request->has('seo_site_title')) {
                $settings->seo_site_title = $request->input('seo_site_title');
            }
            if ($request->has('seo_description')) {
                $settings->seo_description = $request->input('seo_description');
            }
            if ($request->has('seo_keywords')) {
                $settings->seo_keywords = $request->input('seo_keywords');
            }
            if ($request->has('theme_primary_color')) {
                $settings->theme_primary_color = $request->input('theme_primary_color');
            }
            if ($request->has('theme_secondary_color')) {
                $settings->theme_secondary_color = $request->input('theme_secondary_color');
            }
            if ($request->has('logo') && ! $request->hasFile('logo') && is_string($request->input('logo'))) {
                $settings->logo = $request->input('logo') ?: null;
            }
            if ($request->has('favicon') && ! $request->hasFile('favicon') && is_string($request->input('favicon'))) {
                $settings->favicon = $request->input('favicon') ?: null;
            }
            $this->applyButtonFromRequest($request, $settings);

            $this->handleLogoUpload($request, $settings);
            $this->handleFaviconUpload($request, $settings);
            $this->applyButtonFromRequest($request, $settings);
            $settings->save();

            if ($request->has('header_links')) {
                HeaderLink::query()->delete();
                foreach ($request->input('header_links', []) as $index => $item) {
                    HeaderLink::create([
                        'label' => $item['label'] ?? '',
                        'url' => $item['url'] ?? '',
                        'show_in_header' => isset($item['show_in_header']) ? (bool) $item['show_in_header'] : true,
                        'sort_order' => $item['sort_order'] ?? $index,
                    ]);
                }
            }

            $this->broadcastContentUpdate('site-settings', 'updated', ['id' => $settings->id]);

            return response()->json([
                'success' => true,
                'message' => 'Site settings updated successfully',
                'data' => ['site_settings' => $this->buildSettingsData()],
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
                'message' => 'Failed to update site settings',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Delete site settings and all header links (Admin only).
     */
    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            if (! $currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete site settings.',
                ], 403);
            }

            $settings = SiteSetting::find($id);
            if (! $settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Site settings not found.',
                ], 404);
            }

            if ($settings->logo && ! str_starts_with($settings->logo, 'http') && ! str_starts_with($settings->logo, '/')) {
                Storage::disk('public')->delete($settings->logo);
            }
            if ($settings->favicon && ! str_starts_with($settings->favicon, 'http') && ! str_starts_with($settings->favicon, '/')) {
                Storage::disk('public')->delete($settings->favicon);
            }
            HeaderLink::query()->delete();
            $settings->delete();

            $this->broadcastContentUpdate('site-settings', 'deleted', ['id' => (int) $id]);

            return response()->json([
                'success' => true,
                'message' => 'Site settings deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete site settings',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Delete a single field (set to null). Admin only.
     */
    public function deleteField(Request $request, $id, $field)
    {
        try {
            $currentUser = $request->user();
            if (! $currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete site setting fields.',
                ], 403);
            }

            $settings = SiteSetting::find($id);
            if (! $settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Site settings not found.',
                ], 404);
            }

            $allowedFields = ['seo_site_title', 'seo_description', 'seo_keywords', 'logo', 'favicon', 'button_name', 'button_url'];
            if (! in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed: ' . implode(', ', $allowedFields),
                ], 422);
            }

            if ($field === 'logo' && $settings->logo && ! str_starts_with($settings->logo, 'http') && ! str_starts_with($settings->logo, '/')) {
                Storage::disk('public')->delete($settings->logo);
            }
            if ($field === 'favicon' && $settings->favicon && ! str_starts_with($settings->favicon, 'http') && ! str_starts_with($settings->favicon, '/')) {
                Storage::disk('public')->delete($settings->favicon);
            }
            $settings->$field = null;
            $settings->save();
            $settings->refresh();

            $this->broadcastContentUpdate('site-settings', 'updated', ['id' => $settings->id, 'field' => $field]);

            return response()->json([
                'success' => true,
                'message' => 'Field deleted successfully',
                'data' => ['site_settings' => $this->buildSettingsData()],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Delete a single header link (Admin only).
     */
    public function destroyLink(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            if (! $currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete header links.',
                ], 403);
            }

            $link = HeaderLink::find($id);
            if (! $link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Header link not found.',
                ], 404);
            }

            $link->delete();
            $this->broadcastContentUpdate('site-settings', 'updated', ['link_id' => (int) $id]);

            return response()->json([
                'success' => true,
                'message' => 'Header link deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete header link',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    private function applyButtonFromRequest(Request $request, SiteSetting $settings): void
    {
        $name = null;
        $url = null;
        $nameProvided = false;
        $urlProvided = false;

        if ($request->has('button_name')) {
            $name = $request->input('button_name') ?: null;
            $nameProvided = true;
        }
        if ($request->has('button_url')) {
            $url = $request->input('button_url') ?: null;
            $urlProvided = true;
        }
        $btn = $request->input('button');
        if (is_string($btn)) {
            $decoded = json_decode($btn, true);
            $btn = is_array($decoded) ? $decoded : null;
        }
        if (is_array($btn)) {
            if (array_key_exists('name', $btn)) {
                $name = $btn['name'] ?: null;
                $nameProvided = true;
            }
            if (array_key_exists('url', $btn)) {
                $url = $btn['url'] ?: null;
                $urlProvided = true;
            }
        }
        if (! $nameProvided || ! $urlProvided) {
            $btn = $request->input('site_settings.button');
            if (is_string($btn)) {
                $decoded = json_decode($btn, true);
                $btn = is_array($decoded) ? $decoded : null;
            }
            if (is_array($btn)) {
                if (array_key_exists('name', $btn)) {
                    $name = $btn['name'] ?: null;
                    $nameProvided = true;
                }
                if (array_key_exists('url', $btn)) {
                    $url = $btn['url'] ?: null;
                    $urlProvided = true;
                }
            }
        }
        if ($request->has('button.name')) {
            $name = $request->input('button.name') ?: null;
            $nameProvided = true;
        }
        if ($request->has('button.url')) {
            $url = $request->input('button.url') ?: null;
            $urlProvided = true;
        }

        if ($nameProvided || $urlProvided) {
            $settings->setAttribute('button_name', $name);
            $settings->setAttribute('button_url', $url);
        }
    }

    private function handleLogoUpload(Request $request, SiteSetting $settings): void
    {
        $file = $request->hasFile('logo') ? $request->file('logo') : ($request->hasFile('logo_file') ? $request->file('logo_file') : null);
        if ($file) {
            if ($settings->logo && ! str_starts_with($settings->logo, 'http') && ! str_starts_with($settings->logo, '/')) {
                Storage::disk('public')->delete($settings->logo);
            }
            $path = $file->store('site-settings', 'public');
            $settings->logo = $path;
            $settings->save();
            return;
        }

        if ($request->has('logo') && is_string($request->input('logo'))) {
            $logoInput = $request->input('logo');
            if (preg_match('/^data:image\/([\w+]+);base64,/', $logoInput, $matches)) {
                if ($settings->logo && ! str_starts_with($settings->logo, 'http') && ! str_starts_with($settings->logo, '/')) {
                    Storage::disk('public')->delete($settings->logo);
                }
                $imageType = str_replace('+xml', '', $matches[1]);
                $imageData = base64_decode(preg_replace('/^data:image\/[\w+]+;base64,/', '', $logoInput));
                if ($imageData !== false) {
                    $filename = 'logo_' . time() . '.' . $imageType;
                    $path = 'site-settings/' . $filename;
                    Storage::disk('public')->put($path, $imageData);
                    $settings->logo = $path;
                    $settings->save();
                }
            }
        }
    }

    private function handleFaviconUpload(Request $request, SiteSetting $settings): void
    {
        $file = $request->hasFile('favicon') ? $request->file('favicon') : ($request->hasFile('favicon_file') ? $request->file('favicon_file') : null);
        if ($file) {
            if ($settings->favicon && ! str_starts_with($settings->favicon, 'http') && ! str_starts_with($settings->favicon, '/')) {
                Storage::disk('public')->delete($settings->favicon);
            }
            $path = $file->store('site-settings', 'public');
            $settings->favicon = $path;
            $settings->save();
            return;
        }

        if ($request->has('favicon') && is_string($request->input('favicon'))) {
            $faviconInput = $request->input('favicon');
            if (preg_match('/^data:image\/([\w+]+);base64,/', $faviconInput, $matches)) {
                if ($settings->favicon && ! str_starts_with($settings->favicon, 'http') && ! str_starts_with($settings->favicon, '/')) {
                    Storage::disk('public')->delete($settings->favicon);
                }
                $imageType = str_replace('+xml', '', $matches[1]);
                $imageData = base64_decode(preg_replace('/^data:image\/[\w+]+;base64,/', '', $faviconInput));
                if ($imageData !== false) {
                    $filename = 'favicon_' . time() . '.' . $imageType;
                    $path = 'site-settings/' . $filename;
                    Storage::disk('public')->put($path, $imageData);
                    $settings->favicon = $path;
                    $settings->save();
                }
            }
        }
    }

    private function normalizeBooleanInput(Request $request, string $field): void
    {
        if (! $request->has($field)) {
            return;
        }
        $value = $request->input($field);
        if (is_bool($value)) {
            return;
        }
        if (is_string($value)) {
            $v = strtolower(trim($value));
            if (in_array($v, ['true', '1', 'yes', 'on'])) {
                $request->merge([$field => true]);
            } elseif (in_array($v, ['false', '0', 'no', 'off', ''])) {
                $request->merge([$field => false]);
            }
        } elseif (is_numeric($value)) {
            $request->merge([$field => (bool) $value]);
        }
        if (str_contains($field, '*')) {
            $key = str_before($field, '.*');
            $sub = str_after($field, '.*');
            $items = $request->input($key, []);
            foreach ($items as $i => $item) {
                if (isset($item[$sub])) {
                    $val = $item[$sub];
                    if (is_string($val)) {
                        $v = strtolower(trim($val));
                        if (in_array($v, ['true', '1', 'yes', 'on'])) {
                            $items[$i][$sub] = true;
                        } elseif (in_array($v, ['false', '0', 'no', 'off', ''])) {
                            $items[$i][$sub] = false;
                        }
                    }
                }
            }
            $request->merge([$key => $items]);
        }
    }

    private function buildSettingsData(): array
    {
        $settings = SiteSetting::first();
        $headerLinks = HeaderLink::ordered()->get();

        if (! $settings) {
            return $this->defaultStructure($headerLinks);
        }

        return [
            'seo_site_title' => $settings->seo_site_title,
            'seo_description' => $settings->seo_description,
            'seo_keywords' => $settings->seo_keywords,
            'logo' => $settings->logo,
            'logo_url' => $settings->logo_url,
            'favicon' => $settings->favicon,
            'favicon_url' => $settings->favicon_url,
            'button' => [
                'name' => $settings->button_name,
                'url' => $settings->button_url,
            ],
            'theme_primary_color' => $settings->theme_primary_color ?? '#ff6c00',
            'theme_secondary_color' => $settings->theme_secondary_color ?? '#ff00a7',
            'header_links' => $headerLinks->map(fn ($link) => [
                'id' => $link->id,
                'label' => $link->label,
                'url' => $link->url,
                'show_in_header' => $link->show_in_header,
                'sort_order' => $link->sort_order,
            ]),
        ];
    }

    private function defaultStructure($headerLinks = null): array
    {
        $links = $headerLinks ? $headerLinks->map(fn ($link) => [
            'id' => $link->id,
            'label' => $link->label,
            'url' => $link->url,
            'show_in_header' => $link->show_in_header,
            'sort_order' => $link->sort_order,
        ]) : [];

        return [
            'seo_site_title' => null,
            'seo_description' => null,
            'seo_keywords' => null,
            'logo' => null,
            'logo_url' => null,
            'favicon' => null,
            'favicon_url' => null,
            'button' => ['name' => null, 'url' => null],
            'theme_primary_color' => '#ff6c00',
            'theme_secondary_color' => '#ff00a7',
            'header_links' => $links,
        ];
    }
}
