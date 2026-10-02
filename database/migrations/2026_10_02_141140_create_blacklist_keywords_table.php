<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist_keywords', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('keyword', 100);
            $table->string('type', 20);
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            // $table->check(...) dihapus, method ini tidak ada
        });

        // CHECK constraint ditaruh setelah tabel dibuat
        DB::statement("
            ALTER TABLE blacklist_keywords
            ADD CONSTRAINT blacklist_keywords_type_check
            CHECK (type IN ('link','phone','email','word'))
        ");

        DB::statement("
            CREATE INDEX idx_blacklist_active
            ON blacklist_keywords (is_active)
            WHERE is_active = true
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist_keywords');
    }
};