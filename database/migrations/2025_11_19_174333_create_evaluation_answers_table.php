<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('evaluation_submissions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('evaluation_questions')->cascadeOnDelete();
            $table->longText('answer_text')->nullable(); 
            $table->json('answer_options')->nullable(); 
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_answers');
    }
};
