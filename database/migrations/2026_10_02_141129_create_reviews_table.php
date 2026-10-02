<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('order_id')->unique();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('users')->cascadeOnDelete();

            $table->uuid('designer_id');
            $table->foreign('designer_id')->references('id')->on('users')->cascadeOnDelete();

            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_public')->default(true);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('designer_id', 'idx_reviews_designer');
        });

        DB::statement("ALTER TABLE reviews ADD CONSTRAINT reviews_rating_check CHECK (rating BETWEEN 1 AND 5)");
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};