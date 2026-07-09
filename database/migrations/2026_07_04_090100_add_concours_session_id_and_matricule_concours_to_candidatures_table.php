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
        Schema::table('candidatures', function (Blueprint $table) {
            $table->foreignId('concours_session_id')->nullable()->after('annee_scolaire_id')
                ->constrained('concours_sessions')->nullOnDelete();
            $table->string('matricule_concours')->nullable()->unique()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('concours_session_id');
            $table->dropColumn('matricule_concours');
        });
    }
};
