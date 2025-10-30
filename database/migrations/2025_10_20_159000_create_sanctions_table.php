<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sanctions')) {
            return;
        }
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cours_id');
            $table->unsignedBigInteger('etudiant_id');
            $table->unsignedBigInteger('enseignant_id');
            $table->text('description');
            $table->timestamps();

            $table->index(['cours_id']);
            $table->index(['etudiant_id']);
            $table->index(['enseignant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};
