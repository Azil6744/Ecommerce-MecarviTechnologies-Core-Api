<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use Illuminate\Http\Request;

class ProductReportController extends Controller
{
    public function index()
    {
        return response()->json(
            ProductReport::query()
                ->with(['product.previewAssets', 'user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn (ProductReport $report) => $this->transformReport($report))
                ->values()
        );
    }

    public function show(ProductReport $productReport)
    {
        $productReport->load(['product.previewAssets', 'user']);
        return response()->json($this->transformReport($productReport));
    }

    public function update(Request $request, ProductReport $productReport)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Pending,Under Review,Resolved'],
        ]);

        $productReport->status = $validated['status'];
        $productReport->save();

        $productReport->load(['product.previewAssets', 'user']);
        return response()->json($this->transformReport($productReport));
    }

    public function destroy(ProductReport $productReport)
    {
        $productReport->delete();

        return response()->json(['message' => 'Product report deleted successfully']);
    }

    private function transformReport(ProductReport $report): array
    {
        $previewAsset = $report->product && $report->product->previewAssets ? $report->product->previewAssets->first() : null;

        return [
            'id' => $report->id,
            'product_id' => $report->product_id,
            'product' => $report->product ? $report->product->name : 'Unknown Product',
            'product_image' => $previewAsset ? $previewAsset->image_path : null,
            'customer' => $report->user ? $report->user->name : 'Anonymous Guest',
            'issue' => $report->issue,
            'description' => $report->description,
            'status' => $report->status,
            'date' => optional($report->created_at)->format('M d, Y') ?? '-',
            'created_at' => optional($report->created_at)->toISOString(),
            'updated_at' => optional($report->updated_at)->toISOString(),
        ];
    }
}
