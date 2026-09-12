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
        Schema::create('semoa_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('libelle');
            $table->string('psp_libelle')->nullable();
            $table->string('methode')->nullable();
            $table->string('currency')->nullable();
            $table->text('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semoa_gateways');
    }
};
