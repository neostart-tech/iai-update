<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidature_field_configs', function (Blueprint $table) {
            $table->boolean('afficher')->default(true)->after('label');
        });

        // numero_bordereau vient d'être ajouté pour une école précise : il doit rester
        // masqué pour toutes les autres tant qu'elles ne l'activent pas explicitement.
        DB::table('candidature_field_configs')
            ->where('champ_key', 'numero_bordereau')
            ->update(['afficher' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidature_field_configs', function (Blueprint $table) {
            $table->dropColumn('afficher');
        });
    }
};
