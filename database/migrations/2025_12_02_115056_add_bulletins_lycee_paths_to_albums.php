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
        Schema::table('albums', function (Blueprint $table) {
         $table->json('bulletins_lycee_paths')->nullable();
          $table->string('releve_bac1_path')->nullable(); 
            $table->string('releve_bac2_path')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn(['lettre_motivation',"bulletins_lycee_paths","releve_bac1_path",'releve_bac2_path']);
        });
    }
};
