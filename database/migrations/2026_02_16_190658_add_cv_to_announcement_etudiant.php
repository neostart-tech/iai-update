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
        Schema::table('announcement_etudiant', function (Blueprint $table) {
            $table->string('cv_path')->nullable()->after('etudiant_id');
            $table->string('lettre_path')->nullable()->after('cv_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcement_etudiant', function (Blueprint $table) {
            $table->dropColumn(['cv_path', 'lettre_path']);
        });
    }
};
