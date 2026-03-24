<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSeoSetting;
use Illuminate\Http\Request;

class PageSeoSettingController extends Controller
{
    /**
     * Get all page SEO settings.
     */
    public function index()
    {
        $settings = PageSeoSetting::all()->keyBy('page_slug');

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get a single page's SEO settings by slug.
     */
    public function show(string $pageSlug)
    {
        $setting = PageSeoSetting::where('page_slug', $pageSlug)->first();

        if (!$setting) {
            return response()->json([
                'success' => true,
                'data' => [
                    'page_slug' => $pageSlug,
                    'tab_name' => null,
                    'seo_title' => null,
                    'seo_description' => null,
                    'seo_keywords' => null,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $setting,
        ]);
    }

    /**
     * Create or update a page's SEO settings.
     */
    public function upsert(Request $request, string $pageSlug)
    {
        $validated = $request->validate([
            'tab_name' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'seo_keywords' => ['nullable', 'string', 'max:1000'],
        ]);

        $setting = PageSeoSetting::updateOrCreate(
            ['page_slug' => $pageSlug],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Page SEO settings saved successfully.',
            'data' => $setting,
        ]);
    }
}
