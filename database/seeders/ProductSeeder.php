<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Sticker',
                'description' => "Sticker custom (die cut) bisa jadi media branding yang murah meriah tapi berkesan. Kami buatkan desain sticker yang unik, lucu, atau elegan sesuai kebutuhan — siap untuk dicetak.",
                'thumbnail_url' => 'products/sticker.png',
                'is_active' => true,
                'price' => 10000,
            ],
            [
                'name' => 'Scrapbook',
                'description' => 'Mau bikin scrapbook yang penuh kenangan dan estetik? Kami bantu desain layout scrapbook sesuai tema, biar setiap halaman terlihat rapi, menarik, dan punya cerita.',
                'thumbnail_url' => 'products/scrapbook.png',
                'is_active' => true,
                'price' => 20000,
            ],
            [
                'name' => 'Poster',
                'description' => 'Poster yang bagus bisa langsung menarik perhatian orang. Kami buatkan desain poster yang clean, informatif, dan visualnya kuat — cocok untuk event, promosi, pengumuman, atau kebutuhan kampanye.',
                'thumbnail_url' => 'products/poster.png',
                'is_active' => true,
                'price' => null,
            ],
            [
                'name' => 'Postingan Sosial Media',
                'description' => 'Butuh feed Instagram atau konten sosmed yang eye-catching dan konsisten dengan brand? Kami bantu bikin desain postingan yang menarik, rapi, dan siap upload. Cocok untuk promosi produk, quotes, announcement, sampai konten edukasi.',
                'thumbnail_url' => 'products/sosmed.png',
                'is_active' => true,
                'price' => null,
            ],
            [
                'name' => 'Logo',
                'description' => 'Logo adalah wajah brand kamu. Kami buatkan logo yang menarik, memorable, dan sesuai karakter usaha atau komunitas kamu. Hasil akhir siap dipakai di semua media.',
                'thumbnail_url' => 'products/logo.png',
                'is_active' => true,
                'price' => 50000,
            ],
            [
                'name' => 'Presentasi (PowerPoint)',
                'description' => 'Presentasi yang membosankan bisa bikin audiens ngantuk. Kami bantu ubah slide kamu jadi lebih profesional, rapi, dan visualnya enak dilihat — biar pesan yang disampaikan lebih berkesan.',
                'thumbnail_url' => 'products/ppt.png',
                'is_active' => true,
                'price' => null,
            ],
            [
                'name' => 'Twibbon',
                'description' => 'Mau frame foto digital yang kece buat event, kampanye, atau ospek? Twibbon custom dari kami dirancang biar foto kamu tetap menonjol sambil tetap menampilkan identitas brand atau acara dengan jelas.',
                'thumbnail_url' => 'products/twibbon.png',
                'is_active' => true,
                'price' => null,
            ],
            [
                'name' => 'Brosur / Leaflet',
                'description' => 'Butuh media cetak yang ringkas tapi tetap informatif? Brosur kami didesain biar mudah dibaca, visualnya menarik, dan mampu menyampaikan informasi produk atau jasa dengan efektif.',
                'thumbnail_url' => 'products/brosur.png',
                'is_active' => true,
                'price' => null,
            ],
            [
                'name' => 'Banner',
                'description' => 'Dari X-Banner sampai spanduk, kami siap bantu bikin desain yang proporsional, jelas dibaca dari jauh, dan tetap estetik. Cocok untuk event, wisuda, toko, pameran, maupun promosi outdoor.',
                'thumbnail_url' => 'products/banner.png',
                'is_active' => true,
                'price' => null,
            ],
            [
                'name' => 'Infografis',
                'description' => 'Punya data atau informasi penting tapi susah dijelaskan? Infografis kami bikin biar konten kamu lebih gampang dipahami, menarik, dan tetap terlihat profesional.',
                'thumbnail_url' => 'products/infografis.png',
                'is_active' => true,
                'price' => 30000,
            ],
            [
                'name' => 'UI Design',
                'description' => 'Butuh tampilan aplikasi atau website yang rapi, modern, dan user-friendly? Kami bantu buatkan desain antarmuka (UI) yang menarik dan nyaman digunakan, baik untuk versi mobile maupun web.',
                'thumbnail_url' => 'products/ui.png',
                'is_active' => true,
                'price' => null,
            ],
            [
                'name' => 'Pin Button',
                'description' => 'Pin button custom cocok buat merchandise, event, atau komunitas. Kami bantu buatkan desain pin yang menarik dan mewakili identitas kamu.',
                'thumbnail_url' => 'products/pin.png',
                'is_active' => true,
                'price' => 20000,
            ],
            [
                'name' => 'Keychain',
                'description' => 'Gantungan kunci custom yang unik selalu jadi favorit. Kami desain keychain sesuai tema, logo, atau karakter yang kamu inginkan, biar hasilnya personal dan berkesan.',
                'thumbnail_url' => 'products/keychain.png',
                'is_active' => true,
                'price' => 15000,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}