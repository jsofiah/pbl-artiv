<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();

            $table->uuid('sender_id');
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();

            $table->string('type', 20)->default('text');
            $table->text('body')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->text('flag_reason')->nullable();
            $table->timestampTz('read_at')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('conversation_id', 'idx_messages_conversation');
            $table->index('sender_id', 'idx_messages_sender');
        });

        DB::statement("ALTER TABLE messages ADD CONSTRAINT messages_type_check CHECK (type IN ('text','file','deliverable','system'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};