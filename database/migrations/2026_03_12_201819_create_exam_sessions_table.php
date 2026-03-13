<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->onDelete('cascade');
            $table->foreignId('etudiant_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('last_activity_at');
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['en_cours', 'termine', 'interrompu'])->default('en_cours');
            $table->json('progress')->nullable();
            $table->string('session_token')->unique();
            $table->timestamps();
            
            $table->index(['evaluation_id', 'status']);
            $table->index('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};