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
        Schema::table('unite_valeurs', function (Blueprint $table) {
            $table->unsignedBigInteger('periode_id')->nullable()->after('code')->default(1);
            $table->foreign('periode_id')->references('id')->on('periodes')->onDelete('set null');
            $table->integer('volume_horaire')->default(0)->after('periode_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unite_valeurs', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
            $table->dropColumn('periode_id');
            $table->dropColumn('volume_horaire');
        });
    }
};
