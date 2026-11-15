<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Relasi order() mengembalikan Order yang benar
    // ============================================================
    public function test_order_relation_returns_correct_order(): void
    {
        $conversation = $this->createConversation();

        $relatedOrder = $conversation->order;

        $this->assertInstanceOf(Order::class, $relatedOrder);
        $this->assertEquals($conversation->order_id, $relatedOrder->id);
    }

    // ============================================================
    // Test #2: Relasi customer() mengembalikan User customer
    // ============================================================
    public function test_customer_relation_returns_correct_customer(): void
    {
        $conversation = $this->createConversation();

        $relatedCustomer = $conversation->customer;

        $this->assertInstanceOf(User::class, $relatedCustomer);
        $this->assertEquals($conversation->customer_id, $relatedCustomer->id);
        $this->assertEquals(User::ROLE_CUSTOMER, $relatedCustomer->role);
    }

    // ============================================================
    // Test #3: Relasi designer() mengembalikan User designer
    // ============================================================
    public function test_designer_relation_returns_correct_designer(): void
    {
        $conversation = $this->createConversation();

        $relatedDesigner = $conversation->designer;

        $this->assertInstanceOf(User::class, $relatedDesigner);
        $this->assertEquals($conversation->designer_id, $relatedDesigner->id);
        $this->assertEquals(User::ROLE_DESIGNER, $relatedDesigner->role);
    }

    // ============================================================
    // Test #4: Relasi messages() mengembalikan koleksi Message
    // ============================================================
    public function test_messages_relation_returns_messages(): void
    {
        $conversation = $this->createConversation();
        $customer = $conversation->customer;

        // Bikin 2 pesan dalam conversation ini
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $customer->id,
            'type'            => Message::TYPE_TEXT,
            'body'            => 'Pesan pertama',
        ]);
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $customer->id,
            'type'            => Message::TYPE_TEXT,
            'body'            => 'Pesan kedua',
        ]);

        $messages = $conversation->messages()->get();

        $this->assertCount(2, $messages);
        $this->assertInstanceOf(Message::class, $messages->first());
    }

    // ============================================================
    // Test #5: isActive() true saat status 'active', false saat 'closed'
    // ============================================================
    public function test_is_active_returns_true_for_active_status(): void
    {
        $conversation = $this->createConversation();

        $this->assertEquals(Conversation::STATUS_ACTIVE, $conversation->status);
        $this->assertTrue($conversation->isActive());

        // Update ke closed, cek false
        $conversation->update(['status' => Conversation::STATUS_CLOSED]);
        $this->assertFalse($conversation->fresh()->isActive());
    }

    // ============================================================
    // Test #6: unreadCountFor() menghitung pesan belum dibaca
    // ============================================================
    public function test_unread_count_for_user(): void
    {
        $conversation = $this->createConversation();
        $customer = $conversation->customer;
        $designer = $conversation->designer;

        // Pesan dari customer ke designer, belum dibaca
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $customer->id,
            'type'            => Message::TYPE_TEXT,
            'body'            => 'Halo designer',
            'read_at'         => null, // belum dibaca
        ]);
        // Pesan dari customer ke designer, sudah dibaca
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $customer->id,
            'type'            => Message::TYPE_TEXT,
            'body'            => 'Pesan lama',
            'read_at'         => now(),
        ]);
        // Pesan dari designer (tidak dihitung untuk designer sendiri)
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $designer->id,
            'type'            => Message::TYPE_TEXT,
            'body'            => 'Balasan designer',
            'read_at'         => null,
        ]);

        // Untuk designer: hanya pesan dari customer yang belum dibaca (1)
        $this->assertEquals(1, $conversation->unreadCountFor($designer));

        // Untuk customer: hanya pesan dari designer yang belum dibaca (1)
        $this->assertEquals(1, $conversation->unreadCountFor($customer));
    }

    // ============================================================
    // Test #7: unreadCountFor() nol jika tidak ada pesan belum dibaca
    // ============================================================
    public function test_unread_count_zero_when_all_read(): void
    {
        $conversation = $this->createConversation();
        $customer = $conversation->customer;
        $designer = $conversation->designer;

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $customer->id,
            'type'            => Message::TYPE_TEXT,
            'body'            => 'Pesan',
            'read_at'         => now(),
        ]);

        $this->assertEquals(0, $conversation->unreadCountFor($designer));
    }

    // ============================================================
    // HELPER: bikin data berantai User → Product → Order → Conversation
    // ============================================================
    private function createConversation(): Conversation
    {
        $customer = User::create([
            'username'  => 'cust_' . uniqid(),
            'email'     => 'cust_' . uniqid() . '@test.com',
            'full_name' => 'Test Customer',
            'phone'     => '08123456789',
            'role'      => User::ROLE_CUSTOMER,
            'password'  => bcrypt('password'),
        ]);

        $designer = User::create([
            'username'  => 'des_' . uniqid(),
            'email'     => 'des_' . uniqid() . '@test.com',
            'full_name' => 'Test Designer',
            'phone'     => '08123456789',
            'role'      => User::ROLE_DESIGNER,
            'password'  => bcrypt('password'),
        ]);

        $product = Product::create([
            'name'          => 'Test Product ' . uniqid(),
            'description'   => 'Deskripsi test',
            'thumbnail_url' => 'products/test.png',
            'price'         => 50000,
            'is_active'     => true,
        ]);

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

        return Conversation::create([
            'order_id'    => $order->id,
            'customer_id' => $customer->id,
            'designer_id' => $designer->id,
            'status'      => Conversation::STATUS_ACTIVE,
        ]);
    }
}