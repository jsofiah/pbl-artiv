<?php

namespace Database\Seeders;

use App\Models\ExpressFee;
use Illuminate\Database\Seeder;

class ExpressFeeSeeder extends Seeder
{
    public function run(): void
    {
        $fees = [
            [
                'name' => '2 Hari',
                'fee' => 60000,
                'is_active' => true,
            ],
            [
                'name' => '3 Hari',
                'fee' => 50000,
                'is_active' => true,
            ],
            [
                'name' => '4 Hari',
                'fee' => 40000,
                'is_active' => true,
            ],
            [
                'name' => '5 Hari',
                'fee' => 30000,
                'is_active' => true,
            ],
            [
                'name' => '6 Hari',
                'fee' => 20000,
                'is_active' => true,
            ],
        ];

        foreach ($fees as $fee) {
            ExpressFee::create($fee);
        }
    }
}