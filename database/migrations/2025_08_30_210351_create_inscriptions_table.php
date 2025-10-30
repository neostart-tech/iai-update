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
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_table', 50)->index();
            $table->unsignedSmallInteger('annee_bac');
            $table->text('lettre_motivation');
            $table->string('serie', 10);
            $table->string('email')->nullable();
            $table->string('phone1', 20);
            $table->string('phone2', 20);
            $table->string('phone3', 20)->nullable();
            $table->string('tuteur_lieu', 150);
            $table->enum('accepte', ['accepte', 'refuse']);
            $table->string('certificat_medical_path')->nullable();
            $table->json('bulletins_lycee_paths');
            $table->string('releve_bac1_path');
            $table->string('releve_bac2_path');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
