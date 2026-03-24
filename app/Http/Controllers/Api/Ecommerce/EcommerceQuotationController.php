<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceQuotation;
use Illuminate\Support\Facades\Schema;

class EcommerceQuotationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        // Check if admin to return all, or just user
        if ($user && $user->isSuperAdmin()) {
            return response()->json(['success' => true, 'data' => EcommerceQuotation::all()]);
        }
        
        // Get by user_id if column exists, otherwise all
        if(Schema::hasColumn((new EcommerceQuotation)->getTable(), 'user_id')) {
            $query = EcommerceQuotation::where('user_id', $user->id);
            return response()->json(['success' => true, 'data' => $query->get()]);
        }

        return response()->json(['success' => true, 'data' => EcommerceQuotation::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if(Schema::hasColumn((new EcommerceQuotation)->getTable(), 'user_id')) {
            $data['user_id'] = $request->user()->id;
        }
        $item = EcommerceQuotation::create($data);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function show(Request $request, $id)
    {
        $item = EcommerceQuotation::findOrFail($id);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = EcommerceQuotation::findOrFail($id);
        $item->update($request->all());
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $item = EcommerceQuotation::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}
