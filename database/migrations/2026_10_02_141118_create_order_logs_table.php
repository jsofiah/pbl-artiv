<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->uuid('actor_id')->nullable();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();

            $table->string('action', 50);
            $table->string('status', 30)->nullable();
            $table->text('note')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('order_id', 'idx_order_logs_order');
            $table->index('action', 'idx_order_logs_action');
            $table->index(['actor_id', 'action'], 'idx_order_logs_actor_action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_logs');
    }
};