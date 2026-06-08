<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('candidature_documents');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('candidature_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidature_id');
            $table->string('type', 30);
            $table->string('niveau', 30)->nullable();
            $table->string('path');
            $table->timestamps();

            $table->foreign('candidature_id')
                ->references('id')->on('candidatures')
                ->onDelete('cascade');
        });
    }
};
