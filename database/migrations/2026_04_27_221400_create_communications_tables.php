<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Table principale des communications
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->longText('content');
            $table->string('type')->default('info'); // info, warning, event, urgent
            $table->string('target_type')->default('all'); // all, roles, specific_users
            $table->json('target_data')->nullable(); // Contient les IDs des rôles ou utilisateurs
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->softDeletes();
            $table->timestamps();
        });

        // Table des pièces jointes
        Schema::create('communication_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('communication_id')->constrained('communications')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });

        // Table de suivi de lecture
        Schema::create('communication_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('communication_id')->constrained('communications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->unique(['communication_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_user');
        Schema::dropIfExists('communication_attachments');
        Schema::dropIfExists('communications');
    }
};
