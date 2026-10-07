<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

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

    public function down(): void
    {
        DB::statement('ALTER TABLE public.orders DROP CONSTRAINT IF EXISTS orders_status_check');

        DB::statement("
            ALTER TABLE public.orders
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
    }
};