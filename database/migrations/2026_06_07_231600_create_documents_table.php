<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner'); // This creates owner_id and owner_type
            $table->string('document_key'); // e.g. 'lettre', 'naissance', 'photo'
            $table->string('file_path');
            $table->string('statut')->default('en_attente'); // en_attente, valide, rejete
            $table->text('motif_rejet')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
