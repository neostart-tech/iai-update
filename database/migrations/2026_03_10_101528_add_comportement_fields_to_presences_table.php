<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('presences', function (Blueprint $table) {
            // Comportement et participation
            $table->enum('participation', [
                'excellente',
                'bonne', 
                'moyenne',
                'faible',
                'nulle',
                'non_concerné'
            ])->nullable()->after('commentaire');
            
            $table->enum('attitude', [
                'exemplaire',
                'correcte',
                'a_surveiller',
                'problematique',
                'perturbateur'
            ])->nullable()->after('participation');
            
            $table->text('observations_comportement')->nullable()->after('attitude');
            $table->json('points_attention')->nullable()->after('observations_comportement');
            $table->json('points_positifs')->nullable()->after('points_attention');
            
            // Alertes
            $table->boolean('a_signalement')->default(false)->after('points_positifs');
            $table->boolean('a_remonter_conseil')->default(false)->after('a_signalement');
        });
    }

    public function down()
    {
        Schema::table('presences', function (Blueprint $table) {
            $table->dropColumn([
                'participation',
                'attitude',
                'observations_comportement',
                'points_attention',
                'points_positifs',
                'a_signalement',
                'a_remonter_conseil'
            ]);
        });
    }
};