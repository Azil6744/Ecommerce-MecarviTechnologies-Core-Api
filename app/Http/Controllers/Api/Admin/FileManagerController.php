<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FileManagerController extends Controller
{
    public function dashboard()
    {
        $baseDirectory = $this->baseDirectory();
        $dashboard = $this->buildDashboardData($baseDirectory);

        return response()->json([
            'success' => true,
            'data' => $dashboard,
        ]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'path' => 'nullable|string',
        ]);

        $path = $this->normalizePath($validated['path'] ?? '/');
        $directory = $this->resolveDirectoryPath($path, create: true);

        $items = collect(File::exists($directory) ? File::files($directory) : [])
            ->map(fn ($file) => $this->mapFile($file, $path));

        $folders = collect(File::exists($directory) ? File::directories($directory) : [])
            ->map(fn ($folderPath) => $this->mapFolder($folderPath, $path));

        return response()->json([
            'success' => true,
            'data' => [
                'files' => $folders
                    ->concat($items)
                    ->sortBy(fn (array $item) => [$item['type'] !== 'folder', strtolower($item['name'])])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function verifyPassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
            'folder_path' => 'nullable|string',
        ]);

        $folderPath = $this->normalizePath($validated['folder_path'] ?? '/Private');
        if (! $this->isPasswordProtectedPath($folderPath)) {
            return response()->json([
                'success' => true,
                'message' => 'Folder is not password protected.',
            ]);
        }

        $expectedPassword = (string) env('FILE_MANAGER_FOLDER_PASSWORD', 'mecarvi123');

        if (! hash_equals($expectedPassword, $validated['password'])) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password verified successfully.',
        ]);
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'folder_path' => 'nullable|string',
            'files' => 'required|array|min:1',
            'files.*' => 'file|max:10240',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $path = $this->normalizePath((string) $request->input('folder_path', '/'));
        $directory = $this->resolveDirectoryPath($path, create: true);
        $uploadedFiles = [];

        foreach ((array) $request->file('files', []) as $file) {
            $originalName = $file->getClientOriginalName() ?: 'upload.bin';
            $filename = $this->uniqueFilename($directory, $originalName);
            $file->move($directory, $filename);
            $uploadedFiles[] = $this->mapFile($directory.DIRECTORY_SEPARATOR.$filename, $path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Files uploaded successfully.',
            'data' => [
                'files' => $uploadedFiles,
            ],
        ], 201);
    }

    private function baseDirectory(): string
    {
        $directory = storage_path('app/file-manager');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return $directory;
    }

    private function buildDashboardData(string $baseDirectory): array
    {
        $allFiles = collect(File::allFiles($baseDirectory));
        $allDirectories = collect(File::directories($baseDirectory));
        $categorizedFiles = [
            'Images' => $allFiles->filter(fn (\SplFileInfo $file) => str_starts_with((string) File::mimeType($file->getPathname()), 'image/')),
            'Documents' => $allFiles->filter(fn (\SplFileInfo $file) => in_array(strtolower($file->getExtension()), ['pdf', 'doc', 'docx', 'txt', 'rtf'], true)),
            'Audio' => $allFiles->filter(fn (\SplFileInfo $file) => str_starts_with((string) File::mimeType($file->getPathname()), 'audio/')),
            'Apps' => $allFiles->filter(fn (\SplFileInfo $file) => in_array(strtolower($file->getExtension()), ['exe', 'msi', 'apk', 'app'], true)),
            'Video' => $allFiles->filter(fn (\SplFileInfo $file) => str_starts_with((string) File::mimeType($file->getPathname()), 'video/')),
            'Downloads' => $allFiles->filter(fn (\SplFileInfo $file) => str_contains(strtolower(str_replace('\\', '/', $file->getPathname())), '/downloads/')),
        ];

        $totalBytes = max(1, $allFiles->sum(fn (\SplFileInfo $file) => $file->getSize()));
        $storageCategories = collect([
            ['name' => 'Media', 'source' => $categorizedFiles['Images']->concat($categorizedFiles['Video'])->concat($categorizedFiles['Audio']), 'color' => 'purple'],
            ['name' => 'Downloads', 'source' => $categorizedFiles['Downloads'], 'color' => 'orange'],
            ['name' => 'Apps', 'source' => $categorizedFiles['Apps'], 'color' => 'green'],
            ['name' => 'Documents', 'source' => $categorizedFiles['Documents'], 'color' => 'yellow'],
        ])->map(function (array $category) use ($totalBytes) {
            $sizeBytes = $category['source']->sum(fn (\SplFileInfo $file) => $file->getSize());

            return [
                'name' => $category['name'],
                'fileCount' => $category['source']->count(),
                'size' => $this->formatBytes($sizeBytes),
                'percentage' => (int) round(($sizeBytes / $totalBytes) * 100),
                'color' => $category['color'],
            ];
        })->values()->all();

        $quickAccess = [
            ['name' => 'Images', 'color' => 'purple'],
            ['name' => 'Documents', 'color' => 'orange'],
            ['name' => 'Audio', 'color' => 'green'],
            ['name' => 'Apps', 'color' => 'yellow'],
            ['name' => 'Video', 'color' => 'blue'],
        ];

        $quickAccess = collect($quickAccess)->map(function (array $category) use ($categorizedFiles) {
            $files = $categorizedFiles[$category['name']] ?? collect();

            return [
                'name' => $category['name'],
                'fileCount' => $files->count(),
                'totalSize' => $this->formatBytes($files->sum(fn (\SplFileInfo $file) => $file->getSize())),
                'color' => $category['color'],
            ];
        })->all();

        $folders = $allDirectories
            ->take(6)
            ->map(function (string $directoryPath) {
                $relativePath = str_replace('\\', '/', str_replace($this->baseDirectory(), '', $directoryPath));
                $relativePath = '/'.trim($relativePath, '/');
                $fileCount = collect(File::allFiles($directoryPath))->count();

                return [
                    'id' => abs(crc32($relativePath)),
                    'name' => basename($directoryPath),
                    'fileCount' => $fileCount,
                    'color' => '#3B82F6',
                    'users' => [],
                ];
            })
            ->values()
            ->all();

        $recentFiles = $allFiles
            ->sortByDesc(fn (\SplFileInfo $file) => $file->getMTime())
            ->take(8)
            ->map(function (\SplFileInfo $file) {
                $extension = strtolower($file->getExtension());

                return [
                    'id' => abs(crc32($file->getPathname())),
                    'name' => $file->getFilename(),
                    'category' => strtoupper($extension ?: 'FILE'),
                    'size' => $this->formatBytes($file->getSize()),
                    'dateModified' => date('d M Y', $file->getMTime()),
                    'fileType' => $extension ?: 'file',
                    'iconColor' => $this->iconColorForExtension($extension),
                ];
            })
            ->values()
            ->all();

        return [
            'quickAccess' => $quickAccess,
            'storageUsage' => [
                'totalUsed' => round($allFiles->sum(fn (\SplFileInfo $file) => $file->getSize()) / 1073741824, 2),
                'categories' => $storageCategories,
            ],
            'folders' => $folders,
            'recentFiles' => $recentFiles,
        ];
    }

    private function normalizePath(?string $path): string
    {
        $normalized = '/'.trim(str_replace('\\', '/', (string) $path), '/');
        $normalized = preg_replace('#/+#', '/', $normalized) ?: '/';

        if (str_contains($normalized, '..')) {
            abort(422, 'Invalid file manager path.');
        }

        return $normalized === '//' ? '/' : $normalized;
    }

    private function resolveDirectoryPath(string $path, bool $create = false): string
    {
        $baseDirectory = $this->baseDirectory();
        $relativePath = ltrim($path, '/');
        $directory = $relativePath === ''
            ? $baseDirectory
            : $baseDirectory.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if ($create && ! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return $directory;
    }

    private function mapFolder(string $folderPath, string $parentPath): array
    {
        $folderName = basename($folderPath);
        $relativePath = rtrim($parentPath, '/').'/'.$folderName;
        $relativePath = $relativePath === '' ? '/' : $relativePath;

        return [
            'id' => abs(crc32($relativePath)),
            'name' => $folderName,
            'type' => 'folder',
            'path' => $relativePath,
            'isPasswordProtected' => $this->isPasswordProtectedPath($relativePath),
            'createdAt' => now()->toISOString(),
            'updatedAt' => now()->toISOString(),
        ];
    }

    private function mapFile(string|\SplFileInfo $file, string $parentPath): array
    {
        $fileInfo = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);
        $relativePath = rtrim($parentPath, '/').'/'.$fileInfo->getFilename();

        return [
            'id' => abs(crc32($relativePath)),
            'name' => $fileInfo->getFilename(),
            'type' => 'file',
            'path' => $relativePath,
            'size' => $fileInfo->getSize(),
            'mimeType' => File::mimeType($fileInfo->getPathname()) ?: 'application/octet-stream',
            'createdAt' => now()->toISOString(),
            'updatedAt' => now()->toISOString(),
        ];
    }

    private function isPasswordProtectedPath(string $path): bool
    {
        $segments = collect(explode('/', trim($path, '/')))
            ->filter()
            ->map(fn (string $segment) => strtolower($segment));

        return $segments->contains(fn (string $segment) => in_array($segment, ['private', 'secure', 'protected'], true));
    }

    private function uniqueFilename(string $directory, string $originalName): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $candidate = $originalName;
        $suffix = 1;

        while (File::exists($directory.DIRECTORY_SEPARATOR.$candidate)) {
            $candidate = $extension !== ''
                ? sprintf('%s-%d.%s', $name, $suffix, $extension)
                : sprintf('%s-%d', $name, $suffix);
            $suffix++;
        }

        return $candidate;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return round($value, $power === 0 ? 0 : 1).' '.$units[$power];
    }

    private function iconColorForExtension(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'orange',
            'doc', 'docx', 'txt', 'rtf' => 'purple',
            'xls', 'xlsx', 'csv' => 'green',
            'mp4', 'mov', 'avi', 'mkv' => 'blue',
            default => 'purple',
        };
    }
}
