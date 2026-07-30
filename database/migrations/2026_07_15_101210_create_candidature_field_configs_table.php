<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidature_field_configs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('champ_key')->unique();
            $table->string('label');
            $table->boolean('obligatoire')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidature_field_configs');
    }
};
