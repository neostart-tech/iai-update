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
        Schema::table('plans_paiement', function (Blueprint $table) {
            $table->enum('type_plan', [
                'standard',
                'tranches_fixes',
                'negociation'
            ])->default('standard');

            $table->boolean('est_actif')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans_paiement', function (Blueprint $table) {
            $table->dropColumn(['type_plan','est_actif']);
        });
    }
};
