<?php
// database/migrations/xxxx_add_word_count_config_to_exam_questions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pas besoin de nouvelle colonne, on utilise déjà 'config' JSON
        // Cette migration est juste pour info
    }

    public function down(): void
    {
        // Rien à faire
    }
};