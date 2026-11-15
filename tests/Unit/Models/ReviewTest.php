<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: isPositive() true untuk rating >= 4, false < 4
    // ============================================================
    public function test_is_positive_returns_true_for_rating_four_or_above(): void
    {
        $positive1 = $this->createReview(['rating' => 5]);
        $this->assertTrue($positive1->isPositive());

        $positive2 = $this->createReview(['rating' => 4]);
        $this->assertTrue($positive2->isPositive());

        $negative1 = $this->createReview(['rating' => 3]);
        $this->assertFalse($negative1->isPositive());

        $negative2 = $this->createReview(['rating' => 1]);
        $this->assertFalse($negative2->isPositive());
    }

    // ============================================================
    // Test #2: Scope public() hanya return is_public = true
    // ============================================================
    public function test_scope_public_returns_only_public_reviews(): void
    {
        $publicReview = $this->createReview(['is_public' => true]);
        $this->createReview(['is_public' => false]);

        $publicReviews = Review::public()->get();

        $this->assertCount(1, $publicReviews);
        $this->assertEquals($publicReview->id, $publicReviews->first()->id);
        $this->assertTrue($publicReviews->first()->is_public);
    }

    // ============================================================
    // Test #3: Cast rating ke integer
    // ============================================================
    public function test_rating_is_cast_to_integer(): void
    {
        $review = $this->createReview(['rating' => '5']); // string
        $review->refresh();

        $this->assertIsInt($review->rating);
        $this->assertEquals(5, $review->rating);
    }

    // ============================================================
    // Test #4: Cast aspects ke array
    // ============================================================
    public function test_aspects_is_cast_to_array(): void
    {
        $aspects = ['komunikasi', 'kualitas', 'tepat_waktu'];
        $review = $this->createReview(['aspects' => $aspects]);
        $review->refresh();

        $this->assertIsArray($review->aspects);
        $this->assertCount(3, $review->aspects);
        $this->assertEquals($aspects, $review->aspects);
    }

    // ============================================================
    // Test #5: Cast is_public ke boolean
    // ============================================================
    public function test_is_public_is_cast_to_boolean(): void
    {
        $review = $this->createReview(['is_public' => 1]);
        $review->refresh();

        $this->assertTrue($review->is_public);
        $this->assertIsBool($review->is_public);

        $review2 = $this->createReview(['is_public' => 0]);
        $review2->refresh();

        $this->assertFalse($review2->is_public);
        $this->assertIsBool($review2->is_public);
    }

    // ============================================================
    // Test #6: Relasi order() mengembalikan Order
    // ============================================================
    public function test_order_relation_returns_correct_order(): void
    {
        $review = $this->createReview();

        $relatedOrder = $review->order;

        $this->assertInstanceOf(Order::class, $relatedOrder);
        $this->assertEquals($review->order_id, $relatedOrder->id);
    }

    // ============================================================
    // Test #7: Relasi customer() mengembalikan User customer
    // ============================================================
    public function test_customer_relation_returns_correct_customer(): void
    {
        $review = $this->createReview();

        $relatedCustomer = $review->customer;

        $this->assertInstanceOf(User::class, $relatedCustomer);
        $this->assertEquals($review->customer_id, $relatedCustomer->id);
        $this->assertEquals(User::ROLE_CUSTOMER, $relatedCustomer->role);
    }

    // ============================================================
    // Test #8: Relasi designer() mengembalikan User designer
    // ============================================================
    public function test_designer_relation_returns_correct_designer(): void
    {
        $review = $this->createReview();

        $relatedDesigner = $review->designer;

        $this->assertInstanceOf(User::class, $relatedDesigner);
        $this->assertEquals($review->designer_id, $relatedDesigner->id);
        $this->assertEquals(User::ROLE_DESIGNER, $relatedDesigner->role);
    }

    // ============================================================
    // Test #9: Konstanta ASPECTS & RATING_LABELS valid
    // ============================================================
    public function test_aspects_and_rating_labels_constants(): void
    {
        // ASPECTS: 3 aspek
        $this->assertCount(3, Review::ASPECTS);
        $this->assertArrayHasKey('komunikasi', Review::ASPECTS);
        $this->assertArrayHasKey('kualitas', Review::ASPECTS);
        $this->assertArrayHasKey('tepat_waktu', Review::ASPECTS);
        $this->assertArrayHasKey('label', Review::ASPECTS['komunikasi']);
        $this->assertArrayHasKey('icon', Review::ASPECTS['komunikasi']);

        // RATING_LABELS: 1-5
        $this->assertCount(5, Review::RATING_LABELS);
        for ($i = 1; $i <= 5; $i++) {
            $this->assertArrayHasKey($i, Review::RATING_LABELS);
        }
        $this->assertEquals('Luar Biasa / Sangat Puas', Review::RATING_LABELS[5]);
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

    private function createOrder(): Order
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);
        $designer = $this->createUser(User::ROLE_DESIGNER);
        $product = Product::create([
            'name'          => 'Test Product ' . uniqid(),
            'description'   => 'Deskripsi test',
            'thumbnail_url' => 'products/test.png',
            'price'         => 50000,
            'is_active'     => true,
        ]);

        return Order::create([
            'order_code'  => 'ORD-' . uniqid(),
            'customer_id' => $customer->id,
            'designer_id' => $designer->id,
            'product_id'  => $product->id,
            'unit_price'  => 50000,
            'quantity'    => 1,
            'total_price' => 50000,
            'status'      => Order::STATUS_COMPLETED,
        ]);
    }

    private function createReview(array $overrides = []): Review
    {
        $order = $this->createOrder();
        $customer = $order->customer;
        $designer = $order->designer;

        return Review::create(array_merge([
            'order_id'    => $order->id,
            'customer_id' => $customer->id,
            'designer_id' => $designer->id,
            'rating'      => 5,
            'aspects'     => ['komunikasi', 'kualitas'],
            'comment'     => 'Desain bagus dan cepat',
            'is_public'   => true,
        ], $overrides));
    }
}