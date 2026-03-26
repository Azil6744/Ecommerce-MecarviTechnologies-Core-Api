<?php

namespace App\Http\Controllers\Api\Admin\ContactPage;

use App\Http\Controllers\Controller;
use App\Models\ContactCard;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ContactCardController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all contact cards (Public endpoint)
     */
    public function index(Request $request)
    {
        try {
            $query = ContactCard::query();

            // Filter by card type if provided
            if ($request->has('card_type')) {
                $query->ofType($request->card_type);
            }

            // Filter by active status if provided
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            } else {
                // By default, show only active cards for public access
                $query->active();
            }

            $cards = $query->ordered()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'contact_cards' => $cards->map(function ($card) {
                        return $this->formatCard($card);
                    }),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contact cards',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Get specific contact card (Public endpoint)
     */
    public function show($id)
    {
        try {
            $card = ContactCard::find($id);

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact card not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'contact_card' => $this->formatCard($card),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contact card',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Create a new contact card (Admin only)
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage contact cards.',
                ], 403);
            }

            // Normalize boolean values before validation
            $this->normalizeBooleanInput($request, 'is_active');

            $validated = $this->validateCardData($request);

            // Handle icon upload
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('contact-cards', 'public');
                $validated['icon'] = $iconPath;
            } elseif ($request->has('icon') && is_string($request->input('icon'))) {
                $iconString = $request->input('icon');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $iconString, $matches)) {
                    $imageType = $matches[1];
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $iconString));
                    
                    if ($imageData !== false) {
                        $filename = 'contact_card_' . time() . '.' . $imageType;
                        $iconPath = 'contact-cards/' . $filename;
                        
                        Storage::disk('public')->put($iconPath, $imageData);
                        $validated['icon'] = $iconPath;
                    }
                }
            }

            $card = ContactCard::create($validated);

            $this->broadcastContentUpdate('contact-cards', 'created', [
                'id' => $card->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact card created successfully',
                'data' => [
                    'contact_card' => $this->formatCard($card),
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
                'message' => 'Contact card creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during contact card creation.',
            ], 500);
        }
    }

    /**
     * Update a contact card (Admin only)
     */
    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage contact cards.',
                ], 403);
            }

            $card = ContactCard::find($id);

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact card not found.',
                ], 404);
            }

            // Normalize boolean values before validation
            $this->normalizeBooleanInput($request, 'is_active');

            $validated = $this->validateCardData($request, true);

            // Handle icon upload
            if ($request->hasFile('icon')) {
                if ($card->icon) {
                    Storage::disk('public')->delete($card->icon);
                }
                $iconPath = $request->file('icon')->store('contact-cards', 'public');
                $validated['icon'] = $iconPath;
            } elseif ($request->has('icon') && is_string($request->input('icon'))) {
                $iconString = $request->input('icon');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $iconString, $matches)) {
                    $imageType = $matches[1];
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $iconString));
                    
                    if ($imageData !== false) {
                        if ($card->icon) {
                            Storage::disk('public')->delete($card->icon);
                        }
                        
                        $filename = 'contact_card_' . time() . '.' . $imageType;
                        $iconPath = 'contact-cards/' . $filename;
                        
                        Storage::disk('public')->put($iconPath, $imageData);
                        $validated['icon'] = $iconPath;
                    }
                }
            } elseif ($request->has('icon') && ($request->input('icon') === null || $request->input('icon') === 'delete' || $request->input('icon') === '')) {
                // Delete icon if explicitly set to null/delete/empty
                if ($card->icon) {
                    Storage::disk('public')->delete($card->icon);
                }
                $validated['icon'] = null;
            }

            $card->update($validated);

            $this->broadcastContentUpdate('contact-cards', 'updated', [
                'id' => $card->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact card updated successfully',
                'data' => [
                    'contact_card' => $this->formatCard($card),
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
                'message' => 'Contact card update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during contact card update.',
            ], 500);
        }
    }

    /**
     * Delete a contact card (Admin only)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete contact cards.',
                ], 403);
            }

            $card = ContactCard::find($id);

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact card not found.',
                ], 404);
            }

            if ($card->icon) {
                Storage::disk('public')->delete($card->icon);
            }

            $cardId = $card->id;
            $card->delete();

            $this->broadcastContentUpdate('contact-cards', 'deleted', [
                'id' => $cardId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact card deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contact card deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during contact card deletion.',
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
     * Validate card data based on card type
     */
    private function validateCardData(Request $request, $isUpdate = false)
    {
        $rules = [
            'card_type' => [$isUpdate ? 'sometimes' : 'required', 'string', 'in:call,fax,email,visit,store_hours,online_hours'],
            'badge_title' => ['nullable', 'string', 'max:255'],
            'secondary_badge' => ['nullable', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            'is_active' => ['sometimes', 'boolean', 'nullable'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];

        // Add type-specific validation rules
        $cardType = $request->input('card_type');
        
        if ($cardType === 'call') {
            $rules['phone_number_1'] = ['nullable', 'string', 'max:255'];
            $rules['phone_number_2'] = ['nullable', 'string', 'max:255'];
        } elseif ($cardType === 'fax') {
            $rules['fax_number'] = ['nullable', 'string', 'max:255'];
        } elseif ($cardType === 'email') {
            $rules['email_address'] = ['nullable', 'email', 'max:255'];
        } elseif ($cardType === 'visit') {
            $rules['street_address'] = ['nullable', 'string', 'max:500'];
            $rules['state_postal_code'] = ['nullable', 'string', 'max:255'];
            $rules['country'] = ['nullable', 'string', 'max:255'];
            $rules['address_type'] = ['nullable', 'string', 'in:us,other'];
            $rules['us_state'] = ['nullable', 'string', 'max:255'];
            $rules['phone_number_1'] = ['nullable', 'string', 'max:255'];
            $rules['phone_number_2'] = ['nullable', 'string', 'max:255'];
            $rules['email_address'] = ['nullable', 'email', 'max:255'];
        } elseif (in_array($cardType, ['store_hours', 'online_hours'])) {
            $rules['monday_friday_hours'] = ['nullable', 'string', 'max:255'];
            $rules['saturday_hours'] = ['nullable', 'string', 'max:255'];
            $rules['sunday_hours'] = ['nullable', 'string', 'max:255'];
        }

        return $request->validate($rules);
    }

    /**
     * Format card data for response
     */
    private function formatCard(ContactCard $card)
    {
        return [
            'id' => $card->id,
            'card_type' => $card->card_type,
            'badge_title' => $card->badge_title,
            'secondary_badge' => $card->secondary_badge,
            'label' => $card->label,
            'phone_number_1' => $card->phone_number_1,
            'phone_number_2' => $card->phone_number_2,
            'fax_number' => $card->fax_number,
            'email_address' => $card->email_address,
            'street_address' => $card->street_address,
            'state_postal_code' => $card->state_postal_code,
            'country' => $card->country,
            'address_type' => $card->address_type,
            'us_state' => $card->us_state,
            'monday_friday_hours' => $card->monday_friday_hours,
            'saturday_hours' => $card->saturday_hours,
            'sunday_hours' => $card->sunday_hours,
            'icon' => $card->icon_url,
            'is_active' => $card->is_active,
            'sort_order' => $card->sort_order,
            'created_at' => $card->created_at,
            'updated_at' => $card->updated_at,
        ];
    }
}
