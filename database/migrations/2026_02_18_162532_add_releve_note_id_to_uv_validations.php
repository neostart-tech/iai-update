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
        Schema::table('uv_validations', function (Blueprint $table) {
            $table->unsignedBigInteger('releve_note_id')->after('etudiant_id')->nullable();
            $table->foreign('releve_note_id')->references('id')->on('releve_notes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uv_validations', function (Blueprint $table) {
            $table->dropForeign('releve_note_id');
            $table->dropColumn('releve_note_id');
        });
    }
};
