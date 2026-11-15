<?php

namespace Tests\Unit\Models;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Scope unread() hanya return yang read_at null
    // ============================================================
    public function test_scope_unread_returns_only_unread_notifications(): void
    {
        $user = $this->createUser();

        $unread = Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_NEW_ORDER,
            'title'   => 'Pesanan Baru',
            'body'    => 'Anda mendapat pesanan baru',
            'read_at' => null,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_PAYMENT,
            'title'   => 'Pembayaran',
            'body'    => 'Pembayaran diterima',
            'read_at' => now(),
        ]);

        $unreadNotifications = Notification::unread()->get();

        $this->assertCount(1, $unreadNotifications);
        $this->assertEquals($unread->id, $unreadNotifications->first()->id);
    }

    // ============================================================
    // Test #2: isRead() false, markAsRead() ubah jadi true
    // ============================================================
    public function test_is_read_and_mark_as_read(): void
    {
        $user = $this->createUser();
        $notification = Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_ORDER_COMPLETED,
            'title'   => 'Order Selesai',
            'body'    => 'Pesanan telah selesai',
        ]);

        $this->assertFalse($notification->isRead());
        $this->assertNull($notification->read_at);

        $notification->markAsRead();
        $notification->refresh();

        $this->assertTrue($notification->isRead());
        $this->assertNotNull($notification->read_at);
    }

    // ============================================================
    // Test #3: markAsRead() idempoten (tidak ubah jika sudah read)
    // ============================================================
    public function test_mark_as_read_does_not_change_timestamp_if_already_read(): void
    {
        $user = $this->createUser();
        $notification = Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_PAYMENT,
            'title'   => 'Pembayaran',
            'body'    => 'Pembayaran',
            'read_at' => now(),
        ]);
        $originalReadAt = $notification->read_at;

        // Panggil markAsRead lagi
        $notification->markAsRead();
        $notification->refresh();

        // read_at tidak berubah
        $this->assertEquals(
            $originalReadAt->toDateTimeString(),
            $notification->read_at->toDateTimeString()
        );
    }

    // ============================================================
    // Test #4: Relasi user() mengembalikan User yang benar
    // ============================================================
    public function test_user_relation_returns_correct_user(): void
    {
        $user = $this->createUser();
        $notification = Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_SYSTEM,
            'title'   => 'System',
            'body'    => 'Sistem',
        ]);

        $relatedUser = $notification->user;

        $this->assertInstanceOf(User::class, $relatedUser);
        $this->assertEquals($user->id, $relatedUser->id);
    }

    // ============================================================
    // Test #5: Cast data ke array
    // ============================================================
    public function test_data_is_cast_to_array(): void
    {
        $user = $this->createUser();
        $data = ['url' => 'https://example.com', 'order_id' => '123'];

        $notification = Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_PAYMENT,
            'title'   => 'Pembayaran',
            'body'    => 'Pembayaran',
            'data'    => $data,
        ]);

        $notification->refresh();

        $this->assertIsArray($notification->data);
        $this->assertEquals('https://example.com', $notification->data['url']);
        $this->assertEquals('123', $notification->data['order_id']);
    }

    // ============================================================
    // Test #6: uiIcon() return format array [bgClass, svgPath]
    // ============================================================
    public function test_ui_icon_returns_array_with_background_and_path(): void
    {
        $user = $this->createUser();
        $notification = Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_ORDER_COMPLETED,
            'title'   => 'Order Selesai',
            'body'    => 'Selesai',
        ]);

        $icon = $notification->uiIcon();

        $this->assertIsArray($icon);
        $this->assertCount(2, $icon);
        $this->assertIsString($icon[0]); // bg class
        $this->assertIsString($icon[1]); // svg path
        $this->assertStringContainsString('bg-', $icon[0]);
        $this->assertStringContainsString('m', strtolower($icon[1])); // path SVG diawali M
    }

    // ============================================================
    // Test #7: uiIcon() beda tipe → beda ikon
    // ============================================================
    public function test_ui_icon_differs_by_type(): void
    {
        $user = $this->createUser();

        $newOrder = Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_NEW_ORDER,
            'title'   => 'Baru',
            'body'    => 'Baru',
        ]);

        $refund = Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_REFUND_REQUESTED,
            'title'   => 'Refund',
            'body'    => 'Refund',
        ]);

        $newOrderIcon = $newOrder->uiIcon();
        $refundIcon = $refund->uiIcon();

        // Background class berbeda
        $this->assertNotEquals($newOrderIcon[0], $refundIcon[0]);
    }

    // ============================================================
    // HELPER: bikin user
    // ============================================================
    private function createUser(): User
    {
        return User::create([
            'username'  => 'user_' . uniqid(),
            'email'     => 'user_' . uniqid() . '@test.com',
            'full_name' => 'Test User',
            'phone'     => '08123456789',
            'role'      => User::ROLE_CUSTOMER,
            'password'  => bcrypt('password'),
        ]);
    }
}