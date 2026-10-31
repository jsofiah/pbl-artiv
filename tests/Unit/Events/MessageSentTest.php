<?php

namespace Tests\Unit\Events;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageSentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test #1: Event broadcast di PrivateChannel 'chat.{conversation_id}'
     */
    public function test_broadcasts_on_chat_channel(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        $event = new MessageSent($message, $sender);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);

        $this->assertEquals(
            'private-chat.' . $message->conversation_id,
            $channels[0]->name
        );
    }

    /**
     * Test #2: Payload broadcastWith() sesuai struktur asli
     */
    public function test_broadcast_with_returns_expected_payload(): void
    {
        [$message, $sender] = $this->createMessageAndSender([
            'body' => 'Halo, ini pesan test',
        ]);

        $event = new MessageSent($message, $sender);
        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('order_id', $payload);
        $this->assertArrayHasKey('body', $payload);
        $this->assertArrayHasKey('type', $payload);
        $this->assertArrayHasKey('created_at', $payload);
        $this->assertArrayHasKey('sender', $payload);
        $this->assertArrayHasKey('attachments', $payload);

        $this->assertEquals($message->id, $payload['id']);
        $this->assertEquals('Halo, ini pesan test', $payload['body']);
        $this->assertEquals(Message::TYPE_TEXT, $payload['type']);
        $this->assertEquals($message->conversation->order_id, $payload['order_id']);
        
        $this->assertEquals($sender->id, $payload['sender']['id']);
        $this->assertEquals($sender->full_name, $payload['sender']['name']);
        $this->assertEquals($sender->avatar_url, $payload['sender']['avatar']);
        
        $this->assertIsArray($payload['attachments']);
        $this->assertEmpty($payload['attachments']);
        }

    /**
     * Test #3: broadcastAs() return 'message.sent'
     */
    public function test_broadcast_as_returns_message_sent(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        $event = new MessageSent($message, $sender);

        $this->assertEquals('message.sent', $event->broadcastAs());
    }

    /**
     * Test #4: broadcastWithAttachments()
     */
    public function test_broadcast_with_attachments_returns_attachment_data(): void
    {
        [$message, $sender] = $this->createMessageAndSender();
        
        \App\Models\MessageAttachment::create([
            'message_id' => $message->id,
            'file_url'   => 'chat/file.pdf',
            'file_name'  => 'brief.pdf',
            'file_size'  => 1024,
        ]);
        
        $event = new MessageSent($message->fresh(), $sender);
        $payload = $event->broadcastWith();
        
        $this->assertCount(1, $payload['attachments']);
        $this->assertEquals('brief.pdf', $payload['attachments'][0]['file_name']);
    }

    // ============================================================
    // HELPER: Bikin data berantai User → Product → Order → Conversation → Message
    // ============================================================
    private function createMessageAndSender(array $messageOverrides = []): array
    {
        $customer = User::create([
            'username'  => 'customer_' . uniqid(),
            'email'     => 'customer_' . uniqid() . '@test.com',
            'full_name' => 'Test Customer',
            'phone'     => '08123456789',
            'role'      => User::ROLE_CUSTOMER,
            'password'  => bcrypt('password'),
        ]);

        $designer = User::create([
            'username'  => 'designer_' . uniqid(),
            'email'     => 'designer_' . uniqid() . '@test.com',
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

        $message = Message::create(array_merge([
            'conversation_id' => $conversation->id,
            'sender_id'       => $customer->id,
            'type'            => Message::TYPE_TEXT,
            'body'            => 'Pesan default test',
        ], $messageOverrides));

        return [$message, $customer];
    }
}