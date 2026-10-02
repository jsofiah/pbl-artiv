<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designer_stats', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('designer_id')->unique();
            $table->foreign('designer_id')->references('id')->on('users')->cascadeOnDelete();

            // Statistik
            $table->integer('total_orders')->default(0);
            $table->integer('completed_orders')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->decimal('total_earning', 14, 2)->default(0.00);

            // Kapasitas & operasional
            $table->integer('daily_order_limit')->default(2);
            $table->integer('max_active_orders')->default(5);
            $table->string('availability_status', 20)->default('available');

            // Keterlambatan & suspend
            $table->integer('late_count')->default(0);
            $table->integer('total_late_count')->default(0);
            $table->integer('suspend_count')->default(0);
            $table->timestampTz('last_suspended_at')->nullable();
            $table->timestampTz('suspended_until')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('availability_status', 'idx_designer_stats_status');
            // $table->check(...) dihapus, constraint sudah ada di DB::statement di bawah
        });

        DB::statement("
            ALTER TABLE designer_stats
            ADD CONSTRAINT designer_stats_availability_status_check
            CHECK (availability_status IN ('available','unavailable','suspended'))
        ");

        DB::statement("
            CREATE INDEX idx_designer_stats_suspended
            ON designer_stats (suspended_until)
            WHERE suspended_until IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('designer_stats');
    }
};