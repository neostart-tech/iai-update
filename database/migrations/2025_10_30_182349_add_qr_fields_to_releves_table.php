<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('releves', function (Blueprint $table) {
            $table->string('qr_hash')->nullable()->unique()->after('date_publication');
            $table->json('verification_data')->nullable()->after('qr_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('releves', function (Blueprint $table) {
            $table->dropColumn(['qr_hash', 'verification_data']);
        });
    }
};
