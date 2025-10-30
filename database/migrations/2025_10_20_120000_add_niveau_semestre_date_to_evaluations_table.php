<?php

use App\Models\Niveau;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluations', 'niveau_id')) {
                $table->foreignIdFor(Niveau::class)->nullable()->after('group_id');
            }
            if (!Schema::hasColumn('evaluations', 'semestre')) {
                $table->unsignedTinyInteger('semestre')->nullable()->after('niveau_id');
            }
            if (!Schema::hasColumn('evaluations', 'date')) {
                $table->date('date')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('evaluations', 'date')) {
                $table->dropColumn('date');
            }
            if (Schema::hasColumn('evaluations', 'semestre')) {
                $table->dropColumn('semestre');
            }
            if (Schema::hasColumn('evaluations', 'niveau_id')) {
                $table->dropConstrainedForeignId('niveau_id');
            }
        });
    }
};
