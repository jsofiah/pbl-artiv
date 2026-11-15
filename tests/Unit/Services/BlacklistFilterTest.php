<?php

namespace Tests\Unit\Services;

use App\Services\BlacklistFilter;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
/**
 * Tanpa database: BlacklistFilter membaca daftar keyword dari cache
 * 'blacklist_keywords_v2'. Cache itu diisi langsung di test, jadi query ke
 * tabel blacklist_keywords tidak pernah dijalankan.
 */
class BlacklistFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan cache di memori, bukan database/redis
        config(['cache.default' => 'array']);
        cache()->forget('blacklist_keywords_v2');
    }

    private function seedKeywords(array $rows): void
    {
        cache()->put('blacklist_keywords_v2', $rows, 600);
    }

    public function test_blocks_keyword_case_insensitively(): void
    {
        $this->seedKeywords([['keyword' => 'whatsapp', 'type' => 'word']]);

        $result = BlacklistFilter::check('Hubungi WHATSAPP saya ya');

        $this->assertTrue($result['blocked']);
        $this->assertSame('whatsapp', $result['keyword']);
        $this->assertSame('word', $result['type']);
        $this->assertSame('Pesan mengandung kata yang dilarang.', $result['reason']);
    }

    public function test_blocks_link_keyword_with_link_reason(): void
    {
        $this->seedKeywords([['keyword' => 'wa.me', 'type' => 'link']]);

        $result = BlacklistFilter::check('chat aku di wa.me/628123');

        $this->assertTrue($result['blocked']);
        $this->assertSame('link', $result['type']);
        $this->assertSame('Pesan mengandung tautan yang tidak diizinkan.', $result['reason']);
    }

    public function test_blocks_phone_keyword_with_phone_reason(): void
    {
        $this->seedKeywords([['keyword' => '08123456789', 'type' => 'phone']]);

        $result = BlacklistFilter::check('telepon aku di 08123456789');

        $this->assertTrue($result['blocked']);
        $this->assertSame('phone', $result['type']);
        $this->assertSame('Pesan mengandung nomor telepon yang tidak diizinkan.', $result['reason']);
    }

    public function test_blocks_email_keyword_with_email_reason(): void
    {
        $this->seedKeywords([['keyword' => '@gmail.com', 'type' => 'email']]);

        $result = BlacklistFilter::check('kirim ke budi@gmail.com');

        $this->assertTrue($result['blocked']);
        $this->assertSame('email', $result['type']);
        $this->assertSame('Pesan mengandung alamat email yang tidak diizinkan.', $result['reason']);
    }

    public function test_passes_clean_text(): void
    {
        $this->seedKeywords([['keyword' => 'whatsapp', 'type' => 'word']]);

        $result = BlacklistFilter::check('Tolong buatkan logo bertema minimalis modern');

        $this->assertFalse($result['blocked']);
        $this->assertNull($result['keyword']);
        $this->assertNull($result['type']);
        $this->assertNull($result['reason']);
    }

    public function test_skips_entries_with_empty_keyword(): void
    {
        $this->seedKeywords([['keyword' => '', 'type' => 'word']]);

        $result = BlacklistFilter::check('teks apa saja');

        $this->assertFalse($result['blocked']);
    }
}