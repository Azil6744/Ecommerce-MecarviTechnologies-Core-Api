<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderProof;
use App\Models\EcommerceTicket;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class EcommerceDownloadController extends Controller
{
    public function index(Request $request)
    {
        $entries = $this->entries($request);
        $filtered = $this->applyFilters($entries, $request);
        $sorted = $this->applySort($filtered, (string) $request->query('sort', 'newest'));

        $perPage = min(max((int) $request->query('per_page', 12), 1), 50);
        $page = max((int) $request->query('page', 1), 1);
        $total = $sorted->count();

        $items = $sorted->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $paginator,
            'summary' => $this->summary($sorted),
        ]);
    }

    public function stats(Request $request)
    {
        $entries = $this->entries($request);
        $summary = $this->summary($entries);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    public function show(Request $request, string $downloadId)
    {
        $entry = $this->findEntry($request, $downloadId);

        return response()->json([
            'success' => true,
            'data' => $entry,
        ]);
    }

    public function preview(Request $request, string $downloadId): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $entry = $this->findEntry($request, $downloadId);
        $resolved = $this->resolveSource($entry);

        if (! $resolved) {
            return response()->json([
                'success' => false,
                'message' => 'Preview is not available for this file.',
            ], 422);
        }

        $disposition = in_array($resolved['mime'], ['application/pdf', 'image/png', 'image/jpeg', 'image/webp', 'image/gif'], true)
            ? 'inline'
            : 'attachment';

        return $this->streamResolvedFile($resolved, $disposition);
    }

    public function download(Request $request, string $downloadId): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $entry = $this->findEntry($request, $downloadId);
        $resolved = $this->resolveSource($entry);

        if (! $resolved) {
            return response()->json([
                'success' => false,
                'message' => 'Download is not available for this file.',
            ], 422);
        }

        return $this->streamResolvedFile($resolved, 'attachment');
    }

    public function bulkDownload(Request $request): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $entries = $this->applySort($this->applyFilters($this->entries($request), $request), (string) $request->query('sort', 'newest'));
        $items = $entries->values();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No downloadable files were found for the selected filters.',
            ], 422);
        }

        $zipName = 'mecarvi-downloads-' . now()->format('Y-m-d-His') . '.zip';
        $zipPath = storage_path('app/tmp/' . $zipName);
        if (! is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0777, true);
        }

        if (! class_exists(ZipArchive::class)) {
            return response()->json([
                'success' => false,
                'message' => 'ZIP downloads are not available on this server.',
            ], 500);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Could not prepare the download archive.',
            ], 500);
        }

        $manifest = [];

        foreach ($items as $index => $entry) {
            $resolved = $this->resolveSource($entry);
            $entryName = $this->safeFileName(($index + 1) . '-' . ($entry['title'] ?? 'download'));

            if ($resolved && $resolved['type'] === 'storage' && Storage::disk($resolved['disk'])->exists($resolved['path'])) {
                $zip->addFile(Storage::disk($resolved['disk'])->path($resolved['path']), $entryName . '.' . $resolved['extension']);
                continue;
            }

            if ($resolved && $resolved['type'] === 'remote') {
                try {
                    $response = Http::timeout(60)->get($resolved['url']);
                    if ($response->successful()) {
                        $zip->addFromString($entryName . '.' . $resolved['extension'], $response->body());
                        continue;
                    }
                } catch (\Throwable) {
                    // Fall through to manifest entry.
                }
            }

            $manifest[] = $entryName . ' | ' . ($entry['download_source'] ?? 'unavailable');
        }

        if ($manifest) {
            $zip->addFromString('_MANIFEST.txt', implode(PHP_EOL, $manifest));
        }

        $zip->close();

        return response()->streamDownload(function () use ($zipPath) {
            readfile($zipPath);
            @unlink($zipPath);
        }, $zipName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function requestFiles(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'download_ids' => ['nullable', 'array'],
            'download_ids.*' => ['string', 'max:255'],
        ]);

        $user = $request->user();
        $ticket = EcommerceTicket::create([
            'ticket_number' => 'TKT-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'contact_email' => $user->email,
            'subject' => $validated['subject'],
            'category' => $validated['category'] ?? 'Downloads',
            'priority' => 'normal',
            'status' => 'open',
            'message' => $validated['message'],
            'source_page' => 'downloads',
            'metadata' => [
                'download_ids' => $validated['download_ids'] ?? [],
            ],
            'last_customer_reply_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your file request has been submitted.',
            'data' => $ticket,
        ], 201);
    }

    private function entries(Request $request)
    {
        $user = $request->user();
        $orders = EcommerceOrder::query()
            ->with(['items.product.category', 'proofs'])
            ->when(! ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) && Schema::hasColumn((new EcommerceOrder)->getTable(), 'user_id'), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        $entries = collect();

        foreach ($orders as $order) {
            foreach ($order->proofs as $proof) {
                $entries->push($this->proofEntry($order, $proof));
            }

            foreach ($order->items as $item) {
                $product = $item->product;

                if ($product && ($product->is_digital || ! empty($product->download_url))) {
                    $entries->push($this->digitalProductEntry($order, $item));
                }

                foreach ($this->customizationFiles($item) as $fileIndex => $file) {
                    $entries->push($this->customizationFileEntry($order, $item, $file, $fileIndex));
                }
            }
        }

        return $entries->filter(fn ($entry) => ! empty($entry['download_source']))->values();
    }

    private function proofEntry(EcommerceOrder $order, EcommerceOrderProof $proof): array
    {
        $path = (string) $proof->file_path;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf');

        return $this->makeEntry([
            'id' => 'proof-' . $proof->id,
            'source_type' => 'proof',
            'source_id' => (string) $proof->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'title' => $proof->proof_type,
            'category' => 'Order Proof',
            'format' => strtoupper($ext),
            'status' => $this->mapProofStatus($proof->status),
            'status_key' => $proof->status,
            'created_at' => optional($proof->created_at)->toIso8601String(),
            'download_source' => $path,
            'download_name' => $this->safeFileName($proof->proof_type ?: 'proof') . '.' . $ext,
            'source_label' => 'Proof file',
        ]);
    }

    private function digitalProductEntry(EcommerceOrder $order, $item): array
    {
        $product = $item->product;
        $image = Arr::wrap($product?->images ?? []);
        $download = (string) ($product->download_url ?? '');
        $ext = strtolower(pathinfo(parse_url($download, PHP_URL_PATH) ?? $download, PATHINFO_EXTENSION) ?: 'zip');

        return $this->makeEntry([
            'id' => 'digital-' . $item->id,
            'source_type' => 'digital_product',
            'source_id' => (string) $item->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'title' => $product->name ?? $item->product_name,
            'category' => $product?->category?->name ?? 'Digital Product',
            'format' => strtoupper($ext),
            'status' => 'Ready',
            'status_key' => 'ready',
            'created_at' => optional($order->created_at)->toIso8601String(),
            'download_source' => $download,
            'download_name' => $this->safeFileName($product->name ?? $item->product_name ?? 'download') . '.' . $ext,
            'thumbnail' => $image[0] ?? null,
            'source_label' => 'Digital product',
        ]);
    }

    private function customizationFileEntry(EcommerceOrder $order, $item, array $file, int $index): array
    {
        $path = (string) ($file['path'] ?? $file['file_path'] ?? '');
        $name = (string) ($file['original_name'] ?? basename($path) ?: 'file');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION) ?: pathinfo($path, PATHINFO_EXTENSION) ?: 'bin');
        $baseName = pathinfo($name, PATHINFO_FILENAME) ?: $name;

        return $this->makeEntry([
            'id' => 'file-' . $order->id . '-' . $item->id . '-' . $index,
            'source_type' => 'customization_file',
            'source_id' => (string) $item->id . '-' . $index,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'title' => $name,
            'category' => ucfirst((string) ($file['file_type'] ?? 'Artwork Uploads')),
            'format' => strtoupper($ext),
            'status' => 'Ready',
            'status_key' => 'ready',
            'created_at' => optional($order->created_at)->toIso8601String(),
            'download_source' => $path,
            'download_name' => $this->safeFileName($baseName) . '.' . $ext,
            'source_label' => 'Customization file',
        ]);
    }

    private function makeEntry(array $payload): array
    {
        $resolved = $this->resolveSource($payload);
        $size = $resolved['size'] ?? null;

        return array_merge($payload, [
            'size_bytes' => $size,
            'size' => $this->humanSize($size),
            'date' => $payload['created_at'] ? optional(\Carbon\Carbon::parse($payload['created_at']))->format('M j, Y') : now()->format('M j, Y'),
            'download_url' => $this->downloadUrl($payload['id']),
            'preview_url' => $this->previewUrl($payload['id']),
            'thumbnail_url' => $payload['thumbnail'] ?? null,
            'order_label' => 'Order #' . ($payload['order_number'] ?? ''),
        ]);
    }

    private function resolveSource(array $entry): ?array
    {
        $source = (string) ($entry['download_source'] ?? '');
        if ($source === '') {
            return null;
        }

        if (Str::startsWith($source, ['http://', 'https://'])) {
            $name = $entry['download_name'] ?? basename(parse_url($source, PHP_URL_PATH) ?: 'download');

            return [
                'type' => 'remote',
                'url' => $source,
                'name' => $name,
                'mime' => $entry['mime'] ?? 'application/octet-stream',
                'extension' => strtolower(pathinfo($name, PATHINFO_EXTENSION) ?: 'bin'),
            ];
        }

        if (Str::startsWith($source, ['/assets/', '/mock-assets/', '/storage/'])) {
            if (Str::startsWith($source, '/storage/')) {
                $relative = ltrim(Str::after($source, '/storage/'), '/');
                $disk = 'public';
                $path = $relative;
                $name = $entry['download_name'] ?? basename($path);
                $mime = Storage::disk($disk)->exists($path)
                    ? Storage::disk($disk)->mimeType($path)
                    : 'application/octet-stream';

                return [
                    'type' => 'storage',
                    'disk' => $disk,
                    'path' => $path,
                    'name' => $name,
                    'mime' => $mime,
                    'size' => Storage::disk($disk)->exists($path) ? Storage::disk($disk)->size($path) : null,
                    'extension' => strtolower(pathinfo($name, PATHINFO_EXTENSION) ?: 'bin'),
                ];
            }

            return [
                'type' => 'remote',
                'url' => url($source),
                'name' => $entry['download_name'] ?? basename($source),
                'mime' => $entry['mime'] ?? 'application/octet-stream',
                'extension' => strtolower(pathinfo($entry['download_name'] ?? basename($source), PATHINFO_EXTENSION) ?: 'bin'),
            ];
        }

        if (Storage::disk('public')->exists($source)) {
            $name = $entry['download_name'] ?? basename($source);
            return [
                'type' => 'storage',
                'disk' => 'public',
                'path' => $source,
                'name' => $name,
                'mime' => Storage::disk('public')->mimeType($source),
                'size' => Storage::disk('public')->size($source),
                'extension' => strtolower(pathinfo($name, PATHINFO_EXTENSION) ?: 'bin'),
            ];
        }

        return null;
    }

    private function findEntry(Request $request, string $downloadId): array
    {
        $entry = $this->entries($request)->firstWhere('id', $downloadId);
        abort_if(! $entry, 404, 'Download not found.');

        return $entry;
    }

    private function applyFilters($entries, Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $source = strtolower(trim((string) $request->query('source', 'all')));
        $status = strtolower(trim((string) $request->query('status', 'all')));

        return $entries->filter(function (array $entry) use ($search, $source, $status) {
            if ($search !== '') {
                $haystack = strtolower(implode(' ', Arr::only($entry, ['title', 'category', 'order_number', 'status', 'source_label'])));
                if (! str_contains($haystack, strtolower($search))) {
                    return false;
                }
            }

            if ($source !== 'all' && ! str_contains((string) $entry['source_type'], $source)) {
                return false;
            }

            if ($status !== 'all' && strtolower((string) $entry['status_key']) !== $status && strtolower((string) $entry['status']) !== $status) {
                return false;
            }

            return true;
        })->values();
    }

    private function applySort($entries, string $sort)
    {
        return match ($sort) {
            'oldest' => $entries->sortBy(fn (array $entry) => $entry['created_at'] ?? '')->values(),
            'title' => $entries->sortBy(fn (array $entry) => strtolower((string) $entry['title']))->values(),
            default => $entries->sortByDesc(fn (array $entry) => $entry['created_at'] ?? '')->values(),
        };
    }

    private function streamResolvedFile(array $resolved, string $disposition): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        if ($resolved['type'] === 'storage') {
            return response()->stream(function () use ($resolved) {
                echo Storage::disk($resolved['disk'])->get($resolved['path']);
            }, 200, [
                'Content-Type' => $resolved['mime'],
                'Content-Disposition' => $disposition . '; filename="' . $resolved['name'] . '"',
            ]);
        }

        if ($resolved['type'] === 'remote') {
            try {
                $response = Http::timeout(60)->get($resolved['url']);
            } catch (\Throwable) {
                return response()->json([
                    'success' => false,
                    'message' => 'The file could not be retrieved right now.',
                ], 422);
            }

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The file could not be retrieved right now.',
                ], 422);
            }

            $mime = (string) $response->header('Content-Type', $resolved['mime'] ?? 'application/octet-stream');

            return response()->stream(function () use ($response) {
                echo $response->body();
            }, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => $disposition . '; filename="' . $resolved['name'] . '"',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Download is not available for this file.',
        ], 422);
    }

    private function summary($entries): array
    {
        $bytes = (int) $entries->sum(fn (array $entry) => (int) ($entry['size_bytes'] ?? 0));
        $verifiedCount = (int) $entries->where('status', 'Ready')->count();
        $total = max(1, $entries->count());

        return [
            'available_files' => $entries->count(),
            'storage_used_bytes' => $bytes,
            'storage_used' => $this->humanSize($bytes),
            'verified_assets' => (int) round(($verifiedCount / $total) * 100),
            'verified_assets_count' => $verifiedCount,
        ];
    }

    private function customizationFiles($item): array
    {
        $files = data_get($item->product_options, 'files', []);
        return is_array($files) ? $files : [];
    }

    private function mapProofStatus(string $status): string
    {
        return match (strtolower($status)) {
            'approved' => 'Ready',
            'rejected' => 'Needs Revision',
            default => 'Processing',
        };
    }

    private function previewUrl(string $downloadId): string
    {
        return route('api.v1.ecommerce.downloads.preview', ['downloadId' => $downloadId]);
    }

    private function downloadUrl(string $downloadId): string
    {
        return route('api.v1.ecommerce.downloads.download', ['downloadId' => $downloadId]);
    }

    private function safeFileName(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'download';
        }

        return Str::of($value)->replaceMatches('/[^\w\-\.]+/', '-')->trim('-')->lower()->toString() ?: 'download';
    }

    private function humanSize(?int $bytes): string
    {
        if (! $bytes || $bytes < 0) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = (float) $bytes;
        $i = 0;

        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        return round($value, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }
}
