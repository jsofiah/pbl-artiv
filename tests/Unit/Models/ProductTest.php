<?php

namespace Tests\Unit\Models;

use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Scope active() hanya return yang is_active = true
    // ============================================================
    public function test_scope_active_returns_only_active_products(): void
    {
        $active = Product::create([
            'name'          => 'Aktif ' . uniqid(),
            'description'   => 'Deskripsi',
            'thumbnail_url' => 'products/aktif.png',
            'price'         => 50000,
            'is_active'     => true,
        ]);

        Product::create([
            'name'          => 'Nonaktif ' . uniqid(),
            'description'   => 'Deskripsi',
            'thumbnail_url' => 'products/nonaktif.png',
            'price'         => 50000,
            'is_active'     => false,
        ]);

        $activeProducts = Product::active()->get();

        $this->assertCount(1, $activeProducts);
        $this->assertEquals($active->id, $activeProducts->first()->id);
        $this->assertTrue($activeProducts->first()->is_active);
    }

    // ============================================================
    // Test #2: Cast price ke decimal string
    // ============================================================
    public function test_price_is_cast_to_decimal(): void
    {
        $product = $this->createProduct([
            'price' => 75000.50,
        ]);
        $product->refresh();

        // decimal:2 → string "75000.50"
        $this->assertEquals('75000.50', $product->price);
    }

    // ============================================================
    // Test #3: Cast is_active ke boolean
    // ============================================================
    public function test_is_active_is_cast_to_boolean(): void
    {
        $product = $this->createProduct(['is_active' => 1]);
        $product->refresh();

        $this->assertTrue($product->is_active);
        $this->assertIsBool($product->is_active);

        $product2 = $this->createProduct(['is_active' => 0]);
        $product2->refresh();

        $this->assertFalse($product2->is_active);
        $this->assertIsBool($product2->is_active);
    }

    // ============================================================
    // Test #4: Relasi tiers() mengembalikan koleksi ProductTier
    // ============================================================
    public function test_tiers_relation_returns_tiers(): void
    {
        $product = $this->createProduct();

        // Bikin 2 tier
        ProductTier::create([
            'product_id'    => $product->id,
            'name'          => 'Basic',
            'price'         => 25000,
            'description'   => 'Tier basic',
            'thumbnail_url' => 'tiers/basic.png',
            'is_active'     => true,
        ]);
        ProductTier::create([
            'product_id'    => $product->id,
            'name'          => 'Pro',
            'price'         => 50000,
            'description'   => 'Tier pro',
            'thumbnail_url' => 'tiers/pro.png',
            'is_active'     => true,
        ]);

        $tiers = $product->tiers()->get();

        $this->assertCount(2, $tiers);
        $this->assertInstanceOf(ProductTier::class, $tiers->first());
    }

    // ============================================================
    // Test #5: hasTiers() true jika punya tier, false jika tidak
    // ============================================================
    public function test_has_tiers_returns_correct_boolean(): void
    {
        $productWithTier = $this->createProduct();
        ProductTier::create([
            'product_id'    => $productWithTier->id,
            'name'          => 'Tier 1',
            'price'         => 25000,
            'description'   => 'Deskripsi',
            'thumbnail_url' => 'tiers/tier1.png',
            'is_active'     => true,
        ]);

        $this->assertTrue($productWithTier->hasTiers());

        $productWithoutTier = $this->createProduct();
        $this->assertFalse($productWithoutTier->hasTiers());
    }

    // ============================================================
    // Test #6: Relasi orders() mengembalikan koleksi Order
    // ============================================================
    public function test_orders_relation_returns_orders(): void
    {
        $product = $this->createProduct();
        $customer = $this->createUser(User::ROLE_CUSTOMER);

        Order::create([
            'order_code'  => 'ORD-' . uniqid(),
            'customer_id' => $customer->id,
            'designer_id' => null,
            'product_id'  => $product->id,
            'unit_price'  => 50000,
            'quantity'    => 1,
            'total_price' => 50000,
            'status'      => Order::STATUS_PENDING,
        ]);

        $orders = $product->orders()->get();

        $this->assertCount(1, $orders);
        $this->assertInstanceOf(Order::class, $orders->first());
    }

    // ============================================================
    // Test #7: Relasi galleryItems() mengembalikan koleksi GalleryItem
    // ============================================================
    public function test_gallery_items_relation_returns_gallery_items(): void
    {
        $product = $this->createProduct();
        $admin = $this->createUser(User::ROLE_ADMIN);

        GalleryItem::create([
            'product_id'  => $product->id,
            'title'       => 'Hasil Karya',
            'image_url'   => 'gallery/hasil.png',
            'is_active'   => true,
            'created_by'  => $admin->id,
        ]);

        $items = $product->galleryItems()->get();

        $this->assertCount(1, $items);
        $this->assertInstanceOf(GalleryItem::class, $items->first());
    }

    // ============================================================
    // Test #8: UUID auto-generate
    // ============================================================
    public function test_id_is_uuid_auto_generated(): void
    {
        $product = $this->createProduct();

        $this->assertNotNull($product->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $product->id
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

    private function createProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name'          => 'Test Product ' . uniqid(),
            'description'   => 'Deskripsi test',
            'thumbnail_url' => 'products/test.png',
            'price'         => 50000,
            'is_active'     => true,
        ], $overrides));
    }
}