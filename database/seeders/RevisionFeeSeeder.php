<?php

namespace Database\Seeders;

use App\Models\RevisionFee;
use Illuminate\Database\Seeder;

class RevisionFeeSeeder extends Seeder
{
    public function run(): void
    {
        RevisionFee::create([
            'name' => 'Revisi Tambahan',
            'fee' => 15000,
            'is_active' => true,
        ]);
    }
}