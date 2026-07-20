<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Charity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CharityController extends Controller
{
    /**
     * Resolve the logo field: handle base64 data URL or preset string.
     * Returns the storage path or preset name.
     */
    private function resolveLogo(Request $request, ?string $oldPath = null): ?string
    {
        if ($request->has('logo_svg_type') && is_string($request->input('logo_svg_type'))) {
            $imageString = $request->input('logo_svg_type');

            if (preg_match('/^data:image\/(\w+);base64,/', $imageString, $matches)) {
                $imageType = $matches[1];
                $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageString));

                if ($imageData !== false) {
                    // Delete old custom logo if replacing
                    if ($oldPath && !in_array($oldPath, ['feeding_america', 'unicef_usa', 'red_cross', 'nature_conservancy', 'best_friends', 'helping_hands', 'st_jude', 'irc', 'generic_charity'])) {
                        Storage::disk('public')->delete($oldPath);
                    }
                    $filename = 'charity_' . time() . '_' . Str::random(6) . '.' . $imageType;
                    $imagePath = 'charities/' . $filename;
                    Storage::disk('public')->put($imagePath, $imageData);
                    return $imagePath;
                }
            }

            // Return plain preset string or existing image path
            return $imageString;
        }

        return 'generic_charity';
    }

    public function index(Request $request)
    {
        $charities = Charity::orderBy('created_at', 'desc')->get();
        
        $dbCompletedAmount = \App\Models\Donation::where('status', 'Completed')->sum('amount');
        $dbPendingAmount = \App\Models\Donation::where('status', 'Pending')->sum('amount');
        $totalDonationsAmount = $dbCompletedAmount + $dbPendingAmount;

        $stats = [
            'total_charities' => Charity::count(),
            'active_charities' => Charity::where('status', 'Active')->count(),
            'inactive_charities' => Charity::where('status', 'Inactive')->count(),
            'total_donations_amount' => $totalDonationsAmount,
            'total_campaigns' => 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $charities,
            'stats' => $stats
        ]);
    }

    /**
     * Public endpoint: returns only Active charities for the checkout page.
     */
    public function publicIndex()
    {
        $charities = Charity::where('status', 'Active')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $charities,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'web' => 'nullable|string',
            'fax' => 'nullable|string',
            'category' => 'required|string',
            'status' => 'required|string|in:Active,Inactive',
            'assistance_tags' => 'required|array',
            'logo_svg_type' => 'nullable|string',
        ]);

        $logoPath = $this->resolveLogo($request);
        $validated['logo_svg_type'] = $logoPath;

        $charity = Charity::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Charity created successfully',
            'data' => $charity
        ]);
    }

    public function update(Request $request, $id)
    {
        $charity = Charity::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'web' => 'nullable|string',
            'fax' => 'nullable|string',
            'category' => 'required|string',
            'status' => 'required|string|in:Active,Inactive',
            'assistance_tags' => 'required|array',
            'logo_svg_type' => 'nullable|string',
        ]);

        $logoPath = $this->resolveLogo($request, $charity->logo_svg_type);
        $validated['logo_svg_type'] = $logoPath;

        $charity->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Charity updated successfully',
            'data' => $charity
        ]);
    }

    public function destroy($id)
    {
        $charity = Charity::findOrFail($id);
        $charity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Charity deleted successfully'
        ]);
    }

    public function toggleStatus($id)
    {
        $charity = Charity::findOrFail($id);
        $charity->status = $charity->status === 'Active' ? 'Inactive' : 'Active';
        $charity->save();

        return response()->json([
            'success' => true,
            'message' => 'Charity status updated successfully',
            'data' => $charity
        ]);
    }
}
