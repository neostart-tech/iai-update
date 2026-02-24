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
        Schema::table('frais_scolarites', function (Blueprint $table) {
            $table->unsignedBigInteger('filiere_id')->nullable()->after('niveau_id');
            $table->foreign('filiere_id')->references('id')->on('filieres')->onDelete('cascade');
            $table->string("slug")->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frais_scolarites', function (Blueprint $table) {
            $table->dropColumn("filiere_id");
            $table->dropForeign('filiere_id');
            $table->dropColumn('slug');
        });
    }
};
