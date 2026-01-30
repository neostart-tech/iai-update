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
        Schema::table('emploi_du_temps', function (Blueprint $table) {
            $table->enum('recurrence_type', ['aucune', 'hebdomadaire', 'quotidienne'])
                ->default('aucune')
                ->after('details');
            $table->string('recurrence_days', 20)
                ->nullable()
                ->after('recurrence_type');
            $table->date('recurrence_end_date')
                ->nullable()
                ->after('recurrence_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emploi_du_temps', function (Blueprint $table) {
            $table->dropColumn(['recurrence_type', 'recurrence_days', 'recurrence_end_date']);
        });
    }
};
