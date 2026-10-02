<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->text('image_url');
            $table->boolean('is_active')->default(true);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('product_id', 'idx_gallery_product');
        });

        DB::statement("
            create index idx_gallery_active
            on gallery_items(is_active)
            where is_active = true
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};