<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Drop unique index on tel if it exists
            try {
                $table->dropUnique('candidatures_tel_unique');
            } catch (\Throwable $e) {
                // Index might not exist in some environments; ignore
            }

            // Optional: keep a normal index for performance on searches
            try {
                $table->index('tel');
            } catch (\Throwable $e) {
                // Ignore if already indexed
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Remove normal index if we added it
            try {
                $table->dropIndex(['tel']);
            } catch (\Throwable $e) {
                // ignore
            }

            // Restore uniqueness
            try {
                $table->unique('tel');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};
