<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('evaluation_questions')->cascadeOnDelete();
            $table->string('label'); // texte de la réponse
            $table->boolean('is_correct')->default(false); // utile pour correction automatique (optionnel)
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_question_options');
    }
};
