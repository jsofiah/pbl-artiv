<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use App\Models\GalleryItem;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryItemTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Scope active() hanya return yang is_active = true
    // ============================================================
    public function test_scope_active_returns_only_active_items(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser(User::ROLE_ADMIN);

        $activeItem = GalleryItem::create([
            'product_id'  => $product->id,
            'title'       => 'Item Aktif',
            'image_url'   => 'gallery/active.png',
            'is_active'   => true,
            'created_by'  => $user->id,
        ]);

        GalleryItem::create([
            'product_id'  => $product->id,
            'title'       => 'Item Nonaktif',
            'image_url'   => 'gallery/inactive.png',
            'is_active'   => false,
            'created_by'  => $user->id,
        ]);

        $activeItems = GalleryItem::active()->get();

        $this->assertCount(1, $activeItems);
        $this->assertEquals($activeItem->id, $activeItems->first()->id);
        $this->assertTrue($activeItems->first()->is_active);
    }

    // ============================================================
    // Test #2: Scope latest() urutkan descending created_at
    // ============================================================
    public function test_scope_latest_orders_by_created_at_desc(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser(User::ROLE_ADMIN);

        // Bikin item lama (created_at di past)
        $oldItem = GalleryItem::create([
            'product_id'  => $product->id,
            'title'       => 'Item Lama',
            'image_url'   => 'gallery/old.png',
            'is_active'   => true,
            'created_by'  => $user->id,
        ]);
        $oldItem->created_at = now()->subDays(2);
        $oldItem->save();

        // Bikin item baru
        $newItem = GalleryItem::create([
            'product_id'  => $product->id,
            'title'       => 'Item Baru',
            'image_url'   => 'gallery/new.png',
            'is_active'   => true,
            'created_by'  => $user->id,
        ]);

        $items = GalleryItem::latest()->get();

        $this->assertCount(2, $items);
        // Item baru harus di urutan pertama
        $this->assertEquals($newItem->id, $items->first()->id);
        $this->assertEquals($oldItem->id, $items->last()->id);
    }

    // ============================================================
    // Test #3: Relasi product() mengembalikan Product yang benar
    // ============================================================
    public function test_product_relation_returns_correct_product(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser(User::ROLE_ADMIN);

        $item = GalleryItem::create([
            'product_id'  => $product->id,
            'title'       => 'Test Gallery',
            'image_url'   => 'gallery/test.png',
            'is_active'   => true,
            'created_by'  => $user->id,
        ]);

        $relatedProduct = $item->product;

        $this->assertInstanceOf(Product::class, $relatedProduct);
        $this->assertEquals($product->id, $relatedProduct->id);
    }

    // ============================================================
    // Test #4: Relasi creator() mengembalikan User yang benar
    // ============================================================
    public function test_creator_relation_returns_correct_user(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser(User::ROLE_ADMIN);

        $item = GalleryItem::create([
            'product_id'  => $product->id,
            'title'       => 'Test Gallery',
            'image_url'   => 'gallery/test.png',
            'is_active'   => true,
            'created_by'  => $user->id,
        ]);

        $relatedCreator = $item->creator;

        $this->assertInstanceOf(User::class, $relatedCreator);
        $this->assertEquals($user->id, $relatedCreator->id);
        $this->assertEquals($user->email, $relatedCreator->email);
    }

    // ============================================================
    // Test #5: Relasi order() mengembalikan Order yang benar
    // ============================================================
    public function test_order_relation_returns_correct_order(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);
        $designer = $this->createUser(User::ROLE_DESIGNER);
        $product = $this->createProduct();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $order = Order::create([
            'order_code'  => 'ORD-' . uniqid(),
            'customer_id' => $customer->id,
            'designer_id' => $designer->id,
            'product_id'  => $product->id,
            'unit_price'  => 50000,
            'quantity'    => 1,
            'total_price' => 50000,
            'status'      => Order::STATUS_COMPLETED,
        ]);

        $item = GalleryItem::create([
            'product_id'  => $product->id,
            'order_id'    => $order->id,
            'title'       => 'Test Gallery',
            'image_url'   => 'gallery/test.png',
            'is_active'   => true,
            'created_by'  => $admin->id,
        ]);

        $relatedOrder = $item->order;

        $this->assertInstanceOf(Order::class, $relatedOrder);
        $this->assertEquals($order->id, $relatedOrder->id);
    }

    // ============================================================
    // Test #6: Relasi attachment() mengembalikan MessageAttachment
    // ============================================================
    public function test_attachment_relation_returns_correct_attachment(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);
        $designer = $this->createUser(User::ROLE_DESIGNER);
        $product = $this->createProduct();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $order = Order::create([
            'order_code'  => 'ORD-' . uniqid(),
            'customer_id' => $customer->id,
            'designer_id' => $designer->id,
            'product_id'  => $product->id,
            'unit_price'  => 50000,
            'quantity'    => 1,
            'total_price' => 50000,
            'status'      => Order::STATUS_IN_PROGRESS,
        ]);

        $conversation = Conversation::create([
            'order_id'    => $order->id,
            'customer_id' => $customer->id,
            'designer_id' => $designer->id,
            'status'      => Conversation::STATUS_ACTIVE,
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $designer->id,
            'type'            => Message::TYPE_FILE,
            'body'            => 'Ini file desain',
        ]);

        $attachment = MessageAttachment::create([
            'message_id' => $message->id,
            'file_url'   => 'chat/design.png',
            'file_name'  => 'design.png',
            'file_size'  => 1024,
            'mime_type'  => 'image/png',
            'file_type'  => 'image',
        ]);

        $item = GalleryItem::create([
            'product_id'            => $product->id,
            'order_id'              => $order->id,
            'message_attachment_id' => $attachment->id,
            'title'                 => 'Hasil Desain',
            'image_url'             => 'gallery/design.png',
            'is_active'             => true,
            'created_by'            => $admin->id,
        ]);

        $relatedAttachment = $item->attachment;

        $this->assertInstanceOf(MessageAttachment::class, $relatedAttachment);
        $this->assertEquals($attachment->id, $relatedAttachment->id);
        $this->assertEquals('design.png', $relatedAttachment->file_name);
    }

    // ============================================================
    // Test #7: Cast is_active ke boolean
    // ============================================================
    public function test_is_active_is_cast_to_boolean(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser(User::ROLE_ADMIN);

        $item = GalleryItem::create([
            'product_id'  => $product->id,
            'title'       => 'Test',
            'image_url'   => 'gallery/test.png',
            'is_active'   => 1, // int, harus jadi bool
            'created_by'  => $user->id,
        ]);

        $item->refresh();

        $this->assertTrue($item->is_active);
        $this->assertIsBool($item->is_active);
    }

    // ============================================================
    // HELPER
    // ============================================================
    private function createUser(string $role): User
    {
        return User::create([
            'username'  => $role . '_' . uniqid(),
            'email'     => $role . '_' . uniqid() . '@test.com',
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
}