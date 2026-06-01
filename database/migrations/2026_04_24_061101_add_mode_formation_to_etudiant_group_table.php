<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ModeFormationEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('etudiant_group', function (Blueprint $table) {
            $table->string('mode_formation')->default(ModeFormationEnum::PRESENTIEL->value)->after('annee_scolaire_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etudiant_group', function (Blueprint $table) {
            $table->dropColumn('mode_formation');
        });
    }
};
