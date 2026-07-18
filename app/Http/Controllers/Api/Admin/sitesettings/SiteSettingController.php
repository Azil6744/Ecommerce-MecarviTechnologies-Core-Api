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
                'site_name' => $settings->site_name,
                'seo_site_title' => $settings->seo_site_title,
                'seo_description' => $settings->seo_description,
                'seo_keywords' => $settings->seo_keywords,
                'logo' => $settings->logo,
                'logo_url' => $settings->logo_url,
                'login_logo' => $settings->login_logo,
                'login_logo_url' => $settings->login_logo_url,
                'favicon' => $settings->favicon,
                'favicon_url' => $settings->favicon_url,
                'business_panel_logo' => $settings->business_panel_logo,
                'business_panel_logo_url' => $settings->business_panel_logo_url,
                'user_panel_logo' => $settings->user_panel_logo,
                'user_panel_logo_url' => $settings->user_panel_logo_url,
                'email_template_logo' => $settings->email_template_logo,
                'email_template_logo_url' => $settings->email_template_logo_url,
                'button' => [
                    'name' => $settings->button_name,
                    'url' => $settings->button_url,
                ],
                'theme_primary_color' => $settings->theme_primary_color ?? '#ff6c00',
                'theme_secondary_color' => $settings->theme_secondary_color ?? '#ff00a7',
                'tax_rate' => (float)($settings->tax_rate ?? 10.00),
                'tax_enabled' => (bool)$settings->tax_enabled,
                'loyalty_points_earned_per_unit_price' => (float)($settings->loyalty_points_earned_per_unit_price ?? 50.00),
                'loyalty_points_earned_points' => (int)($settings->loyalty_points_earned_points ?? 2),
                'charity_name' => $settings->charity_name ?? 'Red Cross',
                'charity_donation_enabled' => (bool)$settings->charity_donation_enabled,
                'charity_default_amount' => (float)($settings->charity_default_amount ?? 1.00),
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
                'site_name' => ['nullable', 'string', 'max:255'],
                'seo_site_title' => ['nullable', 'string', 'max:255'],
                'seo_description' => ['nullable', 'string', 'max:1000'],
                'seo_keywords' => ['nullable', 'string', 'max:3000'],
                'button_name' => ['nullable', 'string', 'max:255'],
                'button_url' => ['nullable', 'string', 'max:500'],
                'button' => ['sometimes', 'array'],
                'button.name' => ['nullable', 'string', 'max:255'],
                'button.url' => ['nullable', 'string', 'max:500'],
                'theme_primary_color' => ['nullable', 'string', 'max:50'],
                'theme_secondary_color' => ['nullable', 'string', 'max:50'],
                'confirmation_message' => ['nullable', 'string'],
                'default_message' => ['nullable', 'string'],
                'loader_type' => ['nullable', 'string', 'max:50'],
                'loader_color' => ['nullable', 'string', 'max:50'],
                'maintenance_mode' => ['sometimes', 'boolean'],
                'maintenance_message' => ['nullable', 'string'],
                'contact_us_email' => ['nullable', 'email', 'max:255'],
                'contact_us_phone' => ['nullable', 'string', 'max:50'],
                'contact_us_address' => ['nullable', 'string'],
                'header_links' => ['sometimes', 'array'],
                'header_links.*.label' => ['required_with:header_links', 'string', 'max:255'],
                'header_links.*.url' => ['required_with:header_links', 'string', 'max:500'],
                'header_links.*.show_in_header' => ['sometimes', 'boolean'],
                'header_links.*.sort_order' => ['sometimes', 'integer', 'min:0'],
                'tax_rate' => ['nullable', 'numeric', 'min:0'],
                'tax_enabled' => ['sometimes', 'boolean'],
                'loyalty_points_earned_per_unit_price' => ['nullable', 'numeric', 'min:0'],
                'loyalty_points_earned_points' => ['nullable', 'integer', 'min:0'],
                'charity_name' => ['nullable', 'string', 'max:255'],
                'charity_donation_enabled' => ['sometimes', 'boolean'],
                'charity_default_amount' => ['nullable', 'numeric', 'min:0'],
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
            if ($request->hasFile('login_logo')) {
                $rules['login_logo'] = ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } elseif ($request->hasFile('login_logo_file')) {
                $rules['login_logo_file'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } else {
                $rules['login_logo'] = ['nullable', 'string', 'max:2100000'];
            }

            if ($request->hasFile('business_panel_logo')) {
                $rules['business_panel_logo'] = ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } elseif ($request->hasFile('business_panel_logo_file')) {
                $rules['business_panel_logo_file'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } else {
                $rules['business_panel_logo'] = ['nullable', 'string', 'max:2100000'];
            }

            if ($request->hasFile('user_panel_logo')) {
                $rules['user_panel_logo'] = ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } elseif ($request->hasFile('user_panel_logo_file')) {
                $rules['user_panel_logo_file'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } else {
                $rules['user_panel_logo'] = ['nullable', 'string', 'max:2100000'];
            }

            if ($request->hasFile('email_template_logo')) {
                $rules['email_template_logo'] = ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } elseif ($request->hasFile('email_template_logo_file')) {
                $rules['email_template_logo_file'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } else {
                $rules['email_template_logo'] = ['nullable', 'string', 'max:2100000'];
            }
            $request->validate($rules);

            $settings = SiteSetting::first();
            if (! $settings) {
                $settings = new SiteSetting;
            }

            if ($request->has('seo_site_title')) {
                $settings->seo_site_title = $request->input('seo_site_title');
            }
            if ($request->has('site_name')) {
                $settings->site_name = $request->input('site_name');
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
            
            $generalFields = [
                'confirmation_message', 'default_message', 'loader_type', 'loader_color',
                'maintenance_message', 'contact_us_email', 'contact_us_phone', 'contact_us_address',
                'tax_rate', 'loyalty_points_earned_per_unit_price', 'loyalty_points_earned_points',
                'charity_name', 'charity_default_amount'
            ];
            foreach ($generalFields as $field) {
                if ($request->has($field)) {
                    $settings->$field = $request->input($field);
                }
            }
            if ($request->has('maintenance_mode')) {
                $settings->maintenance_mode = filter_var($request->input('maintenance_mode'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('tax_enabled')) {
                $settings->tax_enabled = filter_var($request->input('tax_enabled'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('charity_donation_enabled')) {
                $settings->charity_donation_enabled = filter_var($request->input('charity_donation_enabled'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('logo') && ! $request->hasFile('logo') && is_string($request->input('logo'))) {
                $settings->logo = $request->input('logo') ?: null;
            }
            if ($request->has('favicon') && ! $request->hasFile('favicon') && is_string($request->input('favicon'))) {
                $settings->favicon = $request->input('favicon') ?: null;
            }
            if ($request->has('login_logo') && ! $request->hasFile('login_logo') && is_string($request->input('login_logo'))) {
                $settings->login_logo = $request->input('login_logo') ?: null;
            }
            if ($request->has('business_panel_logo') && ! $request->hasFile('business_panel_logo') && is_string($request->input('business_panel_logo'))) {
                $settings->business_panel_logo = $request->input('business_panel_logo') ?: null;
            }
            if ($request->has('user_panel_logo') && ! $request->hasFile('user_panel_logo') && is_string($request->input('user_panel_logo'))) {
                $settings->user_panel_logo = $request->input('user_panel_logo') ?: null;
            }
            if ($request->has('email_template_logo') && ! $request->hasFile('email_template_logo') && is_string($request->input('email_template_logo'))) {
                $settings->email_template_logo = $request->input('email_template_logo') ?: null;
            }
            $this->handleLogoUpload($request, $settings);
            $this->handleFaviconUpload($request, $settings);
            $this->handleLoginLogoUpload($request, $settings);
            $this->handleBusinessPanelLogoUpload($request, $settings);
            $this->handleUserPanelLogoUpload($request, $settings);
            $this->handleEmailTemplateLogoUpload($request, $settings);
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
                'site_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'seo_site_title' => ['sometimes', 'nullable', 'string', 'max:255'],
                'seo_description' => ['sometimes', 'nullable', 'string', 'max:1000'],
                'seo_keywords' => ['sometimes', 'nullable', 'string', 'max:3000'],
                'button_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'button_url' => ['sometimes', 'nullable', 'string', 'max:500'],
                'button' => ['sometimes', 'array'],
                'button.name' => ['nullable', 'string', 'max:255'],
                'button.url' => ['nullable', 'string', 'max:500'],
                'theme_primary_color' => ['sometimes', 'nullable', 'string', 'max:50'],
                'theme_secondary_color' => ['sometimes', 'nullable', 'string', 'max:50'],
                'confirmation_message' => ['sometimes', 'nullable', 'string'],
                'default_message' => ['sometimes', 'nullable', 'string'],
                'loader_type' => ['sometimes', 'nullable', 'string', 'max:50'],
                'loader_color' => ['sometimes', 'nullable', 'string', 'max:50'],
                'maintenance_mode' => ['sometimes', 'boolean'],
                'maintenance_message' => ['sometimes', 'nullable', 'string'],
                'contact_us_email' => ['sometimes', 'nullable', 'email', 'max:255'],
                'contact_us_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
                'contact_us_address' => ['sometimes', 'nullable', 'string'],
                'header_links' => ['sometimes', 'array'],
                'header_links.*.label' => ['required_with:header_links', 'string', 'max:255'],
                'header_links.*.url' => ['required_with:header_links', 'string', 'max:500'],
                'header_links.*.show_in_header' => ['sometimes', 'boolean'],
                'header_links.*.sort_order' => ['sometimes', 'integer', 'min:0'],
                'tax_rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'tax_enabled' => ['sometimes', 'boolean'],
                'loyalty_points_earned_per_unit_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'loyalty_points_earned_points' => ['sometimes', 'nullable', 'integer', 'min:0'],
                'charity_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'charity_donation_enabled' => ['sometimes', 'boolean'],
                'charity_default_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
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
            if ($request->hasFile('login_logo')) {
                $rules['login_logo'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } elseif ($request->hasFile('login_logo_file')) {
                $rules['login_logo_file'] = ['sometimes', 'nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,ico,bmp,tiff,tif', 'max:51200'];
            } else {
                $rules['login_logo'] = ['sometimes', 'nullable', 'string', 'max:2100000'];
            }
            $request->validate($rules);

            if ($request->has('seo_site_title')) {
                $settings->seo_site_title = $request->input('seo_site_title');
            }
            if ($request->has('site_name')) {
                $settings->site_name = $request->input('site_name');
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
            
            $generalFields = [
                'confirmation_message', 'default_message', 'loader_type', 'loader_color',
                'maintenance_message', 'contact_us_email', 'contact_us_phone', 'contact_us_address',
                'tax_rate', 'loyalty_points_earned_per_unit_price', 'loyalty_points_earned_points',
                'charity_name', 'charity_default_amount'
            ];
            foreach ($generalFields as $field) {
                if ($request->has($field)) {
                    $settings->$field = $request->input($field);
                }
            }
            if ($request->has('maintenance_mode')) {
                $settings->maintenance_mode = filter_var($request->input('maintenance_mode'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('tax_enabled')) {
                $settings->tax_enabled = filter_var($request->input('tax_enabled'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('charity_donation_enabled')) {
                $settings->charity_donation_enabled = filter_var($request->input('charity_donation_enabled'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('logo') && ! $request->hasFile('logo') && is_string($request->input('logo'))) {
                $settings->logo = $request->input('logo') ?: null;
            }
            if ($request->has('favicon') && ! $request->hasFile('favicon') && is_string($request->input('favicon'))) {
                $settings->favicon = $request->input('favicon') ?: null;
            }
            if ($request->has('login_logo') && ! $request->hasFile('login_logo') && is_string($request->input('login_logo'))) {
                $settings->login_logo = $request->input('login_logo') ?: null;
            }
            $this->applyButtonFromRequest($request, $settings);

            $this->handleLogoUpload($request, $settings);
            $this->handleFaviconUpload($request, $settings);
            $this->handleLoginLogoUpload($request, $settings);
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
            if ($settings->login_logo && ! str_starts_with($settings->login_logo, 'http') && ! str_starts_with($settings->login_logo, '/')) {
                Storage::disk('public')->delete($settings->login_logo);
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

            $allowedFields = ['site_name', 'seo_site_title', 'seo_description', 'seo_keywords', 'logo', 'login_logo', 'favicon', 'button_name', 'button_url'];
            if (! in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed: ' . implode(', ', $allowedFields),
                ], 422);
            }

            if ($field === 'logo' && $settings->logo && ! str_starts_with($settings->logo, 'http') && ! str_starts_with($settings->logo, '/')) {
                Storage::disk('public')->delete($settings->logo);
            }
            if ($field === 'login_logo' && $settings->login_logo && ! str_starts_with($settings->login_logo, 'http') && ! str_starts_with($settings->login_logo, '/')) {
                Storage::disk('public')->delete($settings->login_logo);
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

    private function handleLoginLogoUpload(Request $request, SiteSetting $settings): void
    {
        $file = $request->hasFile('login_logo') ? $request->file('login_logo') : ($request->hasFile('login_logo_file') ? $request->file('login_logo_file') : null);
        if ($file) {
            if ($settings->login_logo && ! str_starts_with($settings->login_logo, 'http') && ! str_starts_with($settings->login_logo, '/')) {
                Storage::disk('public')->delete($settings->login_logo);
            }
            $path = $file->store('site-settings', 'public');
            $settings->login_logo = $path;
            $settings->save();
            return;
        }

        if ($request->has('login_logo') && is_string($request->input('login_logo'))) {
            $logoInput = $request->input('login_logo');
            if (preg_match('/^data:image\/([\w+]+);base64,/', $logoInput, $matches)) {
                if ($settings->login_logo && ! str_starts_with($settings->login_logo, 'http') && ! str_starts_with($settings->login_logo, '/')) {
                    Storage::disk('public')->delete($settings->login_logo);
                }
                $imageType = str_replace('+xml', '', $matches[1]);
                $imageData = base64_decode(preg_replace('/^data:image\/[\w+]+;base64,/', '', $logoInput));
                if ($imageData !== false) {
                    $filename = 'login_logo_' . time() . '.' . $imageType;
                    $path = 'site-settings/' . $filename;
                    Storage::disk('public')->put($path, $imageData);
                    $settings->login_logo = $path;
                    $settings->save();
                }
            }
        }
    }

    private function handleBusinessPanelLogoUpload(Request $request, SiteSetting $settings): void
    {
        $file = $request->hasFile('business_panel_logo') ? $request->file('business_panel_logo') : ($request->hasFile('business_panel_logo_file') ? $request->file('business_panel_logo_file') : null);
        if ($file) {
            if ($settings->business_panel_logo && ! str_starts_with($settings->business_panel_logo, 'http') && ! str_starts_with($settings->business_panel_logo, '/')) {
                Storage::disk('public')->delete($settings->business_panel_logo);
            }
            $path = $file->store('site-settings', 'public');
            $settings->business_panel_logo = $path;
            $settings->save();
            return;
        }

        if ($request->has('business_panel_logo') && is_string($request->input('business_panel_logo'))) {
            $logoInput = $request->input('business_panel_logo');
            if (preg_match('/^data:image\/([\w+]+);base64,/', $logoInput, $matches)) {
                if ($settings->business_panel_logo && ! str_starts_with($settings->business_panel_logo, 'http') && ! str_starts_with($settings->business_panel_logo, '/')) {
                    Storage::disk('public')->delete($settings->business_panel_logo);
                }
                $imageType = str_replace('+xml', '', $matches[1]);
                $imageData = base64_decode(preg_replace('/^data:image\/[\w+]+;base64,/', '', $logoInput));
                if ($imageData !== false) {
                    $filename = 'business_panel_logo_' . time() . '.' . $imageType;
                    $path = 'site-settings/' . $filename;
                    Storage::disk('public')->put($path, $imageData);
                    $settings->business_panel_logo = $path;
                    $settings->save();
                }
            }
        }
    }

    private function handleUserPanelLogoUpload(Request $request, SiteSetting $settings): void
    {
        $file = $request->hasFile('user_panel_logo') ? $request->file('user_panel_logo') : ($request->hasFile('user_panel_logo_file') ? $request->file('user_panel_logo_file') : null);
        if ($file) {
            if ($settings->user_panel_logo && ! str_starts_with($settings->user_panel_logo, 'http') && ! str_starts_with($settings->user_panel_logo, '/')) {
                Storage::disk('public')->delete($settings->user_panel_logo);
            }
            $path = $file->store('site-settings', 'public');
            $settings->user_panel_logo = $path;
            $settings->save();
            return;
        }

        if ($request->has('user_panel_logo') && is_string($request->input('user_panel_logo'))) {
            $logoInput = $request->input('user_panel_logo');
            if (preg_match('/^data:image\/([\w+]+);base64,/', $logoInput, $matches)) {
                if ($settings->user_panel_logo && ! str_starts_with($settings->user_panel_logo, 'http') && ! str_starts_with($settings->user_panel_logo, '/')) {
                    Storage::disk('public')->delete($settings->user_panel_logo);
                }
                $imageType = str_replace('+xml', '', $matches[1]);
                $imageData = base64_decode(preg_replace('/^data:image\/[\w+]+;base64,/', '', $logoInput));
                if ($imageData !== false) {
                    $filename = 'user_panel_logo_' . time() . '.' . $imageType;
                    $path = 'site-settings/' . $filename;
                    Storage::disk('public')->put($path, $imageData);
                    $settings->user_panel_logo = $path;
                    $settings->save();
                }
            }
        }
    }

    private function handleEmailTemplateLogoUpload(Request $request, SiteSetting $settings): void
    {
        $file = $request->hasFile('email_template_logo') ? $request->file('email_template_logo') : ($request->hasFile('email_template_logo_file') ? $request->file('email_template_logo_file') : null);
        if ($file) {
            if ($settings->email_template_logo && ! str_starts_with($settings->email_template_logo, 'http') && ! str_starts_with($settings->email_template_logo, '/')) {
                Storage::disk('public')->delete($settings->email_template_logo);
            }
            $path = $file->store('site-settings', 'public');
            $settings->email_template_logo = $path;
            $settings->save();
            return;
        }

        if ($request->has('email_template_logo') && is_string($request->input('email_template_logo'))) {
            $logoInput = $request->input('email_template_logo');
            if (preg_match('/^data:image\/([\w+]+);base64,/', $logoInput, $matches)) {
                if ($settings->email_template_logo && ! str_starts_with($settings->email_template_logo, 'http') && ! str_starts_with($settings->email_template_logo, '/')) {
                    Storage::disk('public')->delete($settings->email_template_logo);
                }
                $imageType = str_replace('+xml', '', $matches[1]);
                $imageData = base64_decode(preg_replace('/^data:image\/[\w+]+;base64,/', '', $logoInput));
                if ($imageData !== false) {
                    $filename = 'email_template_logo_' . time() . '.' . $imageType;
                    $path = 'site-settings/' . $filename;
                    Storage::disk('public')->put($path, $imageData);
                    $settings->email_template_logo = $path;
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
            'site_name' => $settings->site_name,
            'seo_site_title' => $settings->seo_site_title,
            'seo_description' => $settings->seo_description,
            'seo_keywords' => $settings->seo_keywords,
            'logo' => $settings->logo,
            'logo_url' => $settings->logo_url,
            'login_logo' => $settings->login_logo,
            'login_logo_url' => $settings->login_logo_url,
            'favicon' => $settings->favicon,
            'favicon_url' => $settings->favicon_url,
            'business_panel_logo' => $settings->business_panel_logo,
            'business_panel_logo_url' => $settings->business_panel_logo_url,
            'user_panel_logo' => $settings->user_panel_logo,
            'user_panel_logo_url' => $settings->user_panel_logo_url,
            'email_template_logo' => $settings->email_template_logo,
            'email_template_logo_url' => $settings->email_template_logo_url,
            'button' => [
                'name' => $settings->button_name,
                'url' => $settings->button_url,
            ],
            'theme_primary_color' => $settings->theme_primary_color ?? '#ff6c00',
            'theme_secondary_color' => $settings->theme_secondary_color ?? '#ff00a7',
            'confirmation_message' => $settings->confirmation_message,
            'default_message' => $settings->default_message,
            'loader_type' => $settings->loader_type,
            'loader_color' => $settings->loader_color,
            'maintenance_mode' => (bool)$settings->maintenance_mode,
            'maintenance_message' => $settings->maintenance_message,
            'contact_us_email' => $settings->contact_us_email,
            'contact_us_phone' => $settings->contact_us_phone,
            'contact_us_address' => $settings->contact_us_address,
            'tax_rate' => (float)($settings->tax_rate ?? 10.00),
            'tax_enabled' => (bool)$settings->tax_enabled,
            'loyalty_points_earned_per_unit_price' => (float)($settings->loyalty_points_earned_per_unit_price ?? 50.00),
            'loyalty_points_earned_points' => (int)($settings->loyalty_points_earned_points ?? 2),
            'charity_name' => $settings->charity_name ?? 'Red Cross',
            'charity_donation_enabled' => (bool)$settings->charity_donation_enabled,
            'charity_default_amount' => (float)($settings->charity_default_amount ?? 1.00),
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
            'site_name' => null,
            'seo_site_title' => null,
            'seo_description' => null,
            'seo_keywords' => null,
            'logo' => null,
            'logo_url' => null,
            'login_logo' => null,
            'login_logo_url' => null,
            'favicon' => null,
            'favicon_url' => null,
            'business_panel_logo' => null,
            'business_panel_logo_url' => null,
            'user_panel_logo' => null,
            'user_panel_logo_url' => null,
            'email_template_logo' => null,
            'email_template_logo_url' => null,
            'button' => ['name' => null, 'url' => null],
            'theme_primary_color' => '#ff6c00',
            'theme_secondary_color' => '#ff00a7',
            'confirmation_message' => null,
            'default_message' => null,
            'loader_type' => null,
            'loader_color' => null,
            'maintenance_mode' => false,
            'maintenance_message' => null,
            'contact_us_email' => null,
            'contact_us_phone' => null,
            'contact_us_address' => null,
            'header_links' => $links,
        ];
    }
}
