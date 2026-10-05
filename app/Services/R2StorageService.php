<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class R2StorageService
{
    // ============================================================
    // PUBLIC — Bucket artiv-media-public (untuk produk, thumbnail, dll)
    // ============================================================
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
     * Generate URL publik (untuk file di r2-public).
     */
    public function url(string $path): string
    {
        $baseUrl = rtrim(config('filesystems.disks.r2-public.url'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Ambil path dari full URL (untuk file public).
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
     * Cek file ada atau tidak (di r2-public).
     */
    public function exists(string $path): bool
    {
        return Storage::disk('r2-public')->exists($path);
    }

    // ============================================================
    // PRIVATE — Bucket artiv-media-prod (untuk referensi, bukti bayar, hasil desain)
    // ============================================================
    public function uploadPrivate(UploadedFile $file, string $folder = 'uploads'): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = trim($folder, '/') . '/' . $filename;

        Storage::disk('r2')->put($path, file_get_contents($file));

        return $path;   // ← return PATH, bukan URL
    }

    /**
     * Hapus file PRIVATE dari R2.
     */
    public function deletePrivate(string $path): bool
    {
        return Storage::disk('r2')->delete($path);
    }

    /**
     * Generate temporary URL untuk file PRIVATE.
     * URL berlaku terbatas (default 60 menit) — cocok untuk download aman.
     */
    public function temporaryUrlPrivate(string $path, int $minutes = 60): string
    {
        return Storage::disk('r2')->temporaryUrl($path, now()->addMinutes($minutes));
    }

    /**
     * Cek file PRIVATE ada atau tidak (di r2).
     */
    public function existsPrivate(string $path): bool
    {
        return Storage::disk('r2')->exists($path);
    }

        /**
     * Pindahkan file PRIVATE dari satu folder ke folder lain di R2.
     */
    public function movePrivate(string $fromPath, string $toPath): bool
    {
        $disk = Storage::disk('r2');
        
        if (!$disk->exists($fromPath)) {
            return false;
        }
        
        $disk->copy($fromPath, $toPath);
        $disk->delete($fromPath);
        
        return true;
    }
}