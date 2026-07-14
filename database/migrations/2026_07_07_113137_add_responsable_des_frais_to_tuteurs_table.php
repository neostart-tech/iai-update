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
        Schema::table('tuteurs', function (Blueprint $table) {
            $table->boolean('responsable_des_frais')->default(false)->after('bp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tuteurs', function (Blueprint $table) {
            $table->dropColumn('responsable_des_frais');
        });
    }
};
