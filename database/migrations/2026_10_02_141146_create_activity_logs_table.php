<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->string('action', 100);
            $table->string('target_type', 50)->nullable();
            $table->uuid('target_id')->nullable();
            $table->text('description')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('user_id', 'idx_activity_logs_user');
            $table->index(['target_type', 'target_id'], 'idx_activity_logs_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};