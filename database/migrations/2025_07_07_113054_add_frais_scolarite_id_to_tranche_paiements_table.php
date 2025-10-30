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
        Schema::table('tranche_paiements', function (Blueprint $table) {
           $table->unsignedBigInteger('frais_scolarite_id')->after('id');

        $table->foreign('frais_scolarite_id')
              ->references('id')
              ->on('frais_scolarites')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tranche_paiements', function (Blueprint $table) {
            //
        });
    }
};
