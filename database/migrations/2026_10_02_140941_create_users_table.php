<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('username', 50)->unique();
            $table->string('email', 255)->unique();
            $table->string('full_name', 150);
            $table->string('phone', 20)->nullable();
            $table->text('avatar_url')->nullable();
            $table->string('role', 20)->default('customer');
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('role', 'idx_users_role');
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('customer','designer','admin'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};