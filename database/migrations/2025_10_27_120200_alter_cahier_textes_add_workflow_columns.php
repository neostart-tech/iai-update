<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('cahier_textes')) {
            Schema::table('cahier_textes', function (Blueprint $table) {
                if (!Schema::hasColumn('cahier_textes','created_by_user_id')) {
                    $table->unsignedBigInteger('created_by_user_id')->nullable()->after('piece_jointe');
                }
                if (!Schema::hasColumn('cahier_textes','created_by_role')) {
                    $table->string('created_by_role')->nullable()->after('created_by_user_id');
                }
                if (!Schema::hasColumn('cahier_textes','approved_by_user_id')) {
                    $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('created_by_role');
                }
                if (!Schema::hasColumn('cahier_textes','approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
                }
                if (!Schema::hasColumn('cahier_textes','remarks')) {
                    $table->text('remarks')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('cahier_textes','group_id')) {
                    $table->unsignedBigInteger('group_id')->nullable()->after('remarks');
                }
                if (!Schema::hasColumn('cahier_textes','niveau_id')) {
                    $table->unsignedBigInteger('niveau_id')->nullable()->after('group_id');
                }
                if (!Schema::hasColumn('cahier_textes','etudiant_id')) {
                    $table->unsignedBigInteger('etudiant_id')->nullable()->after('niveau_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cahier_textes')) {
            Schema::table('cahier_textes', function (Blueprint $table) {
                foreach (['created_by_user_id','created_by_role','approved_by_user_id','approved_at','remarks','group_id','niveau_id','etudiant_id'] as $col) {
                    if (Schema::hasColumn('cahier_textes', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
