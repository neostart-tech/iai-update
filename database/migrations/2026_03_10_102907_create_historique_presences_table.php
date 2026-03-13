<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historique_presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presence_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->enum('action', ['creation', 'modification', 'validation', 'suppression']);
            $table->json('anciennes_valeurs')->nullable();
            $table->json('nouvelles_valeurs')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('historique_presences');
    }
};