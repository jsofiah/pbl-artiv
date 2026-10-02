<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->string('type', 20)->default('order');
            $table->string('method', 50);
            $table->decimal('amount', 14, 2);
            $table->text('proof_url')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestampTz('paid_at')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('order_id', 'idx_payments_order');
            $table->index('type', 'idx_payments_type');
        });

        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_type_check CHECK (type IN ('order','revision_fee'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('pending','berhasil','gagal'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};