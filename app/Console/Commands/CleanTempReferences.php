<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanTempReferences extends Command
{
    protected $signature = 'r2:clean-temp-references';
    protected $description = 'Hapus file referensi lama di folder temp/references/ (>24 jam)';

    public function handle()
    {
        $disk = Storage::disk('r2');
        $files = $disk->files('temp/references');
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = $disk->lastModified($file);
            
            // Hapus kalau lebih dari 24 jam
            if (Carbon::createFromTimestamp($lastModified)->diffInHours(now()) > 24) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Berhasil hapus {$deleted} file lama.");
    }
}