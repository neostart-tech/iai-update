<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('student_uv_statuses')) {
            Schema::create('student_uv_statuses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('etudiant_id');
                $table->unsignedBigInteger('uv_id');
                $table->unsignedBigInteger('group_id')->nullable();
                $table->unsignedInteger('total_sessions')->default(0);
                $table->unsignedInteger('absences_count')->default(0);
                $table->float('absence_rate')->default(0);
                $table->unsignedTinyInteger('warning_level')->default(0); // 0,1,2,3
                $table->boolean('blocked')->default(false);
                $table->timestamps();

                $table->unique(['etudiant_id','uv_id','group_id'], 'uniq_student_uv_group');
                $table->index(['uv_id','group_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_uv_statuses');
    }
};
