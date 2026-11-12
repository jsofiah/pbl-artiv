<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Illuminate\Http\UploadedFile;
use App\Services\R2StorageService;
use Tests\TestCase;

#[RunTestsInSeparateProcesses] 
class PemesananControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'username'  => 'customer_test',
            'email'     => 'customer@test.com',
            'full_name' => 'Budi Customer',
            'phone'     => '08123456789',
            'role'      => 'customer',
            'password'  => bcrypt('password'),
        ]);

        $this->product = Product::create([
            'name'      => 'Desain Logo Test',
            'price'     => 100000,
            'is_active' => true,
        ]);
    }

    public function test_customer_bisa_buka_form_pemesanan(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('customer.pemesanan.create', $this->product->id));

        $response->assertStatus(200);
        $response->assertViewHas('product');
    }

    public function test_customer_bisa_submit_ringkasan_pemesanan(): void
    {
        $payload = [
            '_token'          => 'test_token', // <-- TRIK CSRF
            'quantity'        => 1,
            'deadline_option' => 'default',
            'brief_note'      => 'Tolong buatkan logo yang keren dan modern ya min.',
            'reference_links' => ['https://pinterest.com/contoh-logo'],
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['_token' => 'test_token']) // <-- TRIK CSRF
            ->post(route('customer.pemesanan.ringkasan', $this->product->id), $payload);

        $response->assertRedirect(route('customer.pemesanan.ringkasan.show', $this->product->id));
        
        $this->assertEquals(1, session('pemesanan_data')['quantity']);
        $this->assertEquals(100000, session('pemesanan_data')['total_price']);
    }

    public function test_customer_bisa_konfirmasi_dan_order_masuk_database(): void
    {
        $sessionData = [
            'product_id'        => $this->product->id,
            'product_tier_id'   => null,
            'product_tier_name' => $this->product->name,
            'quantity'          => 1,
            'unit_price'        => 100000,
            'subtotal'          => 100000,
            'express_fee_id'    => null,
            'express_fee_name'  => null,
            'express_fee'       => 0,
            'total_price'       => 100000,
            'deadline'          => now()->addDays(7)->toDateTimeString(),
            'brief_note'        => 'Brief desain logo',
            'is_express'        => false,
        ];

        $response = $this->actingAs($this->customer)
            ->withSession([
                '_token' => 'test_token', // <-- TRIK CSRF
                'pemesanan_data' => $sessionData,
                'pemesanan_references' => []
            ])
            ->post(route('customer.pemesanan.konfirmasi', $this->product->id), [
                '_token' => 'test_token' // <-- TRIK CSRF
            ]);

        $order = Order::where('customer_id', $this->customer->id)->first();
        
        $this->assertNotNull($order);
        $response->assertRedirect(route('customer.pemesanan.pembayaran', $order->id));

        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
            'product_id'  => $this->product->id,
            'total_price' => 100000,
            'status'      => 'pending',
        ]);
    }

     public function test_customer_bisa_submit_ringkasan_dengan_file_referensi(): void
    {
        // 1. MOCK SERVICE R2
        // Kita boongin aplikasi: "Kalau ada yang manggil R2StorageService buat upload, 
        // anggap aja sukses dan kembalikan path palsu ini."
        $this->mock(R2StorageService::class, function ($mock) {
            $mock->shouldReceive('uploadPrivate')
                 ->once()
                 ->andReturn('temp/references/fake_file_123.jpg');
        });

        // 2. BIKIN FILE PALSU
        // Bikin file gambar palsu bernama 'desain_referensi.jpg' ukuran 1MB
        $file = UploadedFile::fake()->image('desain_referensi.jpg')->size(1024);

        // 3. SIAPKAN PAYLOAD (Perhatikan reference_files pakai array)
        $payload = [
            '_token' => 'test_token',
            'quantity'        => 1,
            'deadline_option' => 'default',
            'brief_note'      => 'Tolong buatkan logo yang keren sesuai file referensi.',
            'reference_files' => [$file], // <-- Masukkan file palsu ke sini
        ];

        // 4. TEMBAK ENDPOINT
        $response = $this->actingAs($this->customer)
            ->withSession(['_token' => 'test_token'])
            ->post(route('customer.pemesanan.ringkasan', $this->product->id), $payload);

        // 5. ASSERT
        $response->assertRedirect(route('customer.pemesanan.ringkasan.show', $this->product->id));
        
        // Cek apakah session 'pemesanan_references' ada isinya 1 file
        $references = session('pemesanan_references');
        $this->assertCount(1, $references);
        $this->assertEquals('desain_referensi.jpg', $references[0]['file_name']);
        $this->assertEquals('temp/references/fake_file_123.jpg', $references[0]['file_url']);
    }

    public function test_customer_gagal_submit_jika_file_terlalu_besar(): void
    {
        // Bikin file gambar palsu ukuran 25 MB (Padahal limit di controller cuma 20 MB / 20480 KB)
        $file = UploadedFile::fake()->image('file_raksasa.jpg')->size(25000);

        $payload = [
            '_token' => 'test_token',
            'quantity'        => 1,
            'deadline_option' => 'default',
            'brief_note'      => 'Ini brief dengan file yang kegedean.',
            'reference_files' => [$file],
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['_token' => 'test_token'])
            ->post(route('customer.pemesanan.ringkasan', $this->product->id), $payload);

        // Harus balik lagi ke halaman form dengan error validasi di 'reference_files'
        $response->assertSessionHasErrors('reference_files.0'); 
        // Note: index .0 karena file array, error-nya nempel di file pertama
    }

}