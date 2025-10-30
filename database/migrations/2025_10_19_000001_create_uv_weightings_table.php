<?php

use App\Models\{Filiere, UniteValeur};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('uv_weightings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(UniteValeur::class);
            $table->foreignIdFor(Filiere::class);
            // Percentages per evaluation type (0-100)
            $table->unsignedTinyInteger('devoir')->default(0);
            $table->unsignedTinyInteger('interrogation')->default(0);
            $table->unsignedTinyInteger('examen')->default(0);
            $table->unsignedTinyInteger('tp')->default(0);
            $table->unsignedTinyInteger('expose')->default(0);
            $table->unique(['unite_valeur_id', 'filiere_id'], 'uv_weightings_unique_uv_filiere');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uv_weightings');
    }
};
