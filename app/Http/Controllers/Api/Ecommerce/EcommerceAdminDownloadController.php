<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCustomerFile;
use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EcommerceAdminDownloadController extends Controller
{
    public function index(Request $request)
    {
        $query = EcommerceCustomerFile::with('user');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('file_type') && $request->file_type !== 'all') {
            $query->where('file_type', $request->file_type);
        }

        $sort = $request->query('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'downloads':
                $query->orderBy('download_count', 'desc');
                break;
            case 'size':
                $query->orderBy('size_bytes', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = $request->query('per_page', 8);
        $files = $query->paginate($perPage);

        $files->getCollection()->transform(function ($file) {
            return [
                'id' => $file->id,
                'customer' => [
                    'id' => $file->user_id,
                    'name' => optional($file->user)->name,
                    'email' => optional($file->user)->email,
                    'avatar' => optional($file->user)->avatar,
                ],
                'file_details' => [
                    'name' => $file->file_name,
                    'description' => $file->description,
                ],
                'type' => strtoupper($file->file_type),
                'size' => $this->humanSize($file->size_bytes),
                'uploaded_on' => $file->created_at->format('M d, Y h:i A'),
                'downloads' => $file->download_count,
                'status' => $file->status,
                'download_url' => url(Storage::url($file->file_path)),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $files,
        ]);
    }

    public function stats()
    {
        $totalFiles = EcommerceCustomerFile::count();
        $totalCustomers = EcommerceCustomerFile::distinct('user_id')->count('user_id');
        $storageUsed = EcommerceCustomerFile::sum('size_bytes');
        $totalDownloads = EcommerceCustomerFile::sum('download_count');
        $storageLimit = 50 * 1024 * 1024 * 1024; // 50 GB

        // Group by category to match chart data if needed (mocked categories for now based on types)
        $categories = EcommerceCustomerFile::selectRaw('category, SUM(size_bytes) as total_size')
            ->groupBy('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_files' => $totalFiles,
                'total_customers' => $totalCustomers,
                'storage_used' => $storageUsed,
                'storage_used_formatted' => $this->humanSize($storageUsed),
                'storage_limit' => $storageLimit,
                'total_downloads' => $totalDownloads,
                'categories' => $categories->map(function ($cat) {
                    return [
                        'name' => $cat->category,
                        'size' => $cat->total_size,
                        'size_formatted' => $this->humanSize($cat->total_size),
                    ];
                }),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category' => 'required|string',
            'file_name' => 'required|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'files' => 'required|array',
            'files.*' => 'file|max:204800', // max 200MB
        ]);

        $savedFiles = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store('downloads/' . $request->user_id, 'public');
            $ext = strtolower($file->getClientOriginalExtension());
            $size = $file->getSize();

            $customerFile = EcommerceCustomerFile::create([
                'user_id' => $request->user_id,
                'file_name' => count($request->file('files')) > 1 ? $request->file_name . ' - ' . $file->getClientOriginalName() : $request->file_name,
                'file_path' => $path,
                'file_type' => $ext,
                'category' => $request->category,
                'size_bytes' => $size,
                'description' => $request->description,
                'notes' => $request->notes,
                'status' => 'Published',
            ]);

            $savedFiles[] = $customerFile;
        }

        return response()->json([
            'success' => true,
            'message' => 'Files uploaded successfully.',
            'data' => $savedFiles,
        ]);
    }

    public function destroy($id)
    {
        $file = EcommerceCustomerFile::findOrFail($id);
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.',
        ]);
    }

    public function settings(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'enable_downloads_page' => 'boolean',
                'allow_search' => 'boolean',
                'allow_sorting' => 'boolean',
                'sorting_by' => 'string',
                'show_file_size' => 'boolean',
                'show_download_count' => 'boolean',
                'allow_guest_access' => 'boolean',
                'allowed_file_types' => 'array',
                'max_file_size' => 'string',
                'max_downloads_limit' => 'string',
                'download_expiry' => 'string',
            ]);

            SiteSetting::updateOrCreate(
                ['key' => 'ecommerce_downloads_settings'],
                ['value' => json_encode($data)]
            );

            return response()->json(['success' => true, 'message' => 'Settings updated successfully.', 'data' => $data]);
        }

        $setting = SiteSetting::where('key', 'ecommerce_downloads_settings')->first();
        $defaultSettings = [
            'enable_downloads_page' => true,
            'allow_search' => true,
            'allow_sorting' => true,
            'sorting_by' => 'latest',
            'show_file_size' => true,
            'show_download_count' => true,
            'allow_guest_access' => false,
            'allowed_file_types' => ['PSD', 'PDF', 'AI', 'EPS', 'JPG', 'PNG', 'DOCX', 'XLSX', 'PPTX', 'ZIP', 'MP4', 'TXT'],
            'max_file_size' => '200',
            'max_downloads_limit' => '10',
            'download_expiry' => '30',
        ];

        return response()->json([
            'success' => true,
            'data' => $setting ? array_merge($defaultSettings, json_decode($setting->value, true)) : $defaultSettings,
        ]);
    }

    private function humanSize($bytes)
    {
        if ($bytes == 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
    }
}
