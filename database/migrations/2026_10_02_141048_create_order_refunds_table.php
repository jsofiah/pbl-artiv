<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('order_id')->unique();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('users')->restrictOnDelete();

            $table->string('bank_name', 100);
            $table->string('bank_account_number', 50);
            $table->string('bank_account_name', 150);
            $table->decimal('amount', 14, 2);

            $table->string('status', 20)->default('pending');

            $table->uuid('processed_by')->nullable();
            $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestampTz('processed_at')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('customer_id', 'idx_refunds_customer');
            $table->index('status', 'idx_refunds_status');
        });

        DB::statement("ALTER TABLE order_refunds ADD CONSTRAINT order_refunds_status_check CHECK (status IN ('pending','completed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
    }
};