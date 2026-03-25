<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Polymorphic relation pour User (professeurs, surveillants, admins) ou Etudiant
            $table->morphs('ticketable');
            
            // Status
            $table->enum('status', ['open', 'in_progress', 'waiting', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Relations
            $table->foreignId('category_id')->constrained('support_categories')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Dates
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Évaluation
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('feedback')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'priority']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};