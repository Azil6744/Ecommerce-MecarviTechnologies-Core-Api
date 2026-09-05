<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceCustomerFile;
use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
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
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('file_type') && $request->file_type !== 'all') {
            $query->where('file_type', strtolower($request->file_type));
        }

        if ($request->has('recipient_type') && $request->recipient_type !== 'all') {
            $type = strtolower($request->recipient_type);
            $businessRoles = ['business', 'business_user', 'business-customer', 'company', 'seller'];
            if ($type === 'business') {
                $query->whereHas('user', function ($u) use ($businessRoles) {
                    $u->whereIn('role', $businessRoles)
                      ->orWhereHas('roles', fn($r) => $r->whereIn('name', $businessRoles));
                });
            } elseif ($type === 'customer') {
                $query->whereHas('user', function ($u) use ($businessRoles) {
                    $u->where('role', 'customer')
                      ->orWhere(function ($sub) use ($businessRoles) {
                          $sub->whereNotIn('role', $businessRoles)
                              ->whereDoesntHave('roles', fn($r) => $r->whereIn('name', $businessRoles));
                      });
                });
            }
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

        $perPage = (int) $request->query('per_page', 8);
        $files = $query->paginate($perPage);

        $businessRoles = ['business', 'business_user', 'business-customer', 'company', 'seller'];

        $files->getCollection()->transform(function ($file) use ($businessRoles) {
            $user = $file->user;
            $isBusiness = false;
            if ($user) {
                $isBusiness = in_array($user->role, $businessRoles, true)
                    || ($user->relationLoaded('roles') && $user->roles->pluck('name')->intersect($businessRoles)->isNotEmpty());
            }

            $businessName = null;
            if ($user) {
                $businessName = $user->business_name ?? $user->company_name ?? ($isBusiness ? ($user->name ? $user->name . "'s Business" : "Business Account") : null);
            }

            return [
                'id' => $file->id,
                'user_type' => $isBusiness ? 'business' : 'customer',
                'customer' => [
                    'id' => $file->user_id,
                    'name' => optional($user)->name ?? 'User #' . $file->user_id,
                    'business_name' => $businessName,
                    'email' => optional($user)->email ?? '',
                    'avatar' => optional($user)->avatar ?? '',
                    'role' => optional($user)->role ?? 'customer',
                    'is_business' => $isBusiness,
                ],
                'file_details' => [
                    'name' => $file->file_name,
                    'description' => $file->description,
                ],
                'category' => $file->category,
                'notes' => $file->notes,
                'type' => strtoupper($file->file_type ?: pathinfo($file->file_path, PATHINFO_EXTENSION)),
                'size' => $this->humanSize($file->size_bytes),
                'uploaded_on' => $file->created_at ? $file->created_at->format('M d, Y h:i A') : '',
                'downloads' => $file->download_count ?? 0,
                'status' => $file->status ?? 'Published',
                'download_url' => url(Storage::url($file->file_path)),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $files,
        ]);
    }

    public function users(Request $request)
    {
        $search = $request->query('search', '');
        $businessRoles = ['business', 'business_user', 'business-customer', 'company', 'seller'];

        $query = User::query();
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $allUsers = $query->orderBy('name', 'asc')->limit(150)->get();

        $customers = [];
        $businesses = [];

        foreach ($allUsers as $user) {
            $isBiz = in_array($user->role, $businessRoles, true);
            $bizName = $user->business_name ?? $user->company_name ?? ($isBiz ? ($user->name ? $user->name . "'s Business" : "Business Account") : null);

            $payload = [
                'id' => $user->id,
                'name' => $user->name,
                'business_name' => $bizName,
                'email' => $user->email,
                'role' => $user->role ?? ($isBiz ? 'business' : 'customer'),
                'type' => $isBiz ? 'business' : 'customer',
                'avatar' => $user->avatar ?? '',
            ];

            if ($isBiz) {
                $businesses[] = $payload;
            } else {
                $customers[] = $payload;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => $customers,
                'businesses' => $businesses,
                'all' => array_merge($customers, $businesses),
            ],
        ]);
    }

    public function stats()
    {
        $totalFiles = EcommerceCustomerFile::count();
        $totalUsers = EcommerceCustomerFile::distinct('user_id')->count('user_id');
        $businessRoles = ['business', 'business_user', 'business-customer', 'company', 'seller'];

        $totalBusinesses = EcommerceCustomerFile::whereHas('user', function ($u) use ($businessRoles) {
            $u->whereIn('role', $businessRoles);
        })->distinct('user_id')->count('user_id');

        $totalCustomers = max(0, $totalUsers - $totalBusinesses);
        $storageUsed = EcommerceCustomerFile::sum('size_bytes') ?: 0;
        $totalDownloads = EcommerceCustomerFile::sum('download_count') ?: 0;
        $storageLimit = 50 * 1024 * 1024 * 1024; // 50 GB

        $categories = EcommerceCustomerFile::selectRaw('category, SUM(size_bytes) as total_size, COUNT(*) as file_count')
            ->groupBy('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_files' => $totalFiles,
                'total_users' => $totalUsers,
                'total_customers' => $totalCustomers,
                'total_businesses' => $totalBusinesses,
                'storage_used' => $storageUsed,
                'storage_used_formatted' => $this->humanSize($storageUsed),
                'storage_limit' => $storageLimit,
                'total_downloads' => $totalDownloads,
                'categories' => $categories->map(function ($cat) {
                    return [
                        'name' => $cat->category ?: 'General',
                        'size' => $cat->total_size,
                        'size_formatted' => $this->humanSize($cat->total_size),
                        'count' => $cat->file_count,
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

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'enable_downloads_page' => 'nullable|boolean',
                'allow_search' => 'nullable|boolean',
                'allow_sorting' => 'nullable|boolean',
                'sorting_by' => 'nullable|string',
                'show_file_size' => 'nullable|boolean',
                'show_download_count' => 'nullable|boolean',
                'allow_guest_access' => 'nullable|boolean',
                'allowed_file_types' => 'nullable|array',
                'max_file_size' => 'nullable|string',
                'max_downloads_limit' => 'nullable|string',
                'download_expiry' => 'nullable|string',
            ]);

            $merged = array_merge($defaultSettings, $data);

            try {
                $siteSetting = SiteSetting::firstOrCreate([]);
                if (Schema::hasColumn('site_settings', 'downloads_settings')) {
                    $siteSetting->downloads_settings = json_encode($merged);
                    $siteSetting->save();
                }
            } catch (\Throwable $e) {
                // Ignore DB error, use cache fallback
            }

            Cache::forever('ecommerce_downloads_settings', $merged);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully.',
                'data' => $merged,
            ]);
        }

        $cached = Cache::get('ecommerce_downloads_settings');
        if ($cached && is_array($cached)) {
            return response()->json([
                'success' => true,
                'data' => array_merge($defaultSettings, $cached),
            ]);
        }

        try {
            $siteSetting = SiteSetting::first();
            if ($siteSetting && Schema::hasColumn('site_settings', 'downloads_settings') && !empty($siteSetting->downloads_settings)) {
                $decoded = json_decode($siteSetting->downloads_settings, true);
                if (is_array($decoded)) {
                    return response()->json([
                        'success' => true,
                        'data' => array_merge($defaultSettings, $decoded),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // DB fallback
        }

        return response()->json([
            'success' => true,
            'data' => $defaultSettings,
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
