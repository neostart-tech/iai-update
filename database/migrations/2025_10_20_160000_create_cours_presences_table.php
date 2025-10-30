<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('cours_presences')) {
            return;
        }
        Schema::create('cours_presences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cours_id');
            $table->unsignedBigInteger('emploi_du_temps_id')->nullable();
            $table->unsignedBigInteger('etudiant_id');
            $table->string('statut', 10); // present | retard | absent | justifie
            $table->text('commentaire')->nullable();
            $table->boolean('needs_validation')->default(false);
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->unsignedBigInteger('sanction_id')->nullable();
            $table->timestamps();

            $table->unique(['cours_id', 'etudiant_id'], 'cours_etudiant_unique_presence');
            $table->index(['emploi_du_temps_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cours_presences');
    }
};
