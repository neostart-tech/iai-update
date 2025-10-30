<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidature_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidature_id');
            $table->string('type', 30); // bulletin | releve
            $table->string('niveau', 30)->nullable(); // seconde|premiere|terminale|bac1|bac2
            $table->string('path');
            $table->timestamps();

            $table->foreign('candidature_id')
                ->references('id')->on('candidatures')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidature_documents');
    }
};
