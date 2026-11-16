<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluations', 'filiere_ids')) {
                $table->json('filiere_ids')->nullable()->after('group_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('evaluations', 'filiere_ids')) {
                $table->dropColumn('filiere_ids');
            }
        });
    }
};

