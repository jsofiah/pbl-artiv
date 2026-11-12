<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Deliverable;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
class PesananControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $designer;
    private Order $order;
    private Deliverable $deliverable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'username'  => 'cust_test', 'email' => 'cust@test.com',
            'full_name' => 'Customer', 'phone' => '0812', 'role' => 'customer',
            'password'  => bcrypt('password'),
        ]);

        $this->designer = User::create([
            'username'  => 'des_test', 'email' => 'des@test.com',
            'full_name' => 'Designer', 'phone' => '0813', 'role' => 'designer',
            'password'  => bcrypt('password'),
        ]);

        $product = Product::create([
            'name' => 'Desain Test', 'price' => 100000, 'is_active' => true,
        ]);

        $this->order = Order::create([
            'order_code'      => 'ARTIV-TEST123',
            'customer_id'     => $this->customer->id,
            'designer_id'     => $this->designer->id,
            'product_id'      => $product->id,
            'unit_price'      => 100000,
            'quantity'        => 1,
            'total_price'     => 100000,
            'status'          => 'in_progress', 
            'deadline'        => now()->addDays(2),
            'revision_count'  => 0,
            'is_express'      => false,
        ]);

        $conversation = Conversation::create([
            'order_id'    => $this->order->id,
            'customer_id' => $this->customer->id,
            'designer_id' => $this->designer->id,
            'status'      => 'active',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $this->designer->id,
            'type'            => 'text',
            'body'            => 'Ini hasil desainnya ya.',
        ]);

        $this->deliverable = Deliverable::create([
            'order_id'   => $this->order->id,
            'message_id' => $message->id,
            'status'     => 'pending', 
        ]);
    }

    public function test_customer_bisa_lihat_detail_pesanan(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('customer.pesanan.show', $this->order->id));

        $response->assertStatus(200);
        $response->assertViewHas('order');
    }

    public function test_customer_bisa_approve_hasil_desain(): void
    {
        $response = $this->actingAs($this->customer)
            ->withSession(['_token' => 'test_token']) // <-- TRIK CSRF
            ->post(route('customer.pesanan.deliverable.approve', [
                'order' => $this->order->id,
                'deliverable' => $this->deliverable->id
            ]), [
                '_token' => 'test_token' // <-- TRIK CSRF
            ]);

        $response->assertRedirect(route('customer.pesanan.show', $this->order->id));

        $this->assertDatabaseHas('orders', [
            'id'     => $this->order->id,
            'status' => 'completed',
        ]);
    }

    public function test_customer_bisa_minta_revisi(): void
    {
        $response = $this->actingAs($this->customer)
            ->withSession(['_token' => 'test_token']) // <-- TRIK CSRF
            ->post(route('customer.pesanan.deliverable.revisi', [
                'order' => $this->order->id,
                'deliverable' => $this->deliverable->id
            ]), [
                '_token' => 'test_token', // <-- TRIK CSRF
                'revision_note' => 'Tolong warnanya dibikin lebih terang ya.'
            ]);

        $response->assertRedirect(route('customer.pesanan.show', $this->order->id));

        $this->assertDatabaseHas('orders', [
            'id'     => $this->order->id,
            'status' => 'revision_needed',
        ]);
    }
}