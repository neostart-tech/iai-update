<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('exam_parts')->onDelete('cascade');
            $table->longText('content');
            $table->enum('type', [
                'qcm_unique',
                'qcm_multiple',
                'texte_court',
                'texte_long',
                'vrai_faux',
                'appariement',
                'ordre',
                'fichier'
            ]);
            $table->json('config')->nullable();
            $table->integer('points')->default(0);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['part_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};