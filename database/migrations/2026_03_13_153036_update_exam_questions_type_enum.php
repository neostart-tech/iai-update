<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pour MySQL, on doit modifier l'ENUM
        DB::statement("ALTER TABLE exam_questions MODIFY COLUMN type ENUM(
            'qcm_unique',
            'qcm_multiple',
            'texte_court',
            'texte_long',
            'vrai_faux',
            'appariement',
            'ordre',
            'fichier',
            'complex_data',
            'structured_data',
            'multi_parts',
            'guided_writing'
        ) NOT NULL");
    }

    public function down(): void
    {
        // Revenir à l'ancienne liste
        DB::statement("ALTER TABLE exam_questions MODIFY COLUMN type ENUM(
            'qcm_unique',
            'qcm_multiple',
            'texte_court',
            'texte_long',
            'vrai_faux',
            'appariement',
            'ordre',
            'fichier'
        ) NOT NULL");
    }
};