<?php

namespace Database\Seeders;

use App\Models\BlacklistKeyword;
use Illuminate\Database\Seeder;

class BlacklistKeywordSeeder extends Seeder
{
    public function run(): void
    {
        $keywords = [
            // Link
            ['keyword' => 'http://', 'type' => 'link'],
            ['keyword' => 'https://', 'type' => 'link'],
            ['keyword' => 'wa.me', 'type' => 'link'],
            ['keyword' => 't.me', 'type' => 'link'],
            ['keyword' => 'bit.ly', 'type' => 'link'],

            // Phone
            ['keyword' => '081', 'type' => 'phone'],
            ['keyword' => '082', 'type' => 'phone'],
            ['keyword' => '083', 'type' => 'phone'],
            ['keyword' => '085', 'type' => 'phone'],
            ['keyword' => '087', 'type' => 'phone'],
            ['keyword' => '088', 'type' => 'phone'],
            ['keyword' => '089', 'type' => 'phone'],

            // Email
            ['keyword' => '@gmail.com', 'type' => 'email'],
            ['keyword' => '@yahoo.com', 'type' => 'email'],
            ['keyword' => '@outlook.com', 'type' => 'email'],

            // Word
            ['keyword' => 'whatsapp', 'type' => 'word'],
            ['keyword' => 'telegram', 'type' => 'word'],
            ['keyword' => 'line', 'type' => 'word'],
            ['keyword' => 'email saya', 'type' => 'word'],
            ['keyword' => 'hubungi saya', 'type' => 'word'],
            ['keyword' => 'kontak saya', 'type' => 'word'],
        ];

        foreach ($keywords as $kw) {
            BlacklistKeyword::create([
                'keyword' => $kw['keyword'],
                'type' => $kw['type'],
                'is_active' => true,
            ]);
        }
    }
}