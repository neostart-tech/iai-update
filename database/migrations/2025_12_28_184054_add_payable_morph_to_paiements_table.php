<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['tranche_paiement_id']);
            $table->dropColumn('tranche_paiement_id');
            $table->nullableMorphs('payable'); 
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {

            $table->dropMorphs('payable');
            $table->unsignedBigInteger('tranche_paiement_id')->nullable();
            $table->foreign('tranche_paiement_id')
                  ->references('id')
                  ->on('tranche_paiements')
                  ->onDelete('set null');
        });
    }
};
