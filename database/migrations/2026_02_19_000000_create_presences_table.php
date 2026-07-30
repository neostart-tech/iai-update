<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('presences')) {
            return;
        }

        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emploi_du_temps_id')->nullable();
            $table->unsignedBigInteger('etudiant_id');
            $table->enum('statut', ['present', 'absent', 'retard', 'justifie'])->default('present');
            $table->text('commentaire')->nullable();
            $table->boolean('needs_validation')->default(false);
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->string('sanction')->nullable();
            $table->timestamps();

            $table->unique('etudiant_id', 'cours_etudiant_unique_presence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
