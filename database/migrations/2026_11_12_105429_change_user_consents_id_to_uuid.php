<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus primary key lama (integer)
        Schema::table('user_consents', function (Blueprint $table) {
            $table->dropPrimary();
        });

        // 2. Hapus kolom id lama
        Schema::table('user_consents', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        // 3. Tambah kolom id baru bertipe UUID
        Schema::table('user_consents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'))->first();
        });
    }

    public function down(): void
    {
        // Rollback: hapus UUID, kembalikan integer
        Schema::table('user_consents', function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
        });

        Schema::table('user_consents', function (Blueprint $table) {
            $table->id()->first();
        });
    }
};