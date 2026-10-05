<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class R2StorageService
{
    public const DISK_PRIVATE = 'r2';
    public const DISK_PUBLIC = 'r2-public';

    /**
     * Upload file ke R2.
     *
     * @param  UploadedFile  $file
     * @param  string        $folder   Folder di dalam bucket, mis. 'references'
     * @param  string        $disk     self::DISK_PRIVATE (default) atau self::DISK_PUBLIC
     * @return string  Full URL untuk file public, path relatif untuk file private
     */
    public function upload(
        UploadedFile $file,
        string $folder = 'uploads',
        string $disk = self::DISK_PRIVATE
    ): string {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = trim($folder, '/') . '/' . $filename;

        $visibility = $disk === self::DISK_PUBLIC ? 'public' : 'private';

        Storage::disk($disk)->put($path, file_get_contents($file), [
            'visibility' => $visibility,
        ]);

        // File public → langsung return full URL
        if ($disk === self::DISK_PUBLIC) {
            return $this->url($path, self::DISK_PUBLIC);
        }

        // File private → simpan path relatif saja.
        // Nanti diakses via temporaryUrl() / download()
        return $path;
    }

    /**
     * Hapus file dari R2.
     *
     * @param  string  $pathOrUrl  Bisa path relatif atau full URL
     * @param  string  $disk
     */
    public function delete(string $pathOrUrl, string $disk = self::DISK_PRIVATE): bool
    {
        $path = $this->extractPath($pathOrUrl, $disk);
        return Storage::disk($disk)->delete($path);
    }

    /**
     * Generate URL publik — hanya untuk file di bucket public.
     */
    public function url(string $path, string $disk = self::DISK_PUBLIC): string
    {
        $baseUrl = rtrim((string) config("filesystems.disks.{$disk}.url"), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Signed URL sementara untuk file private.
     *
     * @param  string  $pathOrUrl  Path relatif atau full URL
     * @param  int     $minutes    Masa berlaku (default 5 menit)
     * @param  string  $disk
     * @param  array   $options    Opsi tambahan S3, mis. ResponseContentDisposition
     */
    public function temporaryUrl(
        string $pathOrUrl,
        int $minutes = 5,
        string $disk = self::DISK_PRIVATE,
        array $options = []
    ): string {
        $path = $this->extractPath($pathOrUrl, $disk);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        return $storage->temporaryUrl(
            $path,
            now()->addMinutes($minutes),
            $options
        );
    }

    public function download(
        string $pathOrUrl,
        string $filename,
        string $disk = self::DISK_PRIVATE
    ): \Symfony\Component\HttpFoundation\StreamedResponse {
        $path = $this->extractPath($pathOrUrl, $disk);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        if (!$storage->exists($path)) {
            abort(404, "File tidak ditemukan: {$path}");
        }

        return $storage->download($path, $filename);
    }

    public function exists(string $pathOrUrl, string $disk = self::DISK_PRIVATE): bool
    {
        $path = $this->extractPath($pathOrUrl, $disk);
        return Storage::disk($disk)->exists($path);
    }

    protected function extractPath(string $pathOrUrl, string $disk): string
    {
        $baseUrl = rtrim((string) config("filesystems.disks.{$disk}.url"), '/');

        if ($baseUrl && str_starts_with($pathOrUrl, $baseUrl)) {
            return ltrim(substr($pathOrUrl, strlen($baseUrl)), '/');
        }

        if (preg_match('#^https?://[^/]+/(.+)$#', $pathOrUrl, $m)) {
            return ltrim($m[1], '/');
        }

        return ltrim($pathOrUrl, '/');
    }
}