<?php

namespace App\Http\Controllers\Api\Admin\CareerPage;

use App\Http\Controllers\Controller;
use App\Models\CareerCard;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CareerCardController extends Controller
{
    use BroadcastsContentUpdates;

    public function index()
    {
        $cards = CareerCard::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'career_cards' => $cards->map(function ($card) {
                    return [
                        'id' => $card->id,
                        'section_title' => $card->section_title,
                        'section_background_color' => $card->section_background_color,
                        'title' => $card->title,
                        'description' => $card->description,
                        'image' => $card->image_url,
                        'background_color' => $card->background_color,
                        'is_active' => $card->is_active,
                        'sort_order' => $card->sort_order,
                        'created_at' => $card->created_at,
                        'updated_at' => $card->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage career cards.',
                ], 403);
            }

            $validated = $request->validate([
                'section_title' => ['required', 'string', 'max:255'],
                'section_background_color' => ['nullable', 'string', 'max:50'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_color' => ['nullable', 'string', 'max:50'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('career-cards', 'public');
                $validated['image'] = $imagePath;
            } elseif ($request->has('image') && is_string($request->input('image'))) {
                $imageString = $request->input('image');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $imageString, $matches)) {
                    $imageType = $matches[1];
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageString));
                    
                    if ($imageData !== false) {
                        $filename = 'career_card_' . time() . '.' . $imageType;
                        $imagePath = 'career-cards/' . $filename;
                        
                        Storage::disk('public')->put($imagePath, $imageData);
                        $validated['image'] = $imagePath;
                    }
                }
            }

            $card = CareerCard::create($validated);

            $this->broadcastContentUpdate('career-cards', 'created', [
                'id' => $card->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Career card created successfully',
                'data' => [
                    'career_card' => [
                        'id' => $card->id,
                        'section_title' => $card->section_title,
                        'section_background_color' => $card->section_background_color,
                        'title' => $card->title,
                        'subtitle' => $card->subtitle,
                        'description' => $card->description,
                        'image' => $card->image_url,
                        'background_color' => $card->background_color,
                        'is_active' => $card->is_active,
                        'sort_order' => $card->sort_order,
                        'created_at' => $card->created_at,
                        'updated_at' => $card->updated_at,
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
                'message' => 'Career card creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during career card creation.',
            ], 500);
        }
    }

    public function show($id)
    {
        $card = CareerCard::find($id);

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Career card not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'career_card' => [
                    'id' => $card->id,
                    'section_title' => $card->section_title,
                    'section_background_color' => $card->section_background_color,
                    'title' => $card->title,
                    'subtitle' => $card->subtitle,
                    'description' => $card->description,
                    'image' => $card->image_url,
                    'background_color' => $card->background_color,
                    'is_active' => $card->is_active,
                    'sort_order' => $card->sort_order,
                    'created_at' => $card->created_at,
                    'updated_at' => $card->updated_at,
                ],
            ],
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage career cards.',
                ], 403);
            }

            $card = CareerCard::find($id);

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career card not found.',
                ], 404);
            }

            $validated = $request->validate([
                'section_title' => ['sometimes', 'string', 'max:255'],
                'section_background_color' => ['nullable', 'string', 'max:50'],
                'title' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_color' => ['nullable', 'string', 'max:50'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            if ($request->hasFile('image')) {
                if ($card->image) {
                    Storage::disk('public')->delete($card->image);
                }
                $imagePath = $request->file('image')->store('career-cards', 'public');
                $validated['image'] = $imagePath;
            } elseif ($request->has('image') && is_string($request->input('image'))) {
                $imageString = $request->input('image');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $imageString, $matches)) {
                    $imageType = $matches[1];
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageString));
                    
                    if ($imageData !== false) {
                        if ($card->image) {
                            Storage::disk('public')->delete($card->image);
                        }
                        
                        $filename = 'career_card_' . time() . '.' . $imageType;
                        $imagePath = 'career-cards/' . $filename;
                        
                        Storage::disk('public')->put($imagePath, $imageData);
                        $validated['image'] = $imagePath;
                    }
                }
            }

            $card->update($validated);

            $this->broadcastContentUpdate('career-cards', 'updated', [
                'id' => $card->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Career card updated successfully',
                'data' => [
                    'career_card' => [
                        'id' => $card->id,
                        'section_title' => $card->section_title,
                        'section_background_color' => $card->section_background_color,
                        'title' => $card->title,
                        'subtitle' => $card->subtitle,
                        'description' => $card->description,
                        'image' => $card->image_url,
                        'background_color' => $card->background_color,
                        'is_active' => $card->is_active,
                        'sort_order' => $card->sort_order,
                        'created_at' => $card->created_at,
                        'updated_at' => $card->updated_at,
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
                'message' => 'Career card update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during career card update.',
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete career cards.',
                ], 403);
            }

            $card = CareerCard::find($id);

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career card not found.',
                ], 404);
            }

            if ($card->image) {
                Storage::disk('public')->delete($card->image);
            }

            $cardId = $card->id;
            $card->delete();

            $this->broadcastContentUpdate('career-cards', 'deleted', [
                'id' => $cardId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Career card deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Career card deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during career card deletion.',
            ], 500);
        }
    }

    public function deleteField(Request $request, $id, $field)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete career card fields.',
                ], 403);
            }

            $card = CareerCard::find($id);

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career card not found.',
                ], 404);
            }

            $allowedFields = [
                'image', 'section_title', 'section_background_color', 'title', 'description', 
                'background_color', 'is_active', 'sort_order'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            $imageFields = ['image'];

            if (in_array($field, $imageFields) && $card->$field) {
                Storage::disk('public')->delete($card->$field);
                $card->$field = null;
            } else {
                $card->$field = null;
            }

            $card->save();

            $this->broadcastContentUpdate('career-cards', 'updated', [
                'id' => $card->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'career_card' => [
                        'id' => $card->id,
                        'section_title' => $card->section_title,
                        'section_background_color' => $card->section_background_color,
                        'title' => $card->title,
                        'subtitle' => $card->subtitle,
                        'description' => $card->description,
                        'image' => $card->image_url,
                        'background_color' => $card->background_color,
                        'is_active' => $card->is_active,
                        'sort_order' => $card->sort_order,
                        'created_at' => $card->created_at,
                        'updated_at' => $card->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Field deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during field deletion.',
            ], 500);
        }
    }
}
