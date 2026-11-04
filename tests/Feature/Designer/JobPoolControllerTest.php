<?php

namespace Tests\Feature\Designer;

use App\Models\Order;
use App\Models\OrderReference;
use App\Models\Product;
use App\Models\ProductTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobPoolControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::create([
            'username'  => $role . '_' . uniqid(),
            'email'     => $role . '_' . uniqid() . '@test.com',
            'full_name' => 'Test ' . ucfirst($role),
            'phone'     => '0812345678' . rand(10, 99),
            'role'      => $role,
            'password'  => bcrypt('password'),
        ]);
    }

    private function createOrder(array $overrides = []): Order
    {
        $customer = $this->createUser('customer');
        
        $product = Product::create([
            'name'        => 'Produk Test ' . uniqid(),
            'description' => 'Deskripsi test',
            'is_active'   => true,
        ]);
        
        $tier = ProductTier::create([
            'product_id' => $product->id,
            'name'       => 'Tier Test',
            'price'      => 100000,
        ]);

        return Order::create(array_merge([
            'order_code'      => 'ORD-' . strtoupper(uniqid()),
            'customer_id'     => $customer->id,
            'product_id'      => $product->id,
            'product_tier_id' => $tier->id,
            'unit_price'      => 100000,
            'quantity'        => 1,
            'total_price'     => 100000,
            'status'          => Order::STATUS_WAITING_DESIGNER,
            'deadline'        => now()->addDays(7),
            'brief_note'      => 'Brief test',
        ], $overrides));
    }

    public function test_ambil_job_assigns_designer_and_sets_in_progress(): void
    {
        $designer = $this->createUser('designer');
        $order    = $this->createOrder(['status' => Order::STATUS_WAITING_DESIGNER]);

        $response = $this->actingAs($designer)
            ->post(route('designer.job-pool.ambil', $order->id));

        $response->assertRedirect(route('designer.job-pool.sukses', $order->id));

        $this->assertDatabaseHas('orders', [
            'id'          => $order->id,
            'designer_id' => $designer->id,
            'status'      => Order::STATUS_IN_PROGRESS,
        ]);
        
        $order->refresh();
        $this->assertNotNull($order->assigned_at);

        $this->assertDatabaseHas('order_logs', [
            'order_id' => $order->id,
            'actor_id' => $designer->id,
            'action'   => 'order_taken',
        ]);
    }

    public function test_ambil_job_blocked_when_already_taken(): void
    {
        $otherDesigner = $this->createUser('designer');
        $newDesigner   = $this->createUser('designer');

        $order = $this->createOrder([
            'status'      => Order::STATUS_IN_PROGRESS,
            'designer_id' => $otherDesigner->id,
        ]);

        $response = $this->actingAs($newDesigner)
            ->post(route('designer.job-pool.ambil', $order->id));

        $response->assertNotFound();

        $order->refresh();
        $this->assertEquals($otherDesigner->id, $order->designer_id);
    }

    public function test_job_pool_only_shows_waiting_designer_orders(): void
    {
        $designer     = $this->createUser('designer');
        $orderWaiting = $this->createOrder(['status' => Order::STATUS_WAITING_DESIGNER]);
        $orderProses  = $this->createOrder(['status' => Order::STATUS_IN_PROGRESS]);
        $orderPending = $this->createOrder(['status' => Order::STATUS_PENDING]);

        $response = $this->actingAs($designer)
            ->get(route('designer.job-pool'));

        $response->assertOk();
        $response->assertSee($orderWaiting->order_code);
        $response->assertDontSee($orderProses->order_code);
        $response->assertDontSee($orderPending->order_code);
    }

    public function test_job_pool_search_by_order_code_works(): void
    {
        $designer = $this->createUser('designer');
        $order1   = $this->createOrder(['order_code' => 'ORD-CARI-SAYA']);
        $order2   = $this->createOrder(['order_code' => 'ORD-LAIN']);

        $response = $this->actingAs($designer)
            ->get(route('designer.job-pool', ['q' => 'CARI-SAYA']));

        $response->assertOk();
        $response->assertSee($order1->order_code);
        $response->assertDontSee($order2->order_code);
    }

    public function test_job_pool_filter_by_category_works(): void
    {
        $designer = $this->createUser('designer');
        
        $productA = Product::create(['name' => 'Desain Logo', 'is_active' => true]);
        $productB = Product::create(['name' => 'Desain Banner', 'is_active' => true]);
        
        $orderA = $this->createOrder([
            'product_id' => $productA->id, 
            'product_tier_id' => ProductTier::create(['product_id' => $productA->id, 'name' => 'T1', 'price' => 100])->id
        ]);
        $orderB = $this->createOrder([
            'product_id' => $productB->id, 
            'product_tier_id' => ProductTier::create(['product_id' => $productB->id, 'name' => 'T2', 'price' => 100])->id
        ]);

        $response = $this->actingAs($designer)->get(route('designer.job-pool', ['kategori' => 'Logo']));
        $response->assertOk();
        $response->assertSee($orderA->order_code);
        $response->assertDontSee($orderB->order_code);
    }

    public function test_job_pool_sorted_by_price_desc(): void
    {
        $designer = $this->createUser('designer');
        $orderMurah = $this->createOrder(['order_code' => 'ORD-MURAH', 'total_price' => 50000]);
        $orderMahal = $this->createOrder(['order_code' => 'ORD-MAHAL', 'total_price' => 500000]);

        $response = $this->actingAs($designer)->get(route('designer.job-pool', ['urutkan' => 'harga_desc']));
        $response->assertOk();
        
        $response->assertSeeInOrder(['ORD-MAHAL', 'ORD-MURAH']);
    }

    public function test_cannot_take_job_if_deadline_is_past(): void
    {
        $designer = $this->createUser('designer');
        $order = $this->createOrder([
            'status'   => Order::STATUS_WAITING_DESIGNER,
            'deadline' => now()->subDays(1), 
        ]);

        $response = $this->actingAs($designer)
            ->post(route('designer.job-pool.ambil', $order->id));

        $response->assertSessionHasErrors('error');
        
        $order->refresh();
        $this->assertNull($order->designer_id);
        $this->assertEquals(Order::STATUS_WAITING_DESIGNER, $order->status);
    }

    public function test_designer_can_view_job_detail_page(): void
    {
        $designer = $this->createUser('designer');
        $order = $this->createOrder(['status' => Order::STATUS_WAITING_DESIGNER]);

        $response = $this->actingAs($designer)->get(route('designer.job-pool.show', $order->id));
        $response->assertOk();
        $response->assertSee($order->order_code);
    }

    public function test_designer_can_view_sukses_page_after_taking_job(): void
    {
        $designer = $this->createUser('designer');
        $order = $this->createOrder(['status' => Order::STATUS_IN_PROGRESS, 'designer_id' => $designer->id]);

        $response = $this->actingAs($designer)->get(route('designer.job-pool.sukses', $order->id));
        $response->assertOk();
    }

    public function test_designer_can_download_reference_file(): void
    {
        $designer = $this->createUser('designer');
        $order = $this->createOrder(['status' => Order::STATUS_WAITING_DESIGNER]);
        
        $reference = OrderReference::create([
            'order_id' => $order->id,
            'type' => 'file',
            'file_url' => 'r2/path/test.pdf',
            'file_name' => 'test.pdf'
        ]);

        Http::fake([
            '*' => Http::response('fake-file-content', 200),
        ]);

        $response = $this->actingAs($designer)
            ->get(route('designer.job-pool.reference.download', [$order->id, $reference->id]));

        $response->assertOk();
    }

    public function test_designer_can_preview_image_reference(): void
    {
        $designer = $this->createUser('designer');
        $order = $this->createOrder(['status' => Order::STATUS_WAITING_DESIGNER]);
        
        $reference = OrderReference::create([
            'order_id' => $order->id,
            'type' => 'file',
            'file_url' => 'r2/path/test.jpg',
            'file_name' => 'test.jpg',
            'mime_type' => 'image/jpeg'
        ]);

        Storage::fake('r2');
        Storage::disk('r2')->put('path/test.jpg', 'fake-image-content');

        Http::fake([
            '*' => Http::response('fake-image-content', 200),
        ]);

        $response = $this->actingAs($designer)
            ->getJson(route('designer.job-pool.reference.preview', [$order->id, $reference->id]));

        $response->assertOk();
        $response->assertJsonStructure(['url', 'name']);
    }

    public function test_designer_cannot_preview_non_image_file(): void
    {
        $designer = $this->createUser('designer');
        $order = $this->createOrder(['status' => Order::STATUS_WAITING_DESIGNER]);
        
        $reference = OrderReference::create([
            'order_id' => $order->id,
            'type' => 'file',
            'file_url' => 'r2/path/test.pdf',
            'file_name' => 'test.pdf',
            'mime_type' => 'application/pdf'
        ]);

        $response = $this->actingAs($designer)
            ->getJson(route('designer.job-pool.reference.preview', [$order->id, $reference->id]));

        $response->assertStatus(404);
    }
}