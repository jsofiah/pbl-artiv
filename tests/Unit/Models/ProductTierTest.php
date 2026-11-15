<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTierTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Scope active() hanya return yang is_active = true
    // ============================================================
    public function test_scope_active_returns_only_active_tiers(): void
    {
        $product = $this->createProduct();

        $activeTier = ProductTier::create([
            'product_id'    => $product->id,
            'name'          => 'Basic',
            'price'         => 25000,
            'description'   => 'Tier basic',
            'thumbnail_url' => 'tiers/basic.png',
            'is_active'     => true,
        ]);

        ProductTier::create([
            'product_id'    => $product->id,
            'name'          => 'Pro (nonaktif)',
            'price'         => 50000,
            'description'   => 'Tier pro',
            'thumbnail_url' => 'tiers/pro.png',
            'is_active'     => false,
        ]);

        $activeTiers = ProductTier::active()->get();

        $this->assertCount(1, $activeTiers);
        $this->assertEquals($activeTier->id, $activeTiers->first()->id);
        $this->assertTrue($activeTiers->first()->is_active);
    }

    // ============================================================
    // Test #2: Cast price ke decimal string
    // ============================================================
    public function test_price_is_cast_to_decimal(): void
    {
        $product = $this->createProduct();
        $tier = ProductTier::create([
            'product_id'    => $product->id,
            'name'          => 'Tier Test',
            'price'         => 35000.75,
            'description'   => 'Deskripsi',
            'thumbnail_url' => 'tiers/test.png',
            'is_active'     => true,
        ]);
        $tier->refresh();

        $this->assertEquals('35000.75', $tier->price);
    }

    // ============================================================
    // Test #3: Cast is_active ke boolean
    // ============================================================
    public function test_is_active_is_cast_to_boolean(): void
    {
        $product = $this->createProduct();

        $tier = ProductTier::create([
            'product_id'    => $product->id,
            'name'          => 'Tier',
            'price'         => 25000,
            'description'   => 'Deskripsi',
            'thumbnail_url' => 'tiers/test.png',
            'is_active'     => 1, // int
        ]);
        $tier->refresh();

        $this->assertTrue($tier->is_active);
        $this->assertIsBool($tier->is_active);

        $tier2 = ProductTier::create([
            'product_id'    => $product->id,
            'name'          => 'Tier 2',
            'price'         => 25000,
            'description'   => 'Deskripsi',
            'thumbnail_url' => 'tiers/test2.png',
            'is_active'     => 0,
        ]);
        $tier2->refresh();

        $this->assertFalse($tier2->is_active);
        $this->assertIsBool($tier2->is_active);
    }

    // ============================================================
    // Test #4: Relasi product() mengembalikan Product
    // ============================================================
    public function test_product_relation_returns_correct_product(): void
    {
        $tier = $this->createTier();

        $relatedProduct = $tier->product;

        $this->assertInstanceOf(Product::class, $relatedProduct);
        $this->assertEquals($tier->product_id, $relatedProduct->id);
    }

    // ============================================================
    // Test #5: Relasi orders() mengembalikan koleksi Order
    // ============================================================
    public function test_orders_relation_returns_orders(): void
    {
        $tier = $this->createTier();
        $customer = $this->createUser(User::ROLE_CUSTOMER);

        Order::create([
            'order_code'      => 'ORD-' . uniqid(),
            'customer_id'     => $customer->id,
            'designer_id'     => null,
            'product_id'      => $tier->product_id,
            'product_tier_id' => $tier->id,
            'unit_price'      => 35000,
            'quantity'        => 1,
            'total_price'     => 35000,
            'status'          => Order::STATUS_PENDING,
        ]);

        $orders = $tier->orders()->get();

        $this->assertCount(1, $orders);
        $this->assertInstanceOf(Order::class, $orders->first());
        $this->assertEquals($tier->id, $orders->first()->product_tier_id);
    }

    // ============================================================
    // Test #6: UUID auto-generate
    // ============================================================
    public function test_id_is_uuid_auto_generated(): void
    {
        $tier = $this->createTier();

        $this->assertNotNull($tier->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $tier->id
        );
    }

    // ============================================================
    // HELPER
    // ============================================================
    private function createUser(string $role = User::ROLE_CUSTOMER): User
    {
        return User::create([
            'username'  => 'user_' . uniqid(),
            'email'     => 'user_' . uniqid() . '@test.com',
            'full_name' => 'Test ' . ucfirst($role),
            'phone'     => '08123456789',
            'role'      => $role,
            'password'  => bcrypt('password'),
        ]);
    }

    private function createProduct(): Product
    {
        return Product::create([
            'name'          => 'Test Product ' . uniqid(),
            'description'   => 'Deskripsi test',
            'thumbnail_url' => 'products/test.png',
            'price'         => 50000,
            'is_active'     => true,
        ]);
    }

    private function createTier(array $overrides = []): ProductTier
    {
        $product = $this->createProduct();

        return ProductTier::create(array_merge([
            'product_id'    => $product->id,
            'name'          => 'Tier ' . uniqid(),
            'price'         => 35000,
            'description'   => 'Deskripsi tier',
            'thumbnail_url' => 'tiers/test.png',
            'is_active'     => true,
        ], $overrides));
    }
}