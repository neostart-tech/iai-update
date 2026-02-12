<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ue_validations', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->foreignId('etudiant_id')->constrained()->onDelete('cascade');
            $table->foreignId('unite_enseignement_id')->constrained()->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained();
            $table->foreignId('periode_id')->constrained();
            $table->decimal('moyenne', 5, 2);
            $table->integer('credit_obtenu');
            $table->boolean('validee')->default(false);
            $table->enum('type_validation', ['normale', 'rattrapage', 'compensation'])->nullable();
            $table->timestamps();
            
            $table->unique(['etudiant_id', 'unite_enseignement_id', 'annee_scolaire_id', 'periode_id'], 'ue_validation_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ue_validations');
    }
};