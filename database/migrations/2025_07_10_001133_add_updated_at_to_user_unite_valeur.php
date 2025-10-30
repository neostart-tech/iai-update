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
        Schema::table('user_unite_valeur', function (Blueprint $table) {
          $table->timestamp('created_at')->nullable();
          $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_unite_valeur', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('update_at');
        });
    }
};
