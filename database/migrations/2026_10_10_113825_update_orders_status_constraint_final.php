<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE public.orders DROP CONSTRAINT IF EXISTS orders_status_check');

        DB::statement("
            ALTER TABLE public.orders
            ADD CONSTRAINT orders_status_check
            CHECK (status IN (
                'pending',
                'waiting_designer',
                'in_progress',
                'deliverable_sent',
                'revision_needed',
                'waiting_customer_decision',
                'refund_requested',
                'refunded',
                'completed',
                'cancelled'
            ))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE public.orders DROP CONSTRAINT IF EXISTS orders_status_check');

        DB::statement("
            ALTER TABLE public.orders
            ADD CONSTRAINT orders_status_check
            CHECK (status IN (
                'pending',
                'reassignment_needed',
                'in_progress',
                'revision_needed',
                'deliverable_sent',
                'waiting_customer_decision',
                'approved',
                'completed',
                'refund_requested',
                'refunded',
                'cancelled'
            ))
        ");
    }
};