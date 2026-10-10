<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Master data
            ProductSeeder::class,
            ProductTierSeeder::class,
            ExpressFeeSeeder::class,
            RevisionFeeSeeder::class,
            BlacklistKeywordSeeder::class,
            UserSeeder::class,
        ]);
    }
}