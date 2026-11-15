<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use App\Models\Deliverable;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: isRead() false saat read_at null, true setelah markAsRead()
    // ============================================================
    public function test_is_read_returns_false_when_read_at_null(): void
    {
        $message = $this->createMessage();

        $this->assertFalse($message->isRead());
        $this->assertNull($message->read_at);

        $message->markAsRead();
        $message->refresh();

        $this->assertTrue($message->isRead());
        $this->assertNotNull($message->read_at);
    }

    // ============================================================
    // Test #2: markAsRead() tidak mengubah read_at jika sudah dibaca
    // ============================================================
    public function test_mark_as_read_does_not_change_timestamp_if_already_read(): void
    {
        $message = $this->createMessage(['read_at' => now()]);
        $originalReadAt = $message->read_at;

        // Panggil markAsRead lagi
        $message->markAsRead();
        $message->refresh();

        // read_at tidak berubah (sama dengan original)
        $this->assertEquals($originalReadAt->toDateTimeString(), $message->read_at->toDateTimeString());
    }

    // ============================================================
    // Test #3: flag() mengubah is_flagged dan flag_reason
    // ============================================================
    public function test_flag_sets_flagged_and_reason(): void
    {
        $message = $this->createMessage();

        $this->assertFalse($message->is_flagged);
        $this->assertNull($message->flag_reason);

        $message->flag('Mengandung kata terlarang');
        $message->refresh();

        $this->assertTrue($message->is_flagged);
        $this->assertEquals('Mengandung kata terlarang', $message->flag_reason);
    }

    // ============================================================
    // Test #4: isDeliverable() dan isSystem()
    // ============================================================
    public function test_is_deliverable_and_is_system(): void
    {
        $textMessage = $this->createMessage(['type' => Message::TYPE_TEXT]);
        $this->assertFalse($textMessage->isDeliverable());
        $this->assertFalse($textMessage->isSystem());

        $deliverableMessage = $this->createMessage(['type' => Message::TYPE_DELIVERABLE]);
        $this->assertTrue($deliverableMessage->isDeliverable());
        $this->assertFalse($deliverableMessage->isSystem());

        $systemMessage = $this->createMessage(['type' => Message::TYPE_SYSTEM]);
        $this->assertTrue($systemMessage->isSystem());
        $this->assertFalse($systemMessage->isDeliverable());
    }

    // ============================================================
    // Test #5: Relasi conversation() mengembalikan Conversation
    // ============================================================
    public function test_conversation_relation_returns_correct_conversation(): void
    {
        $message = $this->createMessage();

        $relatedConversation = $message->conversation;

        $this->assertInstanceOf(Conversation::class, $relatedConversation);
        $this->assertEquals($message->conversation_id, $relatedConversation->id);
    }

    // ============================================================
    // Test #6: Relasi sender() mengembalikan User
    // ============================================================
    public function test_sender_relation_returns_correct_sender(): void
    {
        $message = $this->createMessage();

        $relatedSender = $message->sender;

        $this->assertInstanceOf(User::class, $relatedSender);
        $this->assertEquals($message->sender_id, $relatedSender->id);
    }

    // ============================================================
    // Test #7: Relasi attachments() mengembalikan koleksi MessageAttachment
    // ============================================================
    public function test_attachments_relation_returns_attachments(): void
    {
        $message = $this->createMessage();

        MessageAttachment::create([
            'message_id' => $message->id,
            'file_url'   => 'chat/file1.pdf',
            'file_name'  => 'file1.pdf',
            'file_size'  => 1024,
            'mime_type'  => 'application/pdf',
            'file_type'  => 'document',
        ]);
        MessageAttachment::create([
            'message_id' => $message->id,
            'file_url'   => 'chat/file2.png',
            'file_name'  => 'file2.png',
            'file_size'  => 2048,
            'mime_type'  => 'image/png',
            'file_type'  => 'image',
        ]);

        $attachments = $message->attachments()->get();

        $this->assertCount(2, $attachments);
        $this->assertInstanceOf(MessageAttachment::class, $attachments->first());
    }

    // ============================================================
    // Test #8: Relasi deliverable() mengembalikan Deliverable
    // ============================================================
    public function test_deliverable_relation_returns_deliverable(): void
    {
        $message = $this->createMessage(['type' => Message::TYPE_DELIVERABLE]);

        Deliverable::create([
            'order_id'    => $message->conversation->order_id,
            'message_id'  => $message->id,
            'status'      => Deliverable::STATUS_PENDING,
            'revision_note' => null,
            'approved_at' => null,
        ]);

        $relatedDeliverable = $message->deliverable;

        $this->assertInstanceOf(Deliverable::class, $relatedDeliverable);
        $this->assertEquals($message->id, $relatedDeliverable->message_id);
    }

    // ============================================================
    // Test #9: Cast is_flagged ke boolean
    // ============================================================
    public function test_is_flagged_is_cast_to_boolean(): void
    {
        $message = $this->createMessage(['is_flagged' => 1]);
        $message->refresh();

        $this->assertTrue($message->is_flagged);
        $this->assertIsBool($message->is_flagged);
    }

    // ============================================================
    // HELPER: bikin data berantai User → Product → Order → Conversation → Message
    // ============================================================
    private function createMessage(array $overrides = []): Message
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

        return Message::create(array_merge([
            'conversation_id' => $conversation->id,
            'sender_id'       => $customer->id,
            'type'            => Message::TYPE_TEXT,
            'body'            => 'Pesan test',
            'is_flagged'      => false,
        ], $overrides));
    }
}