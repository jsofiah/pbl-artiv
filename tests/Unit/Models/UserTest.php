<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test #1: isAdmin() membedakan role dengan benar
     */
    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $admin = User::create([
            'username'  => 'admin_' . uniqid(),
            'email'     => 'admin_' . uniqid() . '@test.com',
            'full_name' => 'Test Admin',
            'phone'     => '08123456789',
            'role'      => User::ROLE_ADMIN,
            'password'  => bcrypt('password'),
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCustomer());
        $this->assertFalse($admin->isDesigner());
    }

    /**
     * Test #2: Designer dengan is_active = false → statusnya nonaktif
     */
    public function test_designer_is_inactive_when_is_active_false(): void
    {
        $designer = User::create([
            'username'  => 'designer_' . uniqid(),
            'email'     => 'designer_' . uniqid() . '@test.com',
            'full_name' => 'Test Designer',
            'phone'     => '08123456789',
            'role'      => User::ROLE_DESIGNER,
            'password'  => bcrypt('password'),
            'is_active' => false,
        ]);

        $designer->refresh();

        $this->assertTrue($designer->isDesigner());
        $this->assertFalse($designer->is_active);

        $activeDesigner = User::create([
            'username'  => 'designer_active_' . uniqid(),
            'email'     => 'designer_active_' . uniqid() . '@test.com',
            'full_name' => 'Active Designer',
            'phone'     => '08123456789',
            'role'      => User::ROLE_DESIGNER,
            'password'  => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->assertTrue($activeDesigner->is_active);
    }
}