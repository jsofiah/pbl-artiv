<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_refunds', function (Blueprint $table) {
            $table->string('proof_url')->nullable()->after('status');
            $table->timestamp('proof_uploaded_at')->nullable()->after('proof_url');
        });
    }

    public function down(): void
    {
        Schema::table('order_refunds', function (Blueprint $table) {
            $table->dropColumn(['proof_url', 'proof_uploaded_at']);
        });
    }
};