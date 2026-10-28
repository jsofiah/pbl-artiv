<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ProductTierSeeder;
use Database\Seeders\ExpressFeeSeeder;
use Database\Seeders\RevisionFeeSeeder;
use Database\Seeders\BlacklistKeywordSeeder;
use Database\Seeders\UserSeeder;

return new class extends Migration
{
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => ProductSeeder::class,
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => ProductTierSeeder::class,
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => ExpressFeeSeeder::class,
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => RevisionFeeSeeder::class,
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => BlacklistKeywordSeeder::class,
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => UserSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        // Opsional: hapus data jika rollback.
        // Biasanya dikosongkan karena data master tidak perlu dihapus.
    }
};