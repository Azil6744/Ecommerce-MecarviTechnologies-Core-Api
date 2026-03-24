<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceReview;
use Illuminate\Support\Facades\Schema;

class EcommerceReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        // Check if admin to return all, or just user
        if ($user && $user->isSuperAdmin()) {
            return response()->json(['success' => true, 'data' => EcommerceReview::all()]);
        }
        
        // Get by user_id if column exists, otherwise all
        if(Schema::hasColumn((new EcommerceReview)->getTable(), 'user_id')) {
            $query = EcommerceReview::where('user_id', $user->id);
            return response()->json(['success' => true, 'data' => $query->get()]);
        }

        return response()->json(['success' => true, 'data' => EcommerceReview::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if(Schema::hasColumn((new EcommerceReview)->getTable(), 'user_id')) {
            $data['user_id'] = $request->user()->id;
        }
        $item = EcommerceReview::create($data);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function show(Request $request, $id)
    {
        $item = EcommerceReview::findOrFail($id);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = EcommerceReview::findOrFail($id);
        $item->update($request->all());
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $item = EcommerceReview::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}
