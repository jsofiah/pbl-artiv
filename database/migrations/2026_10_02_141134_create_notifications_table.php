<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->string('type', 50);
            $table->string('title', 200);
            $table->text('body')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestampTz('read_at')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('user_id', 'idx_notifications_user');
        });

        DB::statement("
            create index idx_notifications_unread
            on notifications(user_id)
            where read_at is null
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};