<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            // Allows marking a student as redoublant with an 'R' notation instead of numeric note
            $table->char('notation', 1)->nullable()->comment("'R' pour redoublant");
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('notation');
        });
    }
};
