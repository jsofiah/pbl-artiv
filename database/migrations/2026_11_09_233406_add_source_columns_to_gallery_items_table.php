<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->foreignUuid('order_id')
                ->nullable()
                ->after('product_id')
                ->constrained('orders')
                ->nullOnDelete();

            $table->foreignUuid('message_attachment_id')
                ->nullable()
                ->after('order_id')
                ->constrained('message_attachments')
                ->nullOnDelete();

            $table->foreignUuid('created_by')
                ->nullable()
                ->after('is_active')
                ->constrained('users')
                ->nullOnDelete();

            $table->unique('message_attachment_id');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropUnique(['message_attachment_id']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('message_attachment_id');
            $table->dropConstrainedForeignId('order_id');
        });
    }
};