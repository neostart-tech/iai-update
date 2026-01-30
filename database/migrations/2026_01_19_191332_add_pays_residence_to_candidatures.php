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
        Schema::table('candidatures', function (Blueprint $table) {
            $table->string('pays_residence')->nullable()->after('nationalite');
            $table->string('dernier_diplome')->nullable()->after('pays_residence');
            $table->enum('connaissance_escen', [
                'facebook',
                'linkedin',
                'instagram',
                'tiktok',
                'whatsapp',
                'recherche_en_ligne',
                'recommandation_d_un_ami',
                'autre',
            ])->nullable()->after('dernier_diplome');
            $table->string('autre_connaissance_escen')->nullable()->after('connaissance_escen');
            $table->string('nom_recommande')->nullable()->after('autre_connaissance_escen');
            $table->string('contact_recommande')->nullable()->after('nom_recommande');
            $table->enum('finance_scolarite', [
                'vous_meme',
                'parent_tuteur',
                'autre',
            ])->nullable()->after('contact_recommande');
            $table->string('preciser_autre_finance')->nullable()->after('finance_scolarite');
            $table->string('nom_sponsor')->nullable()->after('preciser_autre_finance');
            $table->string('tel_sponsor')->nullable()->after('nom_sponsor');
            $table->string('email_sponsor')->nullable()->after('tel_sponsor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn([
                'pays_residence',
                'dernier_diplome',
                'connaissance_escen',
                'autre_connaissance_escen',
                'nom_recommande',
                'contact_recommande',
                'finance_scolarite',
                'preciser_autre_finance',
                'nom_sponsor',
                'tel_sponsor',
                'email_sponsor'
            ]);
        });
    }
};
