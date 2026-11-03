<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCustomer());
        $this->assertFalse($admin->isDesigner());
    }

    public function test_is_customer_returns_true_for_customer_role(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());
        $this->assertFalse($customer->isDesigner());
    }

    public function test_is_designer_returns_true_for_designer_role(): void
    {
        $designer = $this->createUser(User::ROLE_DESIGNER);

        $this->assertTrue($designer->isDesigner());
        $this->assertFalse($designer->isAdmin());
        $this->assertFalse($designer->isCustomer());
    }

    public function test_designer_is_inactive_when_is_active_false(): void
    {
        $designer = $this->createUser(User::ROLE_DESIGNER, ['is_active' => false]);
        $designer->refresh();

        $this->assertTrue($designer->isDesigner());
        $this->assertFalse($designer->is_active);

        $activeDesigner = $this->createUser(User::ROLE_DESIGNER, ['is_active' => true]);
        $activeDesigner->refresh();

        $this->assertTrue($activeDesigner->is_active);
    }

    public function test_home_returns_admin_dashboard_for_admin(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);

        $this->assertSame(route('admin.dashboard'), $admin->home());
    }

    public function test_home_returns_designer_dashboard_for_designer(): void
    {
        $designer = $this->createUser(User::ROLE_DESIGNER);

        $this->assertSame(route('designer.dashboard'), $designer->home());
    }

    public function test_home_returns_customer_beranda_for_customer(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);

        $this->assertSame(route('customer.beranda'), $customer->home());
    }

    public function test_password_is_hashed_when_saved(): void
    {
        $user = $this->createUser(User::ROLE_CUSTOMER, ['password' => 'rahasia123']);

        $this->assertNotEquals('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_last_login_at_is_cast_to_carbon_instance(): void
    {
        $user = $this->createUser(User::ROLE_CUSTOMER, [
            'last_login_at' => '2026-11-03 10:00:00',
        ]);
        $user->refresh();

        $this->assertInstanceOf(Carbon::class, $user->last_login_at);
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $user = $this->createUser(User::ROLE_CUSTOMER, ['is_active' => 1]);
        $user->refresh();

        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
    }

    public function test_password_and_remember_token_are_hidden_from_array(): void
    {
        $user = $this->createUser(User::ROLE_CUSTOMER, ['remember_token' => 'token123']);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
        $this->assertArrayHasKey('email', $array);
    }

    public function test_user_uses_uuid_as_primary_key(): void
    {
        $user = $this->createUser(User::ROLE_CUSTOMER);

        $this->assertTrue(Str::isUuid($user->id));
        $this->assertFalse($user->getIncrementing());
        $this->assertSame('string', $user->getKeyType());
    }

    public function test_role_constants_have_expected_values(): void
    {
        $this->assertSame('customer', User::ROLE_CUSTOMER);
        $this->assertSame('designer', User::ROLE_DESIGNER);
        $this->assertSame('admin', User::ROLE_ADMIN);
    }

    #[DataProvider('hasManyRelationProvider')]
    public function test_has_many_relations_use_correct_foreign_key(
        string $method,
        string $relatedClass,
        string $foreignKey
    ): void {
        $user = $this->createUser(User::ROLE_CUSTOMER);

        $relation = $user->$method();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf($relatedClass, $relation->getRelated());
        $this->assertSame($foreignKey, $relation->getForeignKeyName());
    }

    public static function hasManyRelationProvider(): array
    {
        return [
            'customerOrders'          => ['customerOrders', \App\Models\Order::class, 'customer_id'],
            'designerOrders'          => ['designerOrders', \App\Models\Order::class, 'designer_id'],
            'conversationsAsCustomer' => ['conversationsAsCustomer', \App\Models\Conversation::class, 'customer_id'],
            'conversationsAsDesigner' => ['conversationsAsDesigner', \App\Models\Conversation::class, 'designer_id'],
            'messages'                => ['messages', \App\Models\Message::class, 'sender_id'],
            'reviewsAsCustomer'       => ['reviewsAsCustomer', \App\Models\Review::class, 'customer_id'],
            'reviewsAsDesigner'       => ['reviewsAsDesigner', \App\Models\Review::class, 'designer_id'],
            'refundsAsCustomer'       => ['refundsAsCustomer', \App\Models\OrderRefund::class, 'customer_id'],
            'processedRefunds'        => ['processedRefunds', \App\Models\OrderRefund::class, 'processed_by'],
            'notifications'           => ['notifications', \App\Models\Notification::class, 'user_id'],
            'activityLogs'            => ['activityLogs', \App\Models\ActivityLog::class, 'user_id'],
            'orderSplits'             => ['orderSplits', \App\Models\OrderSplit::class, 'designer_id'],
            'orderLogs'               => ['orderLogs', \App\Models\OrderLog::class, 'actor_id'],
        ];
    }

    public function test_designer_stats_relation_is_has_one_with_designer_id(): void
    {
        $user = $this->createUser(User::ROLE_DESIGNER);

        foreach (['designerStats', 'designerStat'] as $method) {
            $relation = $user->$method();

            $this->assertInstanceOf(HasOne::class, $relation);
            $this->assertInstanceOf(\App\Models\DesignerStat::class, $relation->getRelated());
            $this->assertSame('designer_id', $relation->getForeignKeyName());
        }
    }

    private function createUser(string $role, array $overrides = []): User
    {
        return User::create(array_merge([
            'username'  => $role . '_' . uniqid(),
            'email'     => $role . '_' . uniqid() . '@test.com',
            'full_name' => 'Test ' . ucfirst($role),
            'phone'     => '08123456789',
            'role'      => $role,
            'password'  => bcrypt('password'),
        ], $overrides));
    }
}