<?php

namespace App\Helpers;

class R2Helper
{
    /**
     * Generate public URL untuk file di R2 public bucket.
     *
     * @param string|null $path Path relatif, contoh: products/sticker.png
     * @return string|null
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Kalau sudah full URL (http/https), langsung kembalikan
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $baseUrl = rtrim(config('filesystems.disks.r2-public.url'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}