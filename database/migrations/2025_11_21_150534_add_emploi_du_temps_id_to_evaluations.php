<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('emploi_du_temps_id')
                ->nullable()
                ->after('slug');
            $table->foreign('emploi_du_temps_id')->references('id')->on('emploi_du_temps');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('emploi_du_temps_id');
        });
    }
};
