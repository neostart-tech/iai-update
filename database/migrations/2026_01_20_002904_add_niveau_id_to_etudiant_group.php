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
        Schema::table('etudiant_group', function (Blueprint $table) {
            $table->unsignedBigInteger('niveau_id')->nullable()->after('group_id')->default(1);
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etudiant_group', function (Blueprint $table) {
         $table->dropForeign('niveau_id');
         $table->dropColumn('niveau_id');
        });
    }
};
