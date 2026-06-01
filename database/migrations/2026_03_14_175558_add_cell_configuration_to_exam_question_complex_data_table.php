<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exam_question_complex_data', function (Blueprint $table) {
            $table->json('cell_configuration')->nullable()->after('configuration');
            $table->json('cell_data')->nullable()->after('data')->comment('Configuration cellule par cellule');
        });
    }

    public function down()
    {
        Schema::table('exam_question_complex_data', function (Blueprint $table) {
            $table->dropColumn(['cell_configuration', 'cell_data']);
        });
    }
};