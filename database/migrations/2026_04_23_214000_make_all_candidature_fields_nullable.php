<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->string('nom')->nullable()->change();
            $table->string('prenom')->nullable()->change();
            $table->string('genre')->nullable()->change();
            $table->timestamp('date_naissance')->nullable()->change();
            $table->string('lieu_naissance')->nullable()->change();
            $table->string('nationalite')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('tel')->nullable()->change();
        });

        Schema::table('responsable_frais', function (Blueprint $table) {
            $table->string('nom')->nullable()->change();
            $table->string('prenom')->nullable()->change();
            $table->string('profession')->nullable()->change();
            $table->string('employeur')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('tel')->nullable()->change();
            $table->string('adresse')->nullable()->change();
        });

        Schema::table('tuteurs', function (Blueprint $table) {
            $table->string('nom')->nullable()->change();
            $table->string('prenom')->nullable()->change();
            $table->string('profession')->nullable()->change();
            $table->string('employeur')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('tel')->nullable()->change();
            $table->string('adresse')->nullable()->change();
        });
    }

    public function down(): void
    {
        // On ne revient pas en arrière sur la nullabilité pour éviter des erreurs si des données nulles ont été insérées
    }
};
