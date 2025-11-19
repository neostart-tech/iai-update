<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('statement');
            $table->enum('type', ['text', 'textarea', 'choice_single', 'choice_multiple']);
            $table->integer('order')->default(0);
            $table->double('points')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_questions');
    }
};
