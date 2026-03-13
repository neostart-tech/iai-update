<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('presences', function (Blueprint $table) {
            $table->foreignId('justificatif_id')->nullable()->after('commentaire')->constrained();
        });
    }

    public function down()
    {
        Schema::table('presences', function (Blueprint $table) {
            $table->dropForeign(['justificatif_id']);
            $table->dropColumn('justificatif_id');
        });
    }
};