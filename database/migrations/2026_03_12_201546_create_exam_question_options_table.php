<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('exam_questions')->onDelete('cascade');
            $table->text('text'); // Peut contenir du HTML
            $table->boolean('is_correct')->default(false);
            $table->json('metadata')->nullable(); // Pour appariement, ordre, etc.
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['question_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_options');
    }
};