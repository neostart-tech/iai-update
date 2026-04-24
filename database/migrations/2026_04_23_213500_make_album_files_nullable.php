<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->string('lettre')->nullable()->change();
            $table->string('naissance')->nullable()->change();
            $table->string('diplome')->nullable()->change();
            $table->string('nationalite')->nullable()->change();
            $table->string('photo')->nullable()->change();
            $table->string('certificat_medical')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->string('lettre')->nullable(false)->change();
            $table->string('naissance')->nullable(false)->change();
            $table->string('diplome')->nullable(false)->change();
            $table->string('nationalite')->nullable(false)->change();
            $table->string('photo')->nullable(false)->change();
            $table->string('certificat_medical')->nullable(false)->change();
        });
    }
};
