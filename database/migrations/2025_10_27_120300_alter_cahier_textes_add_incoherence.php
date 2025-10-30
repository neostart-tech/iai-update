<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('cahier_textes')) {
            Schema::table('cahier_textes', function (Blueprint $table) {
                if (!Schema::hasColumn('cahier_textes','incoherent')) {
                    $table->boolean('incoherent')->default(false)->after('etudiant_id');
                }
                if (!Schema::hasColumn('cahier_textes','incoherence_notes')) {
                    $table->text('incoherence_notes')->nullable()->after('incoherent');
                }
                if (!Schema::hasColumn('cahier_textes','notified_at')) {
                    $table->timestamp('notified_at')->nullable()->after('incoherence_notes');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cahier_textes')) {
            Schema::table('cahier_textes', function (Blueprint $table) {
                foreach (['incoherent','incoherence_notes'] as $col) {
                    if (Schema::hasColumn('cahier_textes', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
