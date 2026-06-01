<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add advertiser_id and promotion to candidatures
        Schema::table('candidatures', function (Blueprint $table) {
            $table->foreignId('advertiser_id')->nullable()->constrained('advertisers')->nullOnDelete();
            $table->string('promotion')->nullable();
        });

        // Add advertiser_id and promotion to etudiants
        Schema::table('etudiants', function (Blueprint $table) {
            $table->foreignId('advertiser_id')->nullable()->constrained('advertisers')->nullOnDelete();
            $table->string('promotion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropForeign(['advertiser_id']);
            $table->dropColumn(['advertiser_id', 'promotion']);
        });

        Schema::table('etudiants', function (Blueprint $table) {
            $table->dropForeign(['advertiser_id']);
            $table->dropColumn(['advertiser_id', 'promotion']);
        });
    }
};
