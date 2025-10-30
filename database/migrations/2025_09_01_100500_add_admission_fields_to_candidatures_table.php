<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->string('numero_table')->nullable()->after('prenom');
            $table->unsignedSmallInteger('annee_bac')->nullable()->after('numero_table');
            $table->string('serie', 5)->nullable()->after('annee_bac');
            $table->text('lettre_motivation')->nullable()->after('serie');
            $table->string('tel2')->nullable()->after('tel');
            $table->string('tel3')->nullable()->after('tel2');
        });
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn(['numero_table', 'annee_bac', 'serie', 'lettre_motivation', 'tel2', 'tel3']);
        });
    }
};
