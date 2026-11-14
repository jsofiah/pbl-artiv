<?php

namespace Tests\Unit\Events;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessageSentTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_implements_should_broadcast(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        $event = new MessageSent($message, $sender);

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
    }

    public function test_event_can_be_dispatched_with_message_and_sender(): void
    {
        Event::fake([MessageSent::class]);

        [$message, $sender] = $this->createMessageAndSender();

        MessageSent::dispatch($message, $sender);

        Event::assertDispatched(
            MessageSent::class,
            fn (MessageSent $e) => $e->message->is($message) && $e->sender->is($sender)
        );
    }

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

    public function test_broadcast_as_returns_message_sent(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        $event = new MessageSent($message, $sender);

        $this->assertEquals('message.sent', $event->broadcastAs());
    }

    public function test_broadcast_with_returns_expected_payload(): void
    {
        [$message, $sender] = $this->createMessageAndSender([
            'body' => 'Halo, ini pesan test',
        ]);

        $payload = (new MessageSent($message, $sender))->broadcastWith();

        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('order_id', $payload);
        $this->assertArrayHasKey('body', $payload);
        $this->assertArrayHasKey('type', $payload);
        $this->assertArrayHasKey('created_at', $payload);
        $this->assertArrayHasKey('sender', $payload);
        $this->assertArrayHasKey('attachments', $payload);
        $this->assertArrayHasKey('deliverable', $payload);

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

    public function test_created_at_is_formatted_as_iso_string(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        $payload = (new MessageSent($message, $sender))->broadcastWith();

        $this->assertIsString($payload['created_at']);
        $this->assertSame($message->created_at->toISOString(), $payload['created_at']);
    }

    // BARU
    public function test_sender_in_payload_comes_from_event_sender_not_message_sender(): void
    {
        [$message, , $designer] = $this->createMessageAndSender();

        $payload = (new MessageSent($message, $designer))->broadcastWith();

        $this->assertEquals($designer->id, $payload['sender']['id']);
        $this->assertEquals($designer->full_name, $payload['sender']['name']);
    }


    public function test_broadcast_with_attachments_returns_attachment_data(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        $attachment = MessageAttachment::create([
            'message_id' => $message->id,
            'file_url'   => 'chat/file.pdf',
            'file_name'  => 'brief.pdf',
            'file_size'  => 1024,
        ]);

        $payload = (new MessageSent($message->fresh(), $sender))->broadcastWith();

        $this->assertCount(1, $payload['attachments']);
        $this->assertEquals('brief.pdf', $payload['attachments'][0]['file_name']);

        $this->assertEquals($attachment->id, $payload['attachments'][0]['id']);
        $this->assertEquals(1024, $payload['attachments'][0]['file_size']);
    }

    public function test_attachment_payload_does_not_expose_file_url(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        MessageAttachment::create([
            'message_id' => $message->id,
            'file_url'   => 'chat/rahasia.pdf',
            'file_name'  => 'rahasia.pdf',
            'file_size'  => 2048,
        ]);

        $payload = (new MessageSent($message->fresh(), $sender))->broadcastWith();

        $this->assertArrayNotHasKey('file_url', $payload['attachments'][0]);
    }

    public function test_broadcast_with_returns_multiple_attachments(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        foreach (['a.pdf', 'b.png'] as $name) {
            MessageAttachment::create([
                'message_id' => $message->id,
                'file_url'   => 'chat/' . $name,
                'file_name'  => $name,
                'file_size'  => 100,
            ]);
        }

        $payload = (new MessageSent($message->fresh(), $sender))->broadcastWith();

        $this->assertCount(2, $payload['attachments']);
        $this->assertEqualsCanonicalizing(
            ['a.pdf', 'b.png'],
            array_column($payload['attachments'], 'file_name')
        );
    }

    public function test_deliverable_is_null_and_revision_data_absent_without_deliverable(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        $payload = (new MessageSent($message, $sender))->broadcastWith();

        $this->assertNull($payload['deliverable']);
        $this->assertArrayNotHasKey('revision_count', $payload);
        $this->assertArrayNotHasKey('revision_limit', $payload);
        $this->assertArrayNotHasKey('remaining', $payload);
        $this->assertArrayNotHasKey('can_request_revision', $payload);
    }

    public function test_deliverable_and_revision_data_present_when_message_has_deliverable(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        $order = $message->conversation->order;
        $order->forceFill(['revision_count' => 1])->save();

        $this->setDeliverable($message, 'deliv-123', 'pending');

        $payload = (new MessageSent($message, $sender))->broadcastWith();

        $this->assertSame('deliv-123', $payload['deliverable']['id']);
        $this->assertSame('pending', $payload['deliverable']['status']);

        $this->assertSame(1, $payload['revision_count']);
        $this->assertSame(Order::FREE_REVISION_LIMIT, $payload['revision_limit']);
        $this->assertSame(Order::FREE_REVISION_LIMIT - 1, $payload['remaining']);
        $this->assertSame($order->canRequestFreeRevision(), $payload['can_request_revision']);
    }

    public function test_remaining_revisions_never_goes_below_zero(): void
    {
        [$message, $sender] = $this->createMessageAndSender();

        $order = $message->conversation->order;
        $order->forceFill(['revision_count' => Order::FREE_REVISION_LIMIT + 5])->save();

        $this->setDeliverable($message, 'deliv-123', 'pending');

        $payload = (new MessageSent($message, $sender))->broadcastWith();

        $this->assertSame(0, $payload['remaining']);
        $this->assertSame($order->canRequestFreeRevision(), $payload['can_request_revision']);
    }

    private function setDeliverable(Message $message, string $id, string $status): void
    {
        $message->setRelation('deliverable', (object) [
            'id'     => $id,
            'status' => $status,
        ]);
    }

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

        return [$message, $customer, $designer];
    }
}