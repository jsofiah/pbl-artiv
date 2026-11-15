<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use App\Models\Deliverable;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliverableTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Status default = pending saat create
    // ============================================================
    public function test_status_defaults_to_pending(): void
    {
        $deliverable = $this->createDeliverable();

        $this->assertEquals(Deliverable::STATUS_PENDING, $deliverable->status);
        $this->assertTrue($deliverable->isPending());
        $this->assertFalse($deliverable->isApproved());
        $this->assertFalse($deliverable->isRevisionRequested());
    }

    // ============================================================
    // Test #2: approve() mengubah status + set approved_at
    // ============================================================
    public function test_approve_sets_status_approved_and_approved_at(): void
    {
        $deliverable = $this->createDeliverable();
        $this->assertNull($deliverable->approved_at);

        $deliverable->approve();
        $deliverable->refresh();

        $this->assertEquals(Deliverable::STATUS_APPROVED, $deliverable->status);
        $this->assertTrue($deliverable->isApproved());
        $this->assertFalse($deliverable->isPending());
        $this->assertNotNull($deliverable->approved_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $deliverable->approved_at);
    }

    // ============================================================
    // Test #3: requestRevision() mengubah status + simpan note
    // ============================================================
    public function test_request_revision_sets_status_and_note(): void
    {
        $deliverable = $this->createDeliverable();

        $deliverable->requestRevision('Warna kurang cerah');
        $deliverable->refresh();

        $this->assertEquals(Deliverable::STATUS_REVISION_REQUESTED, $deliverable->status);
        $this->assertTrue($deliverable->isRevisionRequested());
        $this->assertFalse($deliverable->isPending());
        $this->assertEquals('Warna kurang cerah', $deliverable->revision_note);
        $this->assertNull($deliverable->approved_at);
    }

    // ============================================================
    // Test #4: Relasi order() mengembalikan Order yang benar
    // ============================================================
    public function test_order_relation_returns_correct_order(): void
    {
        $deliverable = $this->createDeliverable();

        $relatedOrder = $deliverable->order;

        $this->assertInstanceOf(Order::class, $relatedOrder);
        $this->assertEquals($deliverable->order_id, $relatedOrder->id);
    }

    // ============================================================
    // Test #5: Relasi message() mengembalikan Message yang benar
    // ============================================================
    public function test_message_relation_returns_correct_message(): void
    {
        $deliverable = $this->createDeliverable();

        $relatedMessage = $deliverable->message;

        $this->assertInstanceOf(Message::class, $relatedMessage);
        $this->assertEquals($deliverable->message_id, $relatedMessage->id);
    }

    // ============================================================
    // Test #6: approved_at di-cast ke Carbon
    // ============================================================
    public function test_approved_at_is_cast_to_carbon(): void
    {
        $deliverable = $this->createDeliverable();
        $deliverable->approve();
        $deliverable->refresh();

        $this->assertInstanceOf(\Carbon\Carbon::class, $deliverable->approved_at);
        $this->assertNotNull($deliverable->approved_at);
    }

    // ============================================================
    // HELPER: bikin data berantai User → Product → Order → Conversation → Message → Deliverable
    // ============================================================
    private function createDeliverable(): Deliverable
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

        $conversation = Conversation::create([
            'order_id'    => $order->id,
            'customer_id' => $customer->id,
            'designer_id' => $designer->id,
            'status'      => Conversation::STATUS_ACTIVE,
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $designer->id,
            'type'            => Message::TYPE_DELIVERABLE,
            'body'            => 'Ini hasil desainnya',
        ]);

        return Deliverable::create([
            'order_id'    => $order->id,
            'message_id'  => $message->id,
            'status'      => Deliverable::STATUS_PENDING,
            'revision_note' => null,
            'approved_at' => null,
        ]);
    }
}