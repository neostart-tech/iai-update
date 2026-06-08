<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn([
                'connaissance',
                'autre_connaissance',
                'nom_recommande',
                'contact_recommande',
                'finance_scolarite',
                'preciser_autre_finance',
                'nom_sponsor',
                'tel_sponsor',
                'email_sponsor',
            ]);
        });
    }

    public function down()
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->enum('connaissance', ['facebook', 'linkedin', 'instagram', 'tiktok', 'autre'])->nullable();
            $table->string('autre_connaissance')->nullable();
            $table->string('nom_recommande')->nullable();
            $table->string('contact_recommande')->nullable();
            $table->enum('finance_scolarite', ['vous_meme', 'parent_tuteur', 'autre'])->nullable();
            $table->string('preciser_autre_finance')->nullable();
            $table->string('nom_sponsor')->nullable();
            $table->string('tel_sponsor')->nullable();
            $table->string('email_sponsor')->nullable();
        });
    }
};
