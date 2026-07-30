<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Additif : la colonne texte `dernier_diplome` existante est conservée pour
            // ne rien casser (ancien affichage), et continue d'être renseignée en
            // parallèle (dénormalisée) à chaque dépôt. Ce FK est la nouvelle source de
            // vérité pour savoir quels champs du parcours scolaire afficher/exiger.
            $table->foreignId('type_diplome_id')->nullable()->after('dernier_diplome')
                ->constrained('type_diplomes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('type_diplome_id');
        });
    }
};
