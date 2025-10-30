<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('enseignant_presences')) return;
        Schema::create('enseignant_presences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emploi_du_temps_id');
            $table->unsignedBigInteger('enseignant_id');
            $table->string('statut', 10)->default('present'); // present|retard|absent
            $table->string('commentaire', 500)->nullable();
            $table->timestamps();

            $table->index(['emploi_du_temps_id']);
            $table->index(['enseignant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enseignant_presences');
    }
};
