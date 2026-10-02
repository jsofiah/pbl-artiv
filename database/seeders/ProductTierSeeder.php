<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductTier;
use Illuminate\Database\Seeder;

class ProductTierSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping: nama product → daftar tier
        $tiers = [
            'UI Design' => [
                [
                    'name' => 'Web',
                    'price' => 45000,
                    'description' => 'Desain tampilan website (per halaman).',
                    'thumbnail_url' => 'product_tiers/ui_web.png',
                ],
                [
                    'name' => 'Mobile',
                    'price' => 35000,
                    'description' => 'Desain tampilan aplikasi/mobile app (per halaman).',
                    'thumbnail_url' => 'product_tiers/ui_mobile.png',
                ],
            ],
            'Twibbon' => [
                [
                    'name' => 'Twibbon - Simple',
                    'price' => 15000,
                    'description' => 'Twibbon sederhana dengan frame dasar + logo/teks.',
                    'thumbnail_url' => 'product_tiers/twibbon_simple.png',
                ],
                [
                    'name' => 'Twibbon - Medium',
                    'price' => 25000,
                    'description' => 'Twibbon custom dengan desain lebih menarik dan elemen tambahan.',
                    'thumbnail_url' => 'product_tiers/twibbon_medium.png',
                ],
                [
                    'name' => 'Twibbon - Full',
                    'price' => 30000,
                    'description' => 'Twibbon full custom, detail tinggi, efek visual premium.',
                    'thumbnail_url' => 'product_tiers/twibbon_full.png',
                ],
            ],
            'Presentasi (PowerPoint)' => [
                [
                    'name' => 'Presentasi - Simple',
                    'price' => 4000,
                    'description' => 'Desain slide sederhana, rapi, dan profesional dasar (per slide)',
                    'thumbnail_url' => 'product_tiers/ppt_simple.png',
                ],
                [
                    'name' => 'Presentasi - Medium',
                    'price' => 8000,
                    'description' => 'Desain slide custom dengan visual yang lebih menarik + icon/grafik (per slide).',
                    'thumbnail_url' => 'product_tiers/ppt_medium.png',
                ],
                [
                    'name' => 'Presentasi - Full',
                    'price' => 12000,
                    'description' => 'Desain slide full custom, animasi ringan, visual premium, dan layout mewah (per slide).',
                    'thumbnail_url' => 'product_tiers/ppt_full.png',
                ],
            ],
            'Postingan Sosial Media' => [
                [
                    'name' => 'Postingan - Simple',
                    'price' => 12000,
                    'description' => 'Desain sederhana berbasis template, edit warna/teks ringan, cocok untuk kebutuhan cepat (per slide).',
                    'thumbnail_url' => 'product_tiers/sosmed_simple.png',
                ],
                [
                    'name' => 'Postingan - Medium',
                    'price' => 20000,
                    'description' => 'Desain custom dengan layout yang lebih menarik, elemen visual bagus (per slide).',
                    'thumbnail_url' => 'product_tiers/sosmed_medium.png',
                ],
                [
                    'name' => 'Postingan - Full',
                    'price' => 30000,
                    'description' => 'Desain custom penuh, detail tinggi, ilustrasi/efek premium (per slide).',
                    'thumbnail_url' => 'product_tiers/sosmed_full.png',
                ],
            ],
            'Poster' => [
                [
                    'name' => 'Poster - Simple',
                    'price' => 18000,
                    'description' => 'Poster sederhana, layout clean, edit ringan.',
                    'thumbnail_url' => 'product_tiers/poster_simple.png',
                ],
                [
                    'name' => 'Poster - Medium',
                    'price' => 28000,
                    'description' => 'Poster custom dengan komposisi visual yang lebih bagus dan eye-catching.',
                    'thumbnail_url' => 'product_tiers/poster_medium.png',
                ],
                [
                    'name' => 'Poster - Full',
                    'price' => 40000,
                    'description' => 'Poster full custom, detail tinggi, ilustrasi/efek premium, siap cetak.',
                    'thumbnail_url' => 'product_tiers/poster_full.png',
                ],
            ],
            'Brosur / Leaflet' => [
                [
                    'name' => 'Brosur - 1 Sisi',
                    'price' => 15000,
                    'description' => 'Desain brosur 1 sisi, clean dan informatif.',
                    'thumbnail_url' => 'product_tiers/brosur_1sisi.png',
                ],
                [
                    'name' => 'Brosur - 2 Sisi',
                    'price' => 25000,
                    'description' => 'Desain brosur bolak-balik (depan-belakang).',
                    'thumbnail_url' => 'product_tiers/brosur_2sisi.png',
                ],
                [
                    'name' => 'Brosur - 3 Sisi',
                    'price' => 30000,
                    'description' => 'Desain brosur lipat 3 sisi (trifold) dengan layout yang rapi.',
                    'thumbnail_url' => 'product_tiers/brosur_3sisi.png',
                ],
            ],
            'Banner' => [
                [
                    'name' => 'Banner - Simple',
                    'price' => 20000,
                    'description' => 'Banner sederhana, layout basic, cocok untuk kebutuhan cepat.',
                    'thumbnail_url' => 'product_tiers/banner_simple.png',
                ],
                [
                    'name' => 'Banner - Medium',
                    'price' => 30000,
                    'description' => 'Banner custom dengan desain lebih menarik dan proporsi yang pas.',
                    'thumbnail_url' => 'product_tiers/banner_medium.png',
                ],
                [
                    'name' => 'Banner - Full',
                    'price' => 40000,
                    'description' => 'Banner full custom, detail tinggi, visual premium, siap cetak ukuran besar.',
                    'thumbnail_url' => 'product_tiers/banner_full.png',
                ],
            ],
        ];

        foreach ($tiers as $productName => $tierList) {
            $product = Product::where('name', $productName)->first();

            if (!$product) {
                $this->command->warn("Product '{$productName}' tidak ditemukan, skip.");
                continue;
            }

            foreach ($tierList as $tier) {
                ProductTier::create([
                    'product_id' => $product->id,
                    'name' => $tier['name'],
                    'price' => $tier['price'],
                    'description' => $tier['description'],
                    'thumbnail_url' => $tier['thumbnail_url'],
                    'is_active' => true,
                ]);
            }
        }
    }
}