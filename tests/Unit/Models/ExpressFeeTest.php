<?php

namespace Tests\Unit\Models;

use App\Models\ExpressFee;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpressFeeTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Scope active() hanya return yang is_active = true
    // ============================================================
    public function test_scope_active_returns_only_active_fees(): void
    {
        // Bikin 2 fee: 1 aktif, 1 tidak aktif
        $activeFee = ExpressFee::create([
            'name'      => 'Express 1 Hari',
            'fee'       => 20000,
            'is_active' => true,
        ]);

        ExpressFee::create([
            'name'      => 'Express 3 Hari (nonaktif)',
            'fee'       => 10000,
            'is_active' => false,
        ]);

        $activeFees = ExpressFee::active()->get();

        $this->assertCount(1, $activeFees);
        $this->assertEquals($activeFee->id, $activeFees->first()->id);
        $this->assertTrue($activeFees->first()->is_active);
    }

    // ============================================================
    // Test #2: Cast fee ke decimal string
    // ============================================================
    public function test_fee_is_cast_to_decimal(): void
    {
        $fee = ExpressFee::create([
            'name'      => 'Express Test',
            'fee'       => 15000.50,
            'is_active' => true,
        ]);

        $fee->refresh();

        // decimal:2 → string "15000.50"
        $this->assertEquals('15000.50', $fee->fee);
    }

    // ============================================================
    // Test #3: Cast is_active ke boolean
    // ============================================================
    public function test_is_active_is_cast_to_boolean(): void
    {
        $fee = ExpressFee::create([
            'name'      => 'Express Test',
            'fee'       => 10000,
            'is_active' => 1, // kirim int, harus jadi bool true
        ]);

        $fee->refresh();

        $this->assertTrue($fee->is_active);
        $this->assertIsBool($fee->is_active);

        // Test false juga
        $fee2 = ExpressFee::create([
            'name'      => 'Express Test 2',
            'fee'       => 10000,
            'is_active' => 0,
        ]);
        $fee2->refresh();

        $this->assertFalse($fee2->is_active);
        $this->assertIsBool($fee2->is_active);
    }

    // ============================================================
    // Test #4: Relasi orders() mengembalikan koleksi Order
    // ============================================================
    public function test_orders_relation_returns_orders(): void
    {
        $fee = ExpressFee::create([
            'name'      => 'Express Test',
            'fee'       => 10000,
            'is_active' => true,
        ]);

        // Bikin data berantai untuk order yang memakai express fee ini
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

        // Bikin 2 order dengan express_fee_id = $fee->id
        Order::create([
            'order_code'    => 'ORD-' . uniqid(),
            'customer_id'   => $customer->id,
            'designer_id'   => $designer->id,
            'product_id'    => $product->id,
            'unit_price'    => 50000,
            'quantity'      => 1,
            'total_price'   => 70000,
            'is_express'    => true,
            'express_fee_id'=> $fee->id,
            'express_fee'   => 20000,
            'status'        => Order::STATUS_PENDING,
        ]);
        Order::create([
            'order_code'    => 'ORD-' . uniqid(),
            'customer_id'   => $customer->id,
            'designer_id'   => $designer->id,
            'product_id'    => $product->id,
            'unit_price'    => 50000,
            'quantity'      => 1,
            'total_price'   => 70000,
            'is_express'    => true,
            'express_fee_id'=> $fee->id,
            'express_fee'   => 20000,
            'status'        => Order::STATUS_PENDING,
        ]);

        $orders = $fee->orders()->get();

        $this->assertCount(2, $orders);
        $this->assertInstanceOf(Order::class, $orders->first());
    }

    // ============================================================
    // Test #5: Scope active() return kosong kalau tidak ada yang aktif
    // ============================================================
    public function test_scope_active_returns_empty_when_no_active_fees(): void
    {
        ExpressFee::create([
            'name'      => 'Nonaktif 1',
            'fee'       => 10000,
            'is_active' => false,
        ]);
        ExpressFee::create([
            'name'      => 'Nonaktif 2',
            'fee'       => 20000,
            'is_active' => false,
        ]);

        $activeFees = ExpressFee::active()->get();

        $this->assertCount(0, $activeFees);
        $this->assertTrue($activeFees->isEmpty());
    }
}