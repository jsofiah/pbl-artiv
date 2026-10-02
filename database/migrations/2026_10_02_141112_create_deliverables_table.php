<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliverables', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->uuid('message_id')->unique();
            $table->foreign('message_id')->references('id')->on('messages')->cascadeOnDelete();

            $table->string('status', 30)->default('pending');
            $table->text('revision_note')->nullable();
            $table->timestampTz('approved_at')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('order_id', 'idx_deliverables_order');
        });

        DB::statement("ALTER TABLE deliverables ADD CONSTRAINT deliverables_status_check CHECK (status IN ('pending','approved','revision_requested'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverables');
    }
};