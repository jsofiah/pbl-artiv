<?php

namespace Tests\Unit\Services;

use App\Services\R2StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class R2StorageServiceTest extends TestCase
{
    private const PUBLIC_BASE_URL = 'https://pub.example.test';

    private R2StorageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // url() dan extractPath() membaca base URL dari config
        config(['filesystems.disks.r2-public.url' => self::PUBLIC_BASE_URL]);

        // Disk palsu (lokal) supaya tidak menyentuh Cloudflare R2 sungguhan
        Storage::fake(R2StorageService::DISK_PRIVATE);
        Storage::fake(R2StorageService::DISK_PUBLIC);

        $this->service = new R2StorageService();
    }

    private function fakeImage(string $name = 'ref.jpg'): UploadedFile
    {
        // create() tidak butuh ekstensi GD, tidak seperti image()
        return UploadedFile::fake()->create($name, 10, 'image/jpeg');
    }

    public function test_upload_private_returns_relative_path_and_stores_file(): void
    {
        $path = $this->service->upload($this->fakeImage(), 'references');

        $this->assertStringStartsWith('references/', $path);
        $this->assertStringEndsWith('.jpg', $path);
        $this->assertStringNotContainsString('http', $path);
        Storage::disk(R2StorageService::DISK_PRIVATE)->assertExists($path);
    }

    public function test_upload_public_returns_full_url_and_stores_file(): void
    {
        $url = $this->service->upload(
            $this->fakeImage('thumb.jpg'),
            'thumbnails',
            R2StorageService::DISK_PUBLIC
        );

        $this->assertStringStartsWith(self::PUBLIC_BASE_URL . '/thumbnails/', $url);

        $path = ltrim(substr($url, strlen(self::PUBLIC_BASE_URL)), '/');
        Storage::disk(R2StorageService::DISK_PUBLIC)->assertExists($path);
    }

    public function test_delete_accepts_full_url_and_removes_file(): void
    {
        $url = $this->service->upload(
            $this->fakeImage('thumb.jpg'),
            'thumbnails',
            R2StorageService::DISK_PUBLIC
        );
        $path = ltrim(substr($url, strlen(self::PUBLIC_BASE_URL)), '/');

        $deleted = $this->service->delete($url, R2StorageService::DISK_PUBLIC);

        $this->assertTrue($deleted);
        Storage::disk(R2StorageService::DISK_PUBLIC)->assertMissing($path);
    }

    public function test_temporary_url_generates_signed_url_for_private_file(): void
    {
        $path = $this->service->upload($this->fakeImage('bukti.jpg'), 'payments');

        // Disk lokal palsu tidak bisa membuat signed URL, jadi pasang pembuat URL tiruan
        Storage::disk(R2StorageService::DISK_PRIVATE)->buildTemporaryUrlsUsing(
            fn ($path, $expiration, $options) =>
                'https://signed.example.test/' . $path . '?expires=' . $expiration->getTimestamp()
        );

        $before = now()->addMinutes(10)->getTimestamp();
        $url = $this->service->temporaryUrl($path, 10);
        $after = now()->addMinutes(10)->getTimestamp();

        $this->assertStringContainsString($path, $url);

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $this->assertGreaterThanOrEqual($before, (int) $query['expires']);
        $this->assertLessThanOrEqual($after, (int) $query['expires']);
    }
}