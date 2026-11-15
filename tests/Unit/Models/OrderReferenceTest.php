<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderReference;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReferenceTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: isFile() true saat type file, false saat link
    // ============================================================
    public function test_is_file_returns_true_for_file_type(): void
    {
        $fileRef = $this->createReference([
            'type' => OrderReference::TYPE_FILE,
            'file_url' => 'references/design.pdf',
            'file_name' => 'design.pdf',
            'file_size' => 2048,
            'mime_type' => 'application/pdf',
            'external_url' => null,
        ]);

        $this->assertTrue($fileRef->isFile());
        $this->assertFalse($fileRef->isLink());

        $linkRef = $this->createReference([
            'type' => OrderReference::TYPE_LINK,
            'file_url' => null,
            'file_name' => null,
            'file_size' => null,
            'mime_type' => null,
            'external_url' => 'https://example.com/design',
        ]);

        $this->assertFalse($linkRef->isFile());
        $this->assertTrue($linkRef->isLink());
    }

    // ============================================================
    // Test #2: Relasi order() mengembalikan Order yang benar
    // ============================================================
    public function test_order_relation_returns_correct_order(): void
    {
        $reference = $this->createReference();

        $relatedOrder = $reference->order;

        $this->assertInstanceOf(Order::class, $relatedOrder);
        $this->assertEquals($reference->order_id, $relatedOrder->id);
    }

    // ============================================================
    // Test #3: Cast file_size ke integer
    // ============================================================
    public function test_file_size_is_cast_to_integer(): void
    {
        $reference = $this->createReference([
            'file_size' => '4096', // string
        ]);

        $reference->refresh();

        $this->assertIsInt($reference->file_size);
        $this->assertEquals(4096, $reference->file_size);
    }

    // ============================================================
    // Test #4: Mass assignment mengisi semua kolom $fillable
    // ============================================================
    public function test_mass_assignment_fills_all_fillable_columns(): void
    {
        $reference = $this->createReference();

        $this->assertNotNull($reference->order_id);
        $this->assertEquals(OrderReference::TYPE_FILE, $reference->type);
        $this->assertEquals('references/test.pdf', $reference->file_url);
        $this->assertEquals('test.pdf', $reference->file_name);
        $this->assertEquals(1024, $reference->file_size);
        $this->assertEquals('application/pdf', $reference->mime_type);
        $this->assertNull($reference->external_url);
        $this->assertEquals('Contoh referensi', $reference->note);
    }

    // ============================================================
    // Test #5: UUID auto-generate
    // ============================================================
    public function test_id_is_uuid_auto_generated(): void
    {
        $reference = $this->createReference();

        $this->assertNotNull($reference->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $reference->id
        );
    }

    // ============================================================
    // Test #6: Kolom nullable boleh null (file_url, external_url, dll)
    // ============================================================
    public function test_nullable_columns_can_be_null(): void
    {
        $order = $this->createOrder();

        $reference = OrderReference::create([
            'order_id' => $order->id,
            'type' => OrderReference::TYPE_LINK,
            'file_url' => null,
            'file_name' => null,
            'file_size' => null,
            'mime_type' => null,
            'external_url' => 'https://example.com',
            'note' => null,
        ]);

        $this->assertNull($reference->file_url);
        $this->assertNull($reference->file_name);
        $this->assertNull($reference->file_size);
        $this->assertNull($reference->mime_type);
        $this->assertNull($reference->note);
        $this->assertNotNull($reference->external_url);
    }

    // ============================================================
    // HELPER
    // ============================================================
    private function createUser(): User
    {
        return User::create([
            'username'  => 'user_' . uniqid(),
            'email'     => 'user_' . uniqid() . '@test.com',
            'full_name' => 'Test User',
            'phone'     => '08123456789',
            'role'      => User::ROLE_CUSTOMER,
            'password'  => bcrypt('password'),
        ]);
    }

    private function createOrder(): Order
    {
        $customer = $this->createUser();
        $product = Product::create([
            'name'          => 'Test Product ' . uniqid(),
            'description'   => 'Deskripsi test',
            'thumbnail_url' => 'products/test.png',
            'price'         => 50000,
            'is_active'     => true,
        ]);

        return Order::create([
            'order_code'  => 'ORD-' . uniqid(),
            'customer_id' => $customer->id,
            'designer_id' => null,
            'product_id'  => $product->id,
            'unit_price'  => 50000,
            'quantity'    => 1,
            'total_price' => 50000,
            'status'      => Order::STATUS_PENDING,
        ]);
    }

    private function createReference(array $overrides = []): OrderReference
    {
        $order = $this->createOrder();

        return OrderReference::create(array_merge([
            'order_id' => $order->id,
            'type' => OrderReference::TYPE_FILE,
            'file_url' => 'references/test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'external_url' => null,
            'note' => 'Contoh referensi',
        ], $overrides));
    }
}