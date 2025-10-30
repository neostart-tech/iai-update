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
        Schema::table('paiements', function (Blueprint $table) {
            $table->boolean('annule')->default(false)->after('recu');
            $table->text('motif_annulation')->nullable()->after('annule');
            $table->timestamp('date_annulation')->nullable()->after('motif_annulation');
            $table->unsignedBigInteger('annule_par')->nullable()->after('date_annulation');

            $table->foreign('annule_par')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
              $table->dropForeign(['annule_par']);
            $table->dropColumn(['annule', 'motif_annulation', 'date_annulation', 'annule_par']);
        });
    }
};
