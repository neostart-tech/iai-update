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
        Schema::table('urgent_infos', function (Blueprint $table) {
            $table->string('image')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('target_audience', ['all', 'students', 'teachers', 'administration', 'group'])->default('all');
            $table->foreignId('target_group_id')->nullable()->constrained('groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('urgent_infos', function (Blueprint $table) {
            $table->dropForeign(['target_group_id']);
            $table->dropColumn(['image', 'attachments', 'target_audience', 'target_group_id']);
        });
    }
};
