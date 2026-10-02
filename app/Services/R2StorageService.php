<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class R2StorageService
{
    /**
     * Upload file ke R2 public bucket.
     *
     * @return string Full URL (https://cdn.artiv.com/...)
     */
    public function upload(UploadedFile $file, string $folder = 'uploads'): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = trim($folder, '/') . '/' . $filename;

        Storage::disk('r2-public')->put($path, file_get_contents($file), 'public');

        return $this->url($path);
    }

    /**
     * Hapus file dari R2 public bucket.
     */
    public function delete(string $url): bool
    {
        $path = $this->extractPath($url);
        return Storage::disk('r2-public')->delete($path);
    }

    /**
     * Generate URL publik.
     */
    public function url(string $path): string
    {
        $baseUrl = rtrim(config('filesystems.disks.r2-public.url'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Ambil path dari full URL.
     */
    protected function extractPath(string $url): string
    {
        $baseUrl = rtrim(config('filesystems.disks.r2-public.url'), '/');

        if (str_starts_with($url, $baseUrl)) {
            return ltrim(substr($url, strlen($baseUrl)), '/');
        }

        return $url;
    }

    /**
     * Cek file ada atau tidak.
     */
    public function exists(string $path): bool
    {
        return Storage::disk('r2-public')->exists($path);
    }
}