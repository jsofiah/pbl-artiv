<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_splits', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('order_id')->unique();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->uuid('designer_id');
            $table->foreign('designer_id')->references('id')->on('users')->restrictOnDelete();

            $table->string('split_type', 20)->default('normal');
            $table->decimal('commission_rate', 5, 2)->default(10.00);

            $table->decimal('gross_amount', 14, 2);
            $table->decimal('commission_amount', 14, 2);
            $table->decimal('designer_earning', 14, 2);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('designer_id', 'idx_order_splits_designer');
        });

        DB::statement("ALTER TABLE order_splits ADD CONSTRAINT order_splits_split_type_check CHECK (split_type IN ('normal','reassignment'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('order_splits');
    }
};