<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses; 
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;


#[RunTestsInSeparateProcesses] 
class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Order::flushEventListeners();
        Order::clearBootedModels();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Order::flushEventListeners();
        Order::clearBootedModels();
        Cache::flush();

        parent::tearDown();
    }

    // ══════════════════════════════════════════════════════════════════
    //  GRUP 1 — FILLABLE & CASTS (3 test)
    // ══════════════════════════════════════════════════════════════════

    public function test_fillable_mencakup_kolom_wajib(): void
    {
        $fillable = (new Order())->getFillable();

        $wajib = [
            'order_code',
            'customer_id',
            'designer_id',
            'product_id',
            'unit_price',
            'total_price',
            'status',
            'is_urgent',
            'deadline',
            'revision_count',
        ];

        foreach ($wajib as $kolom) {
            $this->assertContains($kolom, $fillable, "Kolom '{$kolom}' harus fillable");
        }
    }

    public function test_casts_decimal_untuk_kolom_harga(): void
    {
        $casts = (new Order())->getCasts();

        $this->assertEquals('decimal:2', $casts['unit_price']);
        $this->assertEquals('decimal:2', $casts['express_fee']);
        $this->assertEquals('decimal:2', $casts['total_price']);
    }

    public function test_casts_boolean_integer_datetime(): void
    {
        $casts = (new Order())->getCasts();

        // Boolean
        $this->assertEquals('boolean', $casts['is_express']);
        $this->assertEquals('boolean', $casts['is_late']);
        $this->assertEquals('boolean', $casts['is_urgent']);

        // Integer
        $this->assertEquals('integer', $casts['paid_revision_quota']);

        // Datetime
        $this->assertEquals('datetime', $casts['deadline']);
        $this->assertEquals('datetime', $casts['completed_at']);
        $this->assertEquals('datetime', $casts['cancelled_at']);
    }

    // ══════════════════════════════════════════════════════════════════
    //  GRUP 2 — RELASI (4 test)
    // ══════════════════════════════════════════════════════════════════

    public function test_relasi_customer_belongs_to_user(): void
    {
        $order = new Order();
        $relasi = $order->customer();

        $this->assertInstanceOf(BelongsTo::class, $relasi);
        $this->assertEquals('customer_id', $relasi->getForeignKeyName());
        $this->assertInstanceOf(User::class, $relasi->getRelated());
    }

    public function test_relasi_designer_belongs_to_user(): void
    {
        $order = new Order();
        $relasi = $order->designer();

        $this->assertInstanceOf(BelongsTo::class, $relasi);
        $this->assertEquals('designer_id', $relasi->getForeignKeyName());
    }

    public function test_relasi_payments_has_many(): void
    {
        $order = new Order();
        $relasi = $order->payments();

        $this->assertInstanceOf(HasMany::class, $relasi);
        $this->assertEquals('order_id', $relasi->getForeignKeyName());
    }

    public function test_relasi_refund_has_one(): void
    {
        $order = new Order();
        $relasi = $order->refund();

        $this->assertInstanceOf(HasOne::class, $relasi);
        $this->assertEquals('order_id', $relasi->getForeignKeyName());
    }

    // ══════════════════════════════════════════════════════════════════
    //  GRUP 3 — STATUS HELPERS (6 test)
    // ══════════════════════════════════════════════════════════════════

    public function test_isPending_true_hanya_ketika_status_pending(): void
    {
        $pending = new Order(['status' => Order::STATUS_PENDING]);
        $this->assertTrue($pending->isPending());

        $other = new Order(['status' => Order::STATUS_IN_PROGRESS]);
        $this->assertFalse($other->isPending());
    }

    public function test_isInProgress_true_hanya_ketika_status_in_progress(): void
    {
        $order = new Order(['status' => Order::STATUS_IN_PROGRESS]);
        $this->assertTrue($order->isInProgress());

        $other = new Order(['status' => Order::STATUS_COMPLETED]);
        $this->assertFalse($other->isInProgress());
    }

    public function test_isCompleted_true_hanya_ketika_status_completed(): void
    {
        $order = new Order(['status' => Order::STATUS_COMPLETED]);
        $this->assertTrue($order->isCompleted());

        $other = new Order(['status' => Order::STATUS_CANCELLED]);
        $this->assertFalse($other->isCompleted());
    }

    public function test_isCancelled_dan_isRefunded(): void
    {
        $cancelled = new Order(['status' => Order::STATUS_CANCELLED]);
        $this->assertTrue($cancelled->isCancelled());
        $this->assertFalse($cancelled->isRefunded());

        $refunded = new Order(['status' => Order::STATUS_REFUNDED]);
        $this->assertTrue($refunded->isRefunded());
        $this->assertFalse($refunded->isCancelled());
    }

    public function test_isUrgent_cek_flag_is_urgent(): void
    {
        $urgent = new Order(['is_urgent' => true]);
        $this->assertTrue($urgent->isUrgent());

        $normal = new Order(['is_urgent' => false]);
        $this->assertFalse($normal->isUrgent());
    }

    public function test_konstanta_aktif_dan_riwayat(): void
    {
        $this->assertContains(Order::STATUS_PENDING, Order::AKTIF);
        $this->assertContains(Order::STATUS_IN_PROGRESS, Order::AKTIF);
        $this->assertContains(Order::STATUS_WAITING_CUSTOMER_DECISION, Order::AKTIF);

        $this->assertNotContains(Order::STATUS_COMPLETED, Order::AKTIF);
        $this->assertNotContains(Order::STATUS_CANCELLED, Order::AKTIF);

        $this->assertContains(Order::STATUS_COMPLETED, Order::RIWAYAT);
        $this->assertContains(Order::STATUS_CANCELLED, Order::RIWAYAT);
        $this->assertContains(Order::STATUS_REFUNDED, Order::RIWAYAT);
    }

    // ══════════════════════════════════════════════════════════════════
    //  GRUP 4 — BISNIS: REVISI (3 test)
    // ══════════════════════════════════════════════════════════════════

    public function test_canRequestFreeRevision_true_ketika_revision_count_kurang_3(): void
    {
        $order0 = new Order(['revision_count' => 0]);
        $this->assertTrue($order0->canRequestFreeRevision());

        $order1 = new Order(['revision_count' => 1]);
        $this->assertTrue($order1->canRequestFreeRevision());

        $order2 = new Order(['revision_count' => 2]);
        $this->assertTrue($order2->canRequestFreeRevision());
    }

    public function test_canRequestFreeRevision_false_ketika_revision_count_3_atau_lebih(): void
    {
        $order3 = new Order(['revision_count' => 3]);
        $this->assertFalse($order3->canRequestFreeRevision());

        $order5 = new Order(['revision_count' => 5]);
        $this->assertFalse($order5->canRequestFreeRevision());
    }

    public function test_freeRevisionRemaining_kalkulasi_dan_minimal_0(): void
    {
        $order0 = new Order(['revision_count' => 0]);
        $this->assertEquals(3, $order0->freeRevisionRemaining());

        $order2 = new Order(['revision_count' => 2]);
        $this->assertEquals(1, $order2->freeRevisionRemaining());

        // Saat sudah penuh → min 0, tidak minus
        $order5 = new Order(['revision_count' => 5]);
        $this->assertEquals(0, $order5->freeRevisionRemaining());
    }

    // ══════════════════════════════════════════════════════════════════
    //  GRUP 5 — BISNIS: REVISI BERBAYAR (1 test — sangat padat)
    // ══════════════════════════════════════════════════════════════════

    public function test_needsPaidRevision_dan_paid_revision_quota(): void
    {
        // Belum penuh → tidak butuh bayar
        $fresh = new Order(['revision_count' => 2, 'paid_revision_quota' => 0]);
        $this->assertFalse($fresh->needsPaidRevision());
        $this->assertFalse($fresh->needsPaidRevisionPayment());
        $this->assertFalse($fresh->canRequestPaidRevision());

        // Sudah penuh & tidak punya kuota → butuh bayar
        $needPay = new Order(['revision_count' => 3, 'paid_revision_quota' => 0]);
        $this->assertTrue($needPay->needsPaidRevision());
        $this->assertTrue($needPay->needsPaidRevisionPayment());
        $this->assertFalse($needPay->canRequestPaidRevision());

        // Sudah penuh tapi punya kuota bayar → bisa request paid revision
        $hasQuota = new Order(['revision_count' => 3, 'paid_revision_quota' => 2]);
        $this->assertTrue($hasQuota->canRequestPaidRevision());
        $this->assertFalse($hasQuota->needsPaidRevisionPayment());
    }

    // ══════════════════════════════════════════════════════════════════
    //  GRUP 6 — BISNIS: DEADLINE (2 test)
    // ══════════════════════════════════════════════════════════════════

    public function test_isDeadlineMissed_true_ketika_deadline_lewat_dan_belum_final(): void
    {
        $order = new Order([
            'deadline' => now()->subDays(2),
            'status'   => Order::STATUS_IN_PROGRESS,
        ]);
        $this->assertTrue($order->isDeadlineMissed());
    }

    public function test_isDeadlineMissed_false_ketika_status_final_atau_belum_lewat(): void
    {
        // Status final → deadline lewat tidak dianggap missed
        $completed = new Order([
            'deadline' => now()->subDays(2),
            'status'   => Order::STATUS_COMPLETED,
        ]);
        $this->assertFalse($completed->isDeadlineMissed());

        // Deadline belum lewat
        $future = new Order([
            'deadline' => now()->addDays(3),
            'status'   => Order::STATUS_IN_PROGRESS,
        ]);
        $this->assertFalse($future->isDeadlineMissed());

        // Tanpa deadline
        $noDeadline = new Order(['status' => Order::STATUS_IN_PROGRESS]);
        $this->assertFalse($noDeadline->isDeadlineMissed());
    }

    // ══════════════════════════════════════════════════════════════════
    //  GRUP 7 — BISNIS: REASSIGN (1 test)
    // ══════════════════════════════════════════════════════════════════

    public function test_hasBeenReassigned_cek_reassign_count(): void
    {
        $fresh = new Order(['reassign_count' => 0]);
        $this->assertFalse($fresh->hasBeenReassigned());

        $reassigned = new Order(['reassign_count' => 1]);
        $this->assertTrue($reassigned->hasBeenReassigned());
    }
}
