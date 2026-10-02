<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('messages')->cascadeOnDelete();

            $table->text('file_url');
            $table->string('file_name', 255);
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('file_type', 50)->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('message_id', 'idx_msg_attachments_message');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
    }
};