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
        Schema::create('jours_feries', function (Blueprint $row) {
            $row->id();
            $row->string('titre');
            $row->string('slug')->unique();
            $row->date('date');
            $row->boolean('est_recurrent')->default(false);
            $row->unsignedBigInteger('annee_scolaire_id')->nullable();
            $row->text('description')->nullable();
            $row->timestamps();

            $row->foreign('annee_scolaire_id')->references('id')->on('annee_scolaires')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jours_feries');
    }
};
