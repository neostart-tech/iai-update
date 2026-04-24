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
        Schema::table('etudiants', function (Blueprint $table) {
            $table->string('tel')->nullable()->change();
            $table->string('nationalite')->nullable()->change();
            $table->timestamp('date_naissance')->nullable()->change();
            $table->string('lieu_naissance')->nullable()->change();
            $table->string('image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etudiants', function (Blueprint $table) {
            $table->string('tel')->nullable(false)->change();
            $table->string('nationalite')->nullable(false)->change();
            $table->timestamp('date_naissance')->nullable(false)->change();
            $table->string('lieu_naissance')->nullable(false)->change();
            $table->string('image')->nullable(false)->change();
        });
    }
};
