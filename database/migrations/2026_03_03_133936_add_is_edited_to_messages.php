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
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->string('message_type')->default('text');
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['is_edited', 'edited_at', 'message_type', 'reply_to_id']);
        });
    }
};
