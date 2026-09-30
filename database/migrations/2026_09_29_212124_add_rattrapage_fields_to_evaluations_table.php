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
        Schema::table('evaluations', function (Blueprint $table) {
            $table->enum('session_type', ['normale', 'rattrapage'])->default('normale')->after('type');
            $table->foreignId('parent_id')->nullable()->constrained('evaluations')->nullOnDelete()->after('session_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['session_type', 'parent_id']);
        });
    }
};
