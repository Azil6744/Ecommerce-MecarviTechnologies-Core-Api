<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Admin\ProductReportController;
use App\Models\ProductReport;
use Illuminate\Http\Request;

class ReportedProductController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user() ? $request->user()->id : $request->input('user_id');
        $email = $request->input('email') ?? ($request->user() ? $request->user()->email : null);
        $name = $request->input('name') ?? ($request->user() ? $request->user()->name : null);

        $query = ProductReport::query();

        // If user identifier (user_id, email, or name) is provided, filter by them
        if (!empty($userId) || !empty($email) || !empty($name)) {
            $query->where(function($q) use ($userId, $email, $name) {
                if (!empty($userId) && is_numeric($userId)) {
                    $q->where('user_id', (int) $userId);
                }
                if (!empty($email) && strlen(trim($email)) > 0) {
                    $cleanEmail = strtolower(trim($email));
                    $q->orWhereRaw('LOWER(customer_email) = ?', [$cleanEmail]);
                }
                if (!empty($name) && strlen(trim($name)) > 0) {
                    $cleanName = strtolower(trim($name));
                    $q->orWhereRaw('LOWER(customer_name) LIKE ?', ['%' . $cleanName . '%']);
                }
            });
        }
        // Fallback: If no parameters provided, return all reports sorted by date
        $allReports = $query->orderBy('created_at', 'desc')->get();

        $transformed = $allReports->map(fn (ProductReport $report) => ProductReportController::transformReport($report))->values();

        $stats = [
            'all' => $allReports->count(),
            'under_review' => $allReports->where('status', 'Under Review')->count(),
            'in_progress' => $allReports->where('status', 'In Progress')->count(),
            'resolved' => $allReports->where('status', 'Resolved')->count(),
            'closed' => $allReports->where('status', 'Closed')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'data' => $transformed,
        ]);
    }

    public function show($id)
    {
        $report = ProductReport::where('id', $id)
            ->orWhere('report_code', $id)
            ->orWhere('report_code', '#' . $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => ProductReportController::transformReport($report),
        ]);
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $report = ProductReport::where('id', $id)
            ->orWhere('report_code', $id)
            ->orWhere('report_code', '#' . $id)
            ->firstOrFail();

        $replies = $report->customer_replies ?? [];
        $userName = $request->user() ? $request->user()->name : ($report->customer_name ?? 'Customer');

        $newReply = [
            'id' => 'reply_' . uniqid(),
            'author' => 'Customer',
            'user_name' => $userName,
            'date' => now()->format('M d, Y h:i A'),
            'message' => $request->input('message'),
        ];

        $replies[] = $newReply;
        $report->customer_replies = $replies;
        $report->save();

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully',
            'data' => ProductReportController::transformReport($report),
        ]);
    }
}
