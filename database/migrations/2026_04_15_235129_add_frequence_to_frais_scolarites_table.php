<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frais_scolarites', function (Blueprint $table) {
            $table->string('frequence')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('frais_scolarites', function (Blueprint $table) {
            $table->dropColumn('frequence');
        });
    }
};
