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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('supervisor_type', ['interne', 'externe', 'non_surveillant'])->default('non_surveillant')->after('slug');
            $table->text('supervisor_notes')->nullable()->after('supervisor_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['supervisor_type', 'supervisor_notes']);
        });
    }
};
