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
        Schema::table('salles', function (Blueprint $table) {
            $table->string('type')->default('physique')->after('effectif');
            $table->string('lien_reunion')->nullable()->after('type');
            $table->string('plateforme')->nullable()->after('lien_reunion');
            $table->text('instructions')->nullable()->after('plateforme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salles', function (Blueprint $table) {
            $table->dropColumn(['type', 'lien_reunion', 'plateforme', 'instructions']);
        });
    }
};
