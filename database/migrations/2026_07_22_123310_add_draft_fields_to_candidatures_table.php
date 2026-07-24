<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Support de l'inscription en plusieurs étapes sur escen-website : le dossier
     * est créé dès la première étape (identité + coordonnées) puis complété par
     * des mises à jour successives, plutôt qu'un seul envoi final.
     */
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Jeton aléatoire (distinct du slug, qui est dérivé du nom et donc devinable)
            // permettant au candidat de reprendre/compléter son dossier sans être
            // authentifié, entre la création à l'étape 1 et la soumission finale.
            $table->string('draft_token', 64)->nullable()->unique()->after('slug');

            // Renseigné uniquement une fois les 4 étapes terminées (tuteurs + acceptation
            // des CGU) : distingue un dossier réellement soumis d'un brouillon abandonné
            // en cours de route. Les listes côté staff filtrent dessus par défaut.
            $table->timestamp('soumis_le')->nullable()->after('draft_token');
        });

        // Rétro-remplissage : tous les dossiers déjà en base ont été déposés en un
        // seul envoi (pas de notion de brouillon avant ce jour) — donc "soumis" dès
        // leur création. Sans ça, ils disparaîtraient des listes qui filtrent
        // désormais sur soumis_le (et seraient traités à tort comme des brouillons).
        DB::table('candidatures')->whereNull('soumis_le')->update(['soumis_le' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn(['draft_token', 'soumis_le']);
        });
    }
};
