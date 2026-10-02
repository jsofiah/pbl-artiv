<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('order_code', 30)->unique();

            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('users')->restrictOnDelete();

            $table->uuid('designer_id')->nullable();
            $table->foreign('designer_id')->references('id')->on('users')->nullOnDelete();

            $table->uuid('product_id');
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();

            $table->uuid('product_tier_id')->nullable();
            $table->foreign('product_tier_id')->references('id')->on('product_tiers')->restrictOnDelete();

            $table->decimal('unit_price', 14, 2);
            $table->integer('quantity')->default(1);

            $table->timestampTz('deadline')->nullable();
            $table->timestampTz('assigned_at')->nullable();

            $table->boolean('is_express')->default(false);
            $table->uuid('express_fee_id')->nullable();
            $table->foreign('express_fee_id')->references('id')->on('express_fees')->nullOnDelete();
            $table->decimal('express_fee', 14, 2)->default(0);

            $table->text('brief_note')->nullable();
            $table->decimal('total_price', 14, 2);
            $table->string('status', 30)->default('pending');

            $table->integer('revision_count')->default(0);

            $table->boolean('is_late')->default(false);
            $table->timestampTz('late_at')->nullable();

            // Reassignment fields
            $table->text('cannot_continue_reason')->nullable();
            $table->timestampTz('cannot_continue_at')->nullable();
            $table->string('customer_decision', 20)->nullable();
            $table->timestampTz('customer_decision_at')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->timestampTz('original_deadline')->nullable();
            $table->integer('reassign_count')->default(0);

            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('customer_id', 'idx_orders_customer');
            $table->index('designer_id', 'idx_orders_designer');
            $table->index('status', 'idx_orders_status');
        });

        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT orders_status_check
            CHECK (status IN (
                'pending',
                'in_progress',
                'waiting_customer_decision',
                'reassignment_needed',
                'refund_requested',
                'refunded',
                'completed',
                'cancelled'
            ))
        ");

        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT orders_customer_decision_check
            CHECK (customer_decision IN ('refund','continue') OR customer_decision IS NULL)
        ");

        DB::statement("
            create index idx_orders_late
            on orders(is_late)
            where is_late = true
        ");

        DB::statement("
            create index idx_orders_urgent
            on orders(is_urgent)
            where is_urgent = true
        ");

        DB::statement("
            create index idx_orders_customer_decision
            on orders(customer_decision)
            where customer_decision is not null
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};