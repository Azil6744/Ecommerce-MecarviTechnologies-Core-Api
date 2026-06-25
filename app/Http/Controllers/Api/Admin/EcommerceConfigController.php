<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class EcommerceConfigController extends Controller
{
    /**
     * Get Loyalty settings.
     */
    public function getLoyalty()
    {
        try {
            $settings = SiteSetting::firstOrCreate([]);
            $loyalty = $settings->loyalty_settings ? json_decode($settings->loyalty_settings, true) : null;

            // Default values if empty
            if (!$loyalty) {
                $loyalty = [
                    'enabled' => false,
                    'points_per_dollar' => '1',
                    'points_to_dollar_ratio' => '0.01',
                    'minimum_redeem_points' => '100',
                    'max_redeem_percent' => '20',
                    'expiry_days' => '365'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $loyalty
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch loyalty config',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save Loyalty settings.
     */
    public function saveLoyalty(Request $request)
    {
        try {
            $settings = SiteSetting::firstOrCreate([]);
            
            $validated = $request->validate([
                'enabled' => 'required|boolean',
                'points_per_dollar' => 'required|string',
                'points_to_dollar_ratio' => 'required|string',
                'minimum_redeem_points' => 'required|string',
                'max_redeem_percent' => 'required|string',
                'expiry_days' => 'required|string'
            ]);

            // Sync legacy columns in site_settings for checkout code compatibility
            $settings->loyalty_points_earned_per_unit_price = (float)($validated['points_per_dollar'] > 0 ? (1.0 / (float)$validated['points_per_dollar']) * 50.0 : 50.0);
            $settings->loyalty_points_earned_points = (int)($validated['points_per_dollar'] ?: 2);

            $settings->loyalty_settings = json_encode($validated);
            $settings->save();

            return response()->json([
                'success' => true,
                'data' => $validated,
                'message' => 'Loyalty configuration saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save loyalty config',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Charity settings.
     */
    public function getCharity()
    {
        try {
            $settings = SiteSetting::firstOrCreate([]);
            $charity = $settings->charity_settings ? json_decode($settings->charity_settings, true) : null;

            if (!$charity) {
                $charity = [
                    'enabled' => false,
                    'charity_name' => 'Red Cross',
                    'charity_description' => 'Global humanitarian network providing relief and support.',
                    'suggested_amounts' => '1,5,10',
                    'allow_custom_amount' => true
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $charity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch charity config',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save Charity settings.
     */
    public function saveCharity(Request $request)
    {
        try {
            $settings = SiteSetting::firstOrCreate([]);
            
            $validated = $request->validate([
                'enabled' => 'required|boolean',
                'charity_name' => 'required|string|max:255',
                'charity_description' => 'required|string',
                'suggested_amounts' => 'required|string',
                'allow_custom_amount' => 'required|boolean'
            ]);

            // Sync legacy columns in site_settings for checkout code compatibility
            $settings->charity_name = $validated['charity_name'];
            $settings->charity_donation_enabled = $validated['enabled'];
            $suggested = explode(',', $validated['suggested_amounts']);
            $settings->charity_default_amount = (float)($suggested[0] ?? 1.00);

            $settings->charity_settings = json_encode($validated);
            $settings->save();

            return response()->json([
                'success' => true,
                'data' => $validated,
                'message' => 'Charity configuration saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save charity config',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Tips settings.
     */
    public function getTips()
    {
        try {
            $settings = SiteSetting::firstOrCreate([]);
            $tips = $settings->tips_settings ? json_decode($settings->tips_settings, true) : null;

            if (!$tips) {
                $tips = [
                    'enabled' => false,
                    'suggested_percentages' => '10,15,20',
                    'allow_custom' => true,
                    'max_custom_percent' => '30'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $tips
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tips config',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save Tips settings.
     */
    public function saveTips(Request $request)
    {
        try {
            $settings = SiteSetting::firstOrCreate([]);
            
            $validated = $request->validate([
                'enabled' => 'required|boolean',
                'suggested_percentages' => 'required|string',
                'allow_custom' => 'required|boolean',
                'max_custom_percent' => 'required|string'
            ]);

            $settings->tips_settings = json_encode($validated);
            $settings->save();

            return response()->json([
                'success' => true,
                'data' => $validated,
                'message' => 'Tips configuration saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save tips config',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
